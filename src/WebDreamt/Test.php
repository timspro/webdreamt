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
		static::$a = new Box;
		static::$db = static::$a->db();
		static::$db->exec("CREATE DATABASE IF NOT EXISTS test; USE test");
		static::$a->DatabaseName = "test";
	}

	public function createTable($name = '') {
		if (!$name) {
			$name = "table" . strval($this->id);
			$this->id++;
		}
		self::$db->exec("CREATE TABLE $name (id INT PRIMARY KEY AUTO_INCREMENT," .
				" letters VARCHAR(20), number INT, big TEXT, appt DATE, gender ENUM('male', 'female'))");
	}

	public function countTables() {
		return count(static::$db->query("SHOW TABLES")->fetchAll());
	}

	public function all($query) {
		return static::$db->query($query)->fetchAll();
	}

	public function column($query) {
		return static::$db->query($query)->fetchAll(PDO::FETCH_COLUMN);
	}

	public function inColumn($query, $value) {
		$array = $this->column($query);
		$this->assertContains($value, $array);
	}

	public function is($query, $value) {
		$this->assertEquals($value, $this->column($query)[0]);
	}

	public function countRows($table) {
		return intval(static::$db->query("SELECT COUNT(*) FROM $table")->fetchAll(PDO::FETCH_COLUMN)[0]);
	}

	public function forTables($callable) {
		$tables = static::$db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table) {
			$callable($table);
		}
	}

}
