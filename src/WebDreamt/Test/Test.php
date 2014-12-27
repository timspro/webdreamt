<?php

namespace WebDreamt\Test;

use DOMDocument;
use PDO;
use PHPUnit_Framework_TestCase;
use Symfony\Component\CssSelector\CssSelector;
use WebDreamt\Box;

/**
 * A better test base class that provides helpful methods and creates and uses a database.
 */
abstract class Test extends PHPUnit_Framework_TestCase {

	/** @var Box */
	protected static $a;
	/** @var PDO */
	protected static $db;
	private $id = 0;

	public static function setUpBeforeClass() {
		static::$a = new Box(false);
		static::$db = static::$a->db();
		static::$db->exec("CREATE DATABASE IF NOT EXISTS test; USE test");
		static::$a->DatabaseName = "test";
	}

	/**
	 * Creates a table with five columns.
	 * @param string $name
	 */
	public function createTable($name = '') {
		if (!$name) {
			$name = "table" . strval($this->id);
			$this->id++;
		}
		self::$db->exec("CREATE TABLE $name (id INT PRIMARY KEY AUTO_INCREMENT," .
				" letters VARCHAR(20), number INT, big TEXT, appt DATE, gender ENUM('male', 'female'))");
	}

	/**
	 * Count how many tables are in the database.
	 * @return string
	 */
	public function countTables() {
		return count(static::$db->query("SHOW TABLES")->fetchAll());
	}

	/**
	 * Get all columns for a query.
	 * @param string $query
	 * @return array
	 */
	public function all($query) {
		return static::$db->query($query)->fetchAll();
	}

	/**
	 * Get a certain column for a query.
	 * @param string $query
	 * @return array
	 */
	public function column($query) {
		return static::$db->query($query)->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Checks if the given value is in the results returned by the query.
	 * @param string $query
	 * @param string $value
	 */
	public function inColumn($query, $value) {
		$array = $this->column($query);
		$this->assertContains($value, $array);
	}

	/**
	 * Check if the first result returned by the query is the value.
	 * @param string $query
	 * @param string $value
	 */
	public function is($query, $value) {
		$this->assertEquals($value, $this->column($query)[0]);
	}

	/**
	 * Return the number of rows in the table.
	 * @param string $table
	 * @return int
	 */
	public function countRows($table) {
		return intval(static::$db->query("SELECT COUNT(*) FROM $table")->fetchAll(PDO::FETCH_COLUMN)[0]);
	}

	/**
	 * Do a function for each table in the database.
	 * @param function $callable
	 */
	public function forTables($callable) {
		$tables = static::$db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table) {
			$callable($table);
		}
	}

	/**
	 * Truncates each table in the array.
	 * @param string|array $tables
	 */
	public function truncateTables($tables) {
		if (!is_array($tables)) {
			$tables = [$tables];
		}
		foreach ($tables as $table) {
			static::$db->exec("TRUNCATE $table");
		}
	}

	/**
	 * Delete the tables in the database.
	 * @param string|array $tables
	 */
	public function deleteTables($tables) {
		if (!is_array($tables)) {
			$tables = [$tables];
		}
		$db = static::$db;
		$db->exec("SET FOREIGN_KEY_CHECKS=0");
		$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table) {
			$db->exec("DROP TABLE $table");
		}
		$db->exec("SET FOREIGN_KEY_CHECKS=1");
	}

	/**
	 * Output XML to a file.
	 * @param string $filename The filename to output to
	 * @param string $output The XML to output
	 */
	public function output($filename, $output) {
		file_put_contents($filename, $output);
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($output);
		$contents = $doc->saveXML($doc->documentElement, LIBXML_NOEMPTYTAG);
		file_put_contents($filename, $contents);
	}

	/**
	 * Count the number of elements in the HTML that match the selector.
	 * @param string $selector
	 * @param string $output
	 * @return int
	 */
	public function countElements($selector, $output) {
		$doc = new DOMDocument();
		$doc->loadHTML($output);
		$xpath = new DOMXpath($doc);
		$elements = $xpath->query(CssSelector::toXPath($selector));
		return count($elements);
	}

}
