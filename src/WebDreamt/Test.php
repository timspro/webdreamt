<?php

namespace WebDreamt;

use DOMDocument;
use DOMNode;
use DOMText;
use DOMXPath;
use PDO;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Symfony\Component\CssSelector\CssSelector;

/**
 * A better test base class that provides helpful methods and creates and uses a database.
 */
abstract class Test extends PHPUnit_Framework_TestCase {

	/** @var Box */
	protected static $box;
	/** @var PDO */
	protected static $db;
	/**
	 * @var int The ID for a new table.
	 */
	private $id = 0;
	/**
	 * A class name that can be used to check return values
	 * @var string
	 */
	protected $ret;

	static function setUpBeforeClass() {
		static::$box = new Box(false);
		static::$db = static::$box->db();
		static::$db->exec("CREATE DATABASE IF NOT EXISTS test; USE test");
		static::$box->DatabaseName = "test";
	}

	/**
	 * Creates a table with five columns.
	 * @param string $name
	 */
	function createTable($name = '') {
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
	function countTables() {
		return count(static::$db->query("SHOW TABLES")->fetchAll());
	}

	/**
	 * Get all columns for a query.
	 * @param string $query
	 * @return array
	 */
	function all($query) {
		return static::$db->query($query)->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Get a certain column for a query.
	 * @param string $query
	 * @return array
	 */
	function column($query) {
		return static::$db->query($query)->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Checks if the given value is in the results returned by the query.
	 * @param string $query
	 * @param string $value
	 */
	function inColumn($query, $value) {
		$array = $this->column($query);
		$this->assertContains($value, $array);
	}

	/**
	 * Check if the first result returned by the query is the value.
	 * @param string $query
	 * @param string $value
	 */
	function is($query, $value) {
		$this->assertEquals($value, $this->column($query)[0]);
	}

	/**
	 * Return the number of rows in the table.
	 * @param string $table
	 * @return int
	 */
	function countRows($table) {
		return intval(static::$db->query("SELECT COUNT(*) FROM $table")->fetchAll(PDO::FETCH_COLUMN)[0]);
	}

	/**
	 * Do a function for each table in the database.
	 * @param function $callable
	 */
	function forTables($callable) {
		$tables = static::$db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table) {
			$callable($table);
		}
	}

	/**
	 * Truncates each table in the array.
	 * @param string|array $tables
	 */
	function truncateTables($tables) {
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
	function deleteTables($tables) {
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
	 * Output HTML to a file.
	 * @param string $filename The filename to output to
	 * @param string $output The XML to output
	 */
	function output($filename, $output) {
		file_put_contents($filename, $output);
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->preserveWhiteSpace = false;
		$doc->loadHTML($output);
		$contents = $doc->saveXML($doc->documentElement, LIBXML_NOEMPTYTAG);
		file_put_contents($filename, $contents);
	}

	/**
	 * Count the number of elements in the HTML that match the selector.
	 * @param string $output
	 * @param string|array $selectors Can be an array, in which case $count is ignored.
	 * @param int $count
	 * @return int
	 */
	function checkCount($output, $selectors, $count = null) {
		$doc = new DOMDocument();
		$doc->loadHTML($output);
		$xpath = new DOMXPath($doc);
		if (!is_array($selectors)) {
			$selectors = [$selectors => $count];
		}
		foreach ($selectors as $selector => $count) {
			$convert = CssSelector::toXPath($selector);
			$this->assertEquals($count, $xpath->query($convert)->length, $selector);
		}
	}

	/**
	 * Check the index of the elements that match the selector.
	 * @param string $output HTML
	 * @param string|array $selectors Can be an array, in which case $index is ignored.
	 * @param int $index
	 */
	function checkIndex($output, $selectors, $index = null) {
		$doc = new DOMDocument();
		$doc->loadHTML($output);
		$xpath = new DOMXPath($doc);
		if (!is_array($selectors)) {
			$selectors = [$selectors => $index];
		}
		foreach ($selectors as $selector => $givenIndex) {
			$convert = CssSelector::toXPath($selector);
			$elements = $xpath->query($convert);
			$this->assertNotEquals(0, $elements->length, $selector);
			foreach ($elements as $element) {
				$index = 0;
				while ($element = $element->previousSibling) {
					if (!($element instanceof DOMText)) {
						$index++;
					}
				}
				$this->assertEquals($givenIndex, $index, $selector);
			}
		}
	}

	/**
	 * Check the HTML of the elements that match the selector.
	 * @param string $output
	 * @param string|array $selectors
	 * @param string $html
	 */
	function checkHtml($output, $selectors, $html = null) {
		$doc = new DOMDocument();
		$doc->loadHTML($output);
		$xpath = new DOMXPath($doc);
		if (!is_array($selectors)) {
			$selectors = [$selectors => $html];
		}
		foreach ($selectors as $selector => $html) {
			$convert = CssSelector::toXPath($selector);
			$elements = $xpath->query($convert);
			$this->assertNotEquals(0, $elements->length, $selector);
			foreach ($elements as $element) {
				$this->assertEquals($html, $this->getInnerHtml($element), $selector);
			}
		}
	}

	/**
	 * Class a private/protected method.
	 * @param mixed $obj
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	function getMethod($obj, $method, $args = array()) {
		if (!is_array($args)) {
			$args = [$args];
		}
		$method = new ReflectionMethod(get_class($obj), $method);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

	/**
	 * Get the inner HTML for a DOMNode element.
	 * @param DOMNode $element
	 * @return string
	 */
	function getInnerHtml(DOMNode $element) {
		$innerHTML = "";
		$children = $element->childNodes;
		foreach ($children as $child) {
			$innerHTML .= $element->ownerDocument->saveHTML($child);
		}
		return $innerHTML;
	}

	/**
	 * Tests if the passed object is an instance of the set class.
	 * @param mixed $test
	 */
	function ret($test) {
		$this->assertTrue($test instanceof $this->ret);
	}

	/**
	 * Set a class to test.
	 * @param string $class
	 */
	function set($class) {
		$this->ret = $class;
	}

	/**
	 * Setup the database by putting in example schemas and data.
	 */
	static function setUpDatabase() {
		static::setUpSchema();
		self::$box->filler()->setNumber([
			"job" => 20,
			"service" => 10,
			"service_job" => 5,
			"customer" => 10,
			"location" => 10,
			"customer_location" => 5,
			"driver" => 10,
			"groups" => 0,
			"users" => 0,
			"users_groups" => 0,
			"vehicles" => 10
				], true)->addData();
	}

	/**
	 * Setup the database schema. Does not add data.
	 */
	static function setUpSchema() {
		$sql = file_get_contents(__DIR__ . '/Test/test.sql');
		self::$box->db()->exec($sql);
		$build = self::$box->builder();
		$build->updatePropel();
		require_once __DIR__ . "/../../db/propel/generated-conf/config.php";
		$build->loadAllClasses();
	}

	/**
	 * Tear down the database and the database folder.
	 */
	static function tearDownDatabase() {
		$build = self::$box->builder();
		$build->deleteDatabase();
		$build->removeDirectory($build->DB);
	}

}
