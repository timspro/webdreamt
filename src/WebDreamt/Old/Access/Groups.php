<?php

namespace WebDreamt\Access;

use PDO;
use WebDreamt\Common\Object;
use WebDreamt\Settings;

/**
 * Provides methods to access group information in the database.
 */
class Groups extends Object {
	/* @var $columns array */

	private $columns;

	/* @var $pdo PDO */
	private $pdo;

	/**
	 *
	 */
	public function __construct() {
		$settings = Settings::get();

		$this->pdo = $settings->PDO();
		$this->columns = $settings->getSetup("Group");
	}

	public function all() {
		$p = $this->columns;
	}

	public function permissionsForTable($table_name) {

	}

	public function permissionForGroup($group_id) {

	}

	public function hasPermission($user_id, $column_ids, $action) {
		$my_groups = forUsers($user_id, $action);
		$needed_groups = forColumns($column_ids, $action);
		if (count($needed_groups) === count(array_intersect($my_groups, $needed_groups))) {
			return true;
		}
		return false;
	}

	/**
	 * Get the groups for the users.
	 * @param array $user_ids
	 * @param string $action
	 */
	public function forUsers($user_ids, $action) {

	}

	/**
	 * Get the groups needed to modify the columns.
	 * @param array $column_ids
	 */
	public function forColumns($column_ids, $action) {

	}

	public function create($label, $comment = "") {

	}

	public function update($id, $label = "", $comment = "") {
		$this->pdo->prepare('UPDATE ' . $p['group_table'] . ' SET ' .
				$p['group_label'] . ' = ' . '  WHERE id = ' . intval($id));
		$this->pdo->execute();
	}

	public function delete($id) {
		$this->pdo->prepare('DELETE FROM ' . $p["group_table"] . ' WHERE id = ' . intval($id));
		$this->pdo->execute();
	}

	public static function createTable() {
		$p = Settings::get()->getSetup("Database Columns");
		$q = Settings::get()->PDO()->prepare(
				'CREATE TABLE IF NOT EXISTS ' . $p["group_table"] . ' ( ' .
				$p["group_id"] . ' INT PRIMARY KEY AUTO_INCREMENT, ' .
				$p["group_label"] . ' VARCHAR(20) NOT NULL, ' .
				$p["group_comment"] . ' TEXT ' .
				' ) '
		);
		$q->execute();
	}

}
