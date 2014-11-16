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
	 * @param string $table
	 * @param string $action
	 * @param array $columns Note that all operations need some columns (create to insert,
	 * update/delete to find)
	 * @return $object
	 */
	function run($table, $action, $columns) {
		if (!$this->canDoAction($table, $action, $columns)) {
			throw new Exception("Insufficient permissions for the requested table or the "
			. "requested table doesn't exist");
		}
		/* @var $tableMap TableMap */
		$tableMap = Propel::getDatabaseMap()->getTable($table);
		$type = $tableMap->getPhpName();
		if ($action === self::ACT_CREATE) {
			$object = new $type();
			$object->fromArray($columns, TableMap::TYPE_FIELDNAME);
			$object->save();
		} else if ($action === self::ACT_UPDATE) {
			$query = $type . "Query";
			$keys = $tableMap->getPrimaryKeys();
			$keyColumns = [];
			foreach ($keys as $key) {
				$keyColumns[$key->getName()] = true;
			}
			$object = call_user_func_array([$query::create(), "findPk"], array_intersect($keyColumns, $columns));
			$object->fromArray($columns, TableMap::TYPE_FIELDNAME);
			$object->save();
		} else if ($action === self::ACT_DELETE) {
			$query = $type . "Query";
			$keys = $tableMap->getPrimaryKeys();
			$keyColumns = [];
			foreach ($keys as $key) {
				$keyColumns[$key->getName()] = true;
			}
			$object = call_user_func_array([$query::create(), "findPk"], array_intersect($keyColumns, $columns));
			$object->delete();
		}
		return $object;
	}

	/**
	 * Checks to see if the user can modify
	 * @param string $table
	 * @param string $action
	 * @param array $columns
	 * @return boolean
	 * @throws Exception
	 */
	function canDoAction($table, $action, $columns = null) {
		if (!$table) {
			return false;
		}
		if (!in_array($action, self::$actions)) {
			throw new Exception("Did not specify a valid action.");
		}
		$user = $this->sentry->getUser();
		if (!$user) {
			throw new Exception("No user is logged in.");
		} else {
			$permissions = $user->getMergedPermissions();
		}
		$key = "api/$table/$action";
		if (isset($permissions[$key]) && $permissions[$key] === 1) {
			return true;
		} else {
			if (empty($columns)) {
				return false;
			}
			foreach ($columns as $column) {
				$key = "api/$table/$action/$column";
				if (!isset($permissions[$key]) || $permissions[$key] !== 1) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Add an action for a certain group.
	 * @param string $groupName
	 * @param string $table
	 * @param string $action
	 * @param array $columns
	 */
	function addAction($groupName, $table, $action, $columns = null) {
		$this->modifyAction($groupName, 1, $table, $action, $columns);
	}

	/**
	 * Remove an action for a certain group.
	 * @param string $groupName
	 * @param string $table
	 * @param string $action
	 * @param array$columns
	 */
	function removeAction($groupName, $table, $action, $columns = null) {
		$this->modifyAction($groupName, -1, $table, $action, $columns);
	}

	protected function modifyAction($groupName, $permission, $table, $action, $columns = null) {
		$group = $this->sentry->findGroupByName($groupName);
		if (!$group) {
			throw Exception("Requested group is not found.");
		}
		if ($permission === 1) {
			$permissions = [];
			if (empty($columns) || $action === self::ACT_DELETE) {
				$permissions["api/$table/$action"] = 1;
			} else {
				foreach ($columns as $column) {
					$permissions["api/$table/$action/$column"] = 1;
				}
			}
			$group->permissions = array_merge($group->permissions, $permissions);
		} else if ($permission === -1) {
			if (empty($columns) || $action === self::ACT_DELETE) {
				unset($permissions["api/$table/$action"]);
			} else {
				foreach ($columns as $column) {
					unset($permissions["api/$table/$action/$column"]);
				}
			}
		}
		$group->save();
	}

}
