<?php

namespace WebDreamt\Test;

use DOMDocument;
use DOMNode;
use DOMText;
use DOMXPath;
use PDO;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
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
	protected $ret;

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
	public function countElements($output, $selectors, $count = null) {
		$doc = new DOMDocument();
		$doc->loadHTML($output);
		$xpath = new DOMXPath($doc);
		if (!is_array($selectors)) {
			$selectors = [$selectors => $count];
		}
		foreach ($selectors as $selector => $count) {
			$convert = CssSelector::toXPath($selector);
			$this->assertEquals($count, $xpath->query($convert)->length);
		}
	}

	/**
	 * Check the index of the elements that match the selector.
	 * @param string $output HTML
	 * @param string|array $selectors Can be an array, in which case $index is ignored.
	 * @param int $index
	 */
	public function indexElements($output, $selectors, $index = null) {
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
	public function html($output, $selectors, $html = null) {
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
				$this->assertEquals($html, $this->innerHtml($element), $selector);
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
	public function method($obj, $method, $args = array()) {
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
	function innerHtml(DOMNode $element) {
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
	function setRet($class) {
		$this->ret = $class;
	}

}
