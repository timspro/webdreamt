<?php

namespace WebDreamt;

use Cartalyst\Sentry\Sentry;
use Exception;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;

class Server {

	const ACT_CREATE = 'create';
	const ACT_UPDATE = 'update';
	const ACT_DELETE = 'delete';

	/** @var Sentry $sentry */
	protected $sentry;
	/**
	 * The available actions.
	 * @var array
	 */
	static protected $actions = [self::ACT_CREATE, self::ACT_UPDATE, self::ACT_DELETE];

	function __construct(Box $box) {
		$this->sentry = $box->sentry();
	}

	/**
	 * Executes the action for the requested table and the given parameters.
	 * Throws an error if insufficient permissions.
	 * If successful, returns the modified object.
	 * @param string $tableName
	 * @param string $action Note can be null, in which cases will try to infer create or delete based
	 * on whether the columns given contain the primary keys
	 * @param array $columns Note that all operations need some columns (create to insert,
	 * update/delete to find)
	 * @param ConnectionInterface $connection A Propel connnection so that the operations can be batched.
	 * Defaults to not using connection (and so not batching).
	 * @return $object
	 */
	function run($tableName, $action, $columns, $connection = null) {
		//Propel throws an exception if the table is invalid.
		/* @var $tableMap TableMap */
		$tableMap = Propel::getDatabaseMap()->getTable($tableName);
		//If the action is null, try to infer whether to create or update with the given columns.
		$keys = null;
		if ($action === null) {
			$keys = $this->findWithKeys($tableMap, $columns);
			if ($keys === false) {
				$action = self::ACT_CREATE;
			} else {
				$action = self::ACT_UPDATE;
			}
		}
		//Note that permissible will flag if the $action is invalid.
		if (!$this->permissible($tableName, $action, $columns)) {
			throw new Exception("Insufficient permissions for the requested table or the "
			. "requested table doesn't exist");
		}
		if ($action === self::ACT_CREATE) {
			//Create and save an object.
			$type = $tableMap->getPhpName();
			$object = new $type();
			$object->fromArray($columns, TableMap::TYPE_FIELDNAME);
			$object->save($connection);
		} else if ($action === self::ACT_UPDATE) {
			//Update an existing an object.
			$object = $keys ? : $this->findWithKeys($tableMap, $columns);
			if ($object === false) {
				throw new Exception("Tried to update but did not provide the primary keys.");
			}
			$object->fromArray($columns, TableMap::TYPE_FIELDNAME);
			$object->save($connection);
		} else if ($action === self::ACT_DELETE) {
			//Delete an eisting object.
			$object = $this->findWithKeys($tableMap, $columns);
			if ($object === false) {
				throw new Exception("Tried to delete but did not provide the primary keys.");
			}
			$object->delete($connection);
		}
		return $object;
	}

	/**
	 * Tries to find a Propel object based off the $columns given in the given table. Returns
	 * false if not enough information is given.
	 * @param TableMap $tableMap
	 * @param array $columns
	 * @return boolean|ActiveRecordInterface
	 */
	protected function findWithKeys(TableMap $tableMap, $columns) {
		$type = $tableMap->getPhpName();
		$keys = $tableMap->getPrimaryKeys();
		//Change the $keys array into a form we can use .
		$keyColumns = [];
		foreach ($keys as $key) {
			$keyColumns[$key->getName()] = true;
		}
		//Get the primary key columns from the input.
		$findWith = array_intersect($keyColumns, $columns);
		//Count the columns to make sure all were filled.
		if (count($keyColumns) !== count($findWith)) {
			return false;
		}
		//For the given query class, create a query object and call findPK() on it with the $findWith array.
		$query = $type . "Query";
		$object = call_user_func_array([$query::create(), "findPk"], $findWith);
		return $object;
	}

	/**
	 * Attempts to infer how to modify the database (create or update) based on data on passed in data
	 * or from the $_POST variable. The format used is the same as the format used in Hyper/Form.
	 * @param array $data If null, then uses the $_POST variable.
	 * @throws Exception If Propel can't commit the batch.
	 */
	function batch($data = null) {
		$data = $data ? : $_POST;

		$connection = Propel::getWriteConnection(Propel::getDefaultDatasource());
		//Maybe disable instance pooling?

		$items = [];
		$tables = [];
		//Change POST data into a more usable format.
		foreach ($data as $key => $value) {
			//Get the table name if of the form '1' => 'customer'
			if (is_numeric($key)) {
				$tables[$key] = $value;
				//Get the value for the specified column.
				//This will be of the form '1-first_name' => 'John'
			} else {
				$parts = explode('-', $key);
				//Make an array if it doesn't exist aleady for the item.
				if (!isset($items[$parts[0]])) {
					$items[$parts[0]] = [];
				}
				$items[$parts[0]][$parts[1]] = $value;
			}
		}

		$connection->beginTransaction();

		try {
			//Create or update for the given items.
			foreach ($items as $key => $item) {
				$this->run($tables[$key], null, $item, $connection);
			}
			$connection->commit();
		} catch (Exception $e) {
			$connection->rollBack();
			throw $e;
		}
	}

	/**
	 * Checks to see if the user is allowed to do the given action. It does this by checking if first the
	 * user has permission to do the action on the table in general. If he or she, does then returns true.
	 * If not, then checks the permissions for the given columns.
	 * @param string $tableName
	 * @param string $action
	 * @param array $columns
	 * @return boolean True if allowed. False if not.
	 * @throws Exception Thrown if a valid action is not specified OR if no uses is logged in and no
	 * default group set OR if the table name is not a string.
	 */
	function permissible($tableName, $action, $columns = null) {
		//Check the input.
		if (!is_string($tableName)) {
			throw new Exception('Did not specify the name of the table as a string.');
		}
		if (!in_array($action, self::$actions)) {
			throw new Exception("Did not specify a valid action.");
		}
		//Get user permissions.
		$user = $this->sentry->getUser();
		if (!$user) {
			throw new Exception("No user is logged in.");
		} else {
			$permissions = $user->getMergedPermissions();
		}
		$key = "api/$tableName/$action";
		//Check if there are general permissions.
		if (isset($permissions[$key]) && $permissions[$key] === 1) {
			return true;
		} else {
			//Check to see if the user has permission for the specified columns.
			if (empty($columns)) {
				return false;
			}
			foreach ($columns as $column) {
				$key = "api/$tableName/$action/$column";
				if (!isset($permissions[$key]) || $permissions[$key] !== 1) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Allows an action for a certain group.
	 * @param string $groupName
	 * @param string $tableName
	 * @param string $action
	 * @param array $columns
	 */
	function allow($groupName, $tableName, $action, $columns = null) {
		$this->codify($groupName, 1, $tableName, $action, $columns);
	}

	/**
	 * Denies an action for a certain group.
	 * @param string $groupName
	 * @param string $tableName
	 * @param string $action
	 * @param array $columns
	 */
	function deny($groupName, $tableName, $action, $columns = null) {
		$this->codify($groupName, -1, $tableName, $action, $columns);
	}

	/**
	 * Allows or denies an action depending on the value of $permission.
	 * @param string $groupName
	 * @param int $permission
	 * @param string $tableName
	 * @param string $action
	 * @param array $columns
	 * @throws Exception If the requested group is not found.
	 */
	protected function codify($groupName, $permission, $tableName, $action, $columns = null) {
		$group = $this->sentry->findGroupByName($groupName);
		if (!$group) {
			throw Exception("Requested group is not found.");
		}
		//Allow the action.
		if ($permission === 1) {
			$permissions = [];
			//Allow in general.
			if (empty($columns) || $action === self::ACT_DELETE) {
				$permissions["api/$tableName/$action"] = 1;
			} else {
				//Allow for given columns.
				foreach ($columns as $column) {
					$permissions["api/$tableName/$action/$column"] = 1;
				}
			}
			$group->permissions = array_merge($group->permissions, $permissions);
			//Deny the action.
		} else if ($permission === -1) {
			//Deny in general.
			if (empty($columns) || $action === self::ACT_DELETE) {
				unset($permissions["api/$tableName/$action"]);
			} else {
				//Deny for given columns.
				foreach ($columns as $column) {
					unset($permissions["api/$tableName/$action/$column"]);
				}
			}
		}
		$group->save();
	}

}
