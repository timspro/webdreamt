<?php

namespace WebDreamt;

use PDO;
use PHPUnit_Framework_TestCase;

abstract class Test extends PHPUnit_Framework_TestCase {

	/** @var Box */
	protected static $a;
	/** @var PDO */
	protected static $db;
	private $id = 0;

	public static function setUpBeforeClass() {
		self::$a = new Box;
		self::$db = self::$a->db();
		self::$db->exec("CREATE DATABASE IF NOT EXISTS test; USE test");
		self::$a->DatabaseName = "test";
	}

	public function createTable($name = '') {
		if (!$name) {
			$name = "table" . strval($this->id);
			$this->id++;
		}
		self::$db->exec("CREATE TABLE $name (id INT PRIMARY KEY AUTO_INCREMENT," .
				" letters VARCHAR(20), number INT, big TEXT, appt DATE)");
	}

	public function countTables() {
		return count(self::$db->query("SHOW TABLES")->fetchAll());
	}

	public function all($query) {
		return self::$db->query($query)->fetchAll();
	}

	public function column($query) {
		return self::$db->query($query)->fetchAll(PDO::FETCH_COLUMN);
	}

	public function inColumn($query, $value) {
		$this->assertContains($value, $this->column($query));
	}

	public function is($query, $value) {
		$this->assertEquals($value, $this->column($query)[0]);
	}

	public function countRows($table) {
		return intval(self::$db->query("SELECT COUNT(*) FROM $table")->fetchAll(PDO::FETCH_COLUMN)[0]);
	}

	public function forTables($callable) {
		$tables = self::$db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table) {
			$callable($table);
		}
	}

}
