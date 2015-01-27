<?php

namespace WebDreamt;

use Cartalyst\Sentry\Sentry;
use Exception;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use WebDreamt\Component\Wrapper\Data;

class Server {

	const ACT_CREATE = 'create';
	const ACT_UPDATE = 'update';
	const ACT_DELETE = 'delete';

	/**
	 * The name to use to try to get the default group.
	 * @var string
	 */
	protected $defaultGroupName = 'default';
	/**
	 * The sentry instance
	 * @var Sentry $sentry
	 */
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
	 * Set the default group name.
	 * @param string $name
	 * @return static
	 */
	function setDefaultGroupName($name) {
		$this->defaultGroupName = $name;
		return $this;
	}

	/**
	 * Get the default group name. By default, it is default.
	 * @return string
	 */
	function getDefaultGroupName() {
		return $this->defaultGroupName;
	}

	/**
	 * Executes the action for the requested table and the given parameters.
	 * If successful, returns the modified object. If insufficient permissions, returns false.
	 * @param string $tableName
	 * @param string $action Note can be null, in which cases will try to infer create or delete based
	 * on whether the columns given contain the primary keys
	 * @param array $columns Note that all operations need some columns (create to insert,
	 * update/delete to find)
	 * @param ConnectionInterface $connection A Propel connnection so that the operations can be batched.
	 * Defaults to not using connection (and so not batching).
	 * @return ActiveRecordInterface|boolean
	 */
	function run($tableName, $action, $columns, $connection = null) {
		Builder::loadMaps();

		//Check the input.
		if (!is_string($tableName)) {
			throw new Exception('Did not specify the name of the table as a string.');
		}
		if ($action !== null && !in_array($action, self::$actions)) {
			throw new Exception("Did not specify a valid action.");
		}
		//Propel throws an exception if the table is invalid.
		/* @var $tableMap TableMap */
		$tableMap = Propel::getDatabaseMap()->getTable($tableName);
		//If the action is null, try to infer whether to create or update with the given columns.
		$keys = null;
		if ($action === null) {
			//If the table is a cross-reference table, then we can't tell if we are supposed to update
			//or create the entry in the database. So, we just assume that we update unless we can't find
			//the item, which is indicated by $keys being null. Note that in other cases we assume it is an
			//error when we can't find the item, but could find the primary keys (which is indicated when
			//$keys is false).
			$keys = $this->useKeys($tableMap, $columns);
			if ($keys === false || ($tableMap->isCrossRef() && $keys === null)) {
				$action = self::ACT_CREATE;
			} else {
				$action = self::ACT_UPDATE;
			}
		}
		//Note that permissible will flag if the $action is invalid.
		if (!$this->permitted($tableName, $action, $columns)) {
			return false;
		}
		if ($action === self::ACT_CREATE) {
			//Create and save an object.
			$type = $tableMap->getPhpName();
			$object = new $type();
			$object->fromArray($columns, TableMap::TYPE_FIELDNAME);
			$object->save($connection);
		} else if ($action === self::ACT_UPDATE) {
			//Update an existing an object.
			$object = $keys ? : $this->useKeys($tableMap, $columns);
			if (!$object) {
				throw new Exception("Tried to update but did not provide the primary keys.");
			}
			$object->fromArray($columns, TableMap::TYPE_FIELDNAME);
			$object->save($connection);
		} else if ($action === self::ACT_DELETE) {
			//Delete an eisting object.
			$object = $this->useKeys($tableMap, $columns);
			if (!$object) {
				throw new Exception("Tried to delete but did not provide the primary keys.");
			}
			$object->delete($connection);
		}
		return $object;
	}

	/**
	 * Check if the the user belongs to any of the passed in groups.
	 * @param array|string $requiredGroups
	 * @return boolean
	 */
	function checkGroups($requiredGroups) {
		if (!is_array($requiredGroups)) {
			$requiredGroups = [$requiredGroups];
		}
		$sentry = Box::get()->sentry();
		$user = $sentry->getUser();
		$groupNames = [];
		if ($user) {
			$groups = $user->getGroups();
			foreach ($groups as $group) {
				$groupNames[] = $group['name'];
			}
		} else {
			$groupNames[] = $this->getDefaultGroupName();
		}

		if (count(array_intersect($requiredGroups, $groupNames)) >= 1) {
			return true;
		}
		return false;
	}

	/**
	 * Tries to find a Propel object based off the $columns given in the given table. Returns
	 * false if not enough information is given. This has a side-effect of removing any
	 * primary key column in $columns that is empty (on the assumption that it is not a valid primary
	 * key).
	 * @param TableMap $tableMap
	 * @param array $columns
	 * @return boolean|ActiveRecordInterface
	 */
	protected function useKeys(TableMap $tableMap, &$columns) {
		$type = $tableMap->getPhpName();
		$keys = $tableMap->getPrimaryKeys();
		//Change the $keys array into a form we can use .
		$findWith = [];
		foreach ($keys as $key) {
			$name = $key->getName();
			if (isset($columns[$name])) {
				if ($columns[$name] === '') {
					unset($columns[$name]);
				} else {
					$findWith[$name] = $columns[$name];
				}
			}
		}
		//Get the primary key columns from the input.
		//Count the columns to make sure all were filled.
		if (count($keys) !== count($findWith)) {
			return false;
		}
		//For the given query class, create a query object and call findPK() on it with the $findWith array.
		$query = $type . "Query";
		if (count($findWith) === 1) {
			$object = call_user_func_array([$query::create(), "findPk"], $findWith);
		} else {
			$object = call_user_func([$query::create(), "findPk"], array_values($findWith));
		}
		return $object;
	}

	/**
	 * Allow the server to handle forms via $_POST. Note this will also check for delete
	 * conditions in the $_GET variable.
	 */
	function automate() {
		if (count($_POST) >= 0) {
			$this->batch($_POST);
		}
		if (count($_GET) >= 0 && isset($_GET['delete']) && isset($_GET['class'])) {
			$pks = Data::getPrimaryKeysFromUrl();
			if ($pks !== null) {
				Builder::loadMaps();

				$mapClass = "\\Map\\" . $_GET['class'] . "TableMap";
				$tableMap = $mapClass::getTableMap();
				$tableName = $tableMap->getName();

				$this->run($tableName, Server::ACT_DELETE, $pks);
			}
		}
	}

	/**
	 * Attempts to infer how to modify the database (create or update) based on passed in data.
	 * @param array $data See test cases for examples of format.
	 * @throws Exception If Propel can't commit the batch.
	 */
	function batch($data) {
		$connection = Propel::getWriteConnection(Propel::getDefaultDatasource());
		//Maybe disable instance pooling?

		$items = [];
		$tables = [];
		$store = [];
		$delete = [];
		//Change POST data into a more usable format.
		foreach ($data as $key => $value) {
			//Get the table name if of the form '1' => 'customer'
			if (is_numeric($key)) {
				$tables[$key] = $value;
			} else {
				$parts = explode(':', $key);
				//4:with:3: contract.buyer_agent_id
				if (count($parts) === 4) {
					$delete[$parts[0]] = true;
				} else if (count($parts) === 3) {
					//This case is a bit tricky because we need to notate the dependency and figure out
					//how to fill it. We will deal with this once we know the tables for all IDs.
					if (intval($parts[0]) < intval($parts[2])) {
						$index = "$parts[0].$parts[2]";
					} else {
						$index = "$parts[2].$parts[0]";
					}
					if (!isset($store[$index])) {
						$store[$index] = [];
					}
					$store[$index][] = $value;
					//Get the value for the specified column.
					//This will be of the form '1:first_name' => 'John'
				} else if (count($parts) === 2) {
					//Make an array if it doesn't exist aleady for the item.
					if (!isset($items[$parts[0]])) {
						$items[$parts[0]] = [];
					}
					$items[$parts[0]][$parts[1]] = $value;
				}
			}
		}

		//Figure out dependencies.
		$edges = [];
		$fulfills = [];
		foreach ($store as $index => $columns) {
			foreach ($columns as $column) {
				$ids = explode('.', $index);
				$table = explode('.', $column)[0];
				//We need to figure out what table the column is in. If it is the first table, then
				//we will need to add the second table before the first. Otherwise, we need to do the opposite.
				if ($tables[$ids[0]] === $table) {
					$first = $ids[1];
					$second = $ids[0];
				} else {
					$first = $ids[0];
					$second = $ids[1];
				}

				if (!isset($fulfills[$first])) {
					$fulfills[$first] = [];
				}
				$intFirst = intval($first);
				$intSecond = intval($second);
				//$fulfills keeps track of what the form ID of $first is used in.
				$fulfills[$intFirst][] = $intSecond;
				//$edges is used for topological sort and states that $first comes before $second.
				$edges[] = [$intFirst, $intSecond];
			}
		}

		//Do a topological sort of the dependencies to determine what we can add first.
		$sortedIds = Topological::sort(array_keys($tables), $edges);

		//Do the transaction.
		$connection->beginTransaction();
		try {
			//Create or update for the given items.
			foreach ($sortedIds as $id) {
				$objectId = null;
				$action = null;
				if (isset($delete[$id])) {
					$action = Server::ACT_DELETE;
				}
				$object = $this->run($tables[$id], $action, $items[$id], $connection);
				if ($object !== false) {
					if (method_exists($object, 'getId')) {
						$objectId = $object->getId();
					}
				} else {
					if (isset($items[$id]['id']) && $items[$id]['id'] !== '') {
						$objectId = intval($items[$id]['id']);
					}
				}
				if ($objectId !== null) {
					//Now that we have added the object, we need to update other items with the ID.
					if (isset($fulfills[$id])) {
						foreach ($fulfills[$id] as $incompleteId) {
							if ($id < $incompleteId) {
								$columns = $store["$id.$incompleteId"];
							} else {
								$columns = $store["$incompleteId.$id"];
							}
							foreach ($columns as $column) {
								$items[$incompleteId][explode('.', $column)[1]] = $objectId;
							}
						}
					}
				}
			}
			$connection->commit();
		} catch (Exception $e) {
			$connection->rollBack();
			throw $e;
		}
	}

	/**
	 * Checks to see if the current user is allowed to do the given action. It does this by
	  e	 * checking if first the user has permission to do the action on the table in general.
	 * If he or she, does then returns true. If not, then checks the permissions for the given columns.
	 * @param string $tableName
	 * @param string $action
	 * @param array $columns
	 * @return boolean True if allowed. False if not.
	 * @throws Exception Thrown if a valid action is not specified OR if no uses is logged in and no
	 * default group set OR if the table name is not a string.
	 */
	function permitted($tableName, $action, $columns = null) {
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
			$group = $this->sentry->findGroupByName($this->defaultGroupName);
			$permissions = $group->getPermissions();
		} else {
			$permissions = $user->getMergedPermissions();
		}
		return $this->permissionsContain($permissions, $tableName, $action, $columns);
	}

	/**
	 * Checks to see if the group is permitted to do the given action.
	 * @param string $groupName
	 * @param string $tableName
	 * @param string $action
	 * @param array $columns
	 * @return boolean True if allowed. False if not
	 * @throws Exception
	 */
	function groupPermitted($groupName, $tableName, $action, $columns = null) {
		$permissions = $this->sentry->findGroupByName($groupName)->getPermissions();
		return $this->permissionsContain($permissions, $tableName, $action);
	}

	/**
	 * Check to see if the permissions contain the given action for the table name.
	 * @param mixed $permissions
	 * @param string $tableName
	 * @param string $action
	 * @param string|array $columns
	 * @return boolean
	 */
	function permissionsContain($permissions, $tableName, $action, $columns = null) {
		$key = "api/$tableName/$action";
		//Coerce columns.
		if ($columns !== null && !is_array($columns)) {
			$columns = [$columns];
		}
		//Check if there are general permissions.
		if (isset($permissions[$key]) && $permissions[$key] === 1) {
			return true;
		} else {
			//Check to see if the user has permission for the specified columns.
			if (empty($columns)) {
				return false;
			}
			foreach ($columns as $column => $value) {
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
	 * @param string|array $groupName
	 * @param string|array $tableName
	 * @param string|array $action
	 * @param array $columns
	 */
	function allow($groupName, $tableName, $action, $columns = null) {
		$this->codify(true, $groupName, $tableName, $action, $columns);
	}

	/**
	 * Denies an action for a certain group.
	 * @param string|array $groupName
	 * @param string|array $tableName
	 * @param string|array $action
	 * @param array $columns
	 */
	function deny($groupName, $tableName, $action, $columns = null) {
		$this->codify(false, $groupName, $tableName, $action, $columns);
	}

	/**
	 * Allows or denies an action depending on the value of $permission.
	 * @param boolean $permission If true then the action is allowed. If false then the action is denied.
	 * @param string|array $groupNames
	 * @param string|array $tableNames
	 * @param string|array $actions
	 * @param array $columns
	 * @throws Exception If the requested group is not found.
	 */
	function codify($permission, $groupNames, $tableNames, $actions, $columns = null) {
		//Coerce into an array.
		if (!is_array($groupNames)) {
			$groupNames = [$groupNames];
		}
		if (!is_array($tableNames)) {
			$tableNames = [$tableNames];
		}
		if (!is_array($actions)) {
			$actions = [$actions];
		}
		if ($columns !== null && !is_array($columns)) {
			$columns = [$columns];
		}

		foreach ($groupNames as $groupName) {
			//Get the group.
			$group = $this->sentry->findGroupByName($groupName);
			if (!$group) {
				throw Exception("Requested group is not found.");
			}

			$permissions = $group->permissions;
			foreach ($tableNames as $tableName) {
				foreach ($actions as $action) {
					//Allow the action.
					if ($permission) {
						//Allow in general.
						if (empty($columns) || $action === self::ACT_DELETE) {
							$permissions["api/$tableName/$action"] = 1;
						} else {
							//Allow for given columns.
							foreach ($columns as $column) {
								$permissions["api/$tableName/$action/$column"] = 1;
							}
						}
						//Deny the action.
					} else {
						//Deny in general.
						if (empty($columns) || $action === self::ACT_DELETE) {
							$permissions["api/$tableName/$action"] = null;
						} else {
							//Deny for given columns.
							foreach ($columns as $column) {
								$permissions["api/$tableName/$action/$column"] = null;
							}
						}
					}
				}
			}
			$group->permissions = $permissions;
			$group->save();
		}
	}

}
