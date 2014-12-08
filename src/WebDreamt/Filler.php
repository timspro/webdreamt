<?php

namespace WebDreamt;

use Faker\Factory;
use WebDreamt\Filler\Populator;
use WebDreamt\Filler\Topological;
use Propel\Runtime\Propel;

class Filler {

	private $vendor;

	public function __construct(Box $box) {
		$this->vendor = $box->VendorDirectory;
	}

	/**
	 * Adds test data to the database.
	 * @param array $numberConverted Specifies the number to add for a given Propel class, where the Propel
	 * class's name is the key and the number to add is the value.
	 * @param boolean $only If true, then only fills in the given tables.
	 * @param array $customConverted An array where keys are Propel class names and the values are arrays that
	 * consist of keys that are Propel names and values are functions of the form:
	 * <code>
	 * function () use ($generator) { return $generator->text; }
	 * </code>
	 * where $generator is a \Faker\Factory.
	 */
	public function addData($number = [], $only = false, $custom = []) {
		require_once $this->vendor . "../db/Propel/generated-conf/config.php";

		$generator = Factory::create();
		$populator = new Populator($generator);

		$constraints = [];
		$names = [];

		$mapDirectory = $this->vendor . "../db/Propel/generated-classes/Map/";
		foreach (array_diff(scandir($mapDirectory), array('..', '.')) as $file) {
			require_once $mapDirectory . $file;
		}

		$map = Propel::getDatabaseMap();
		$tables = $map->getTables();
		$numberConverted = [];
		$customConverted = [];
		foreach ($number as $tableName => $value) {
			$table = $map->getTable($tableName);
			$numberConverted[$table->getPhpName()] = $value;
		}

		foreach ($custom as $tableName => $array) {
			$table = $map->getTable($tableName);
			$tablePhp = $table->getPhpName();
			$customConverted[$tablePhp] = [];
			foreach ($array as $columnName => $value) {
				$column = $table->getColumn($columnName);
				if (!$column) {
					throw new Exception("Column $tableName.$columnName not known by Propel. "
					. "Is propel in sync with the database?");
				}
				$name = $column->getPhpName();
				$customConverted[$tablePhp][$name] = $value;
			}
		}

		$tables = $map->getTables();
		foreach ($tables as $table) {
			$name = $table->getPhpName();
			if (!$only || isset($numberConverted[$name])) {
				$names[] = $name;
				foreach ($table->getForeignKeys() as $key) {
					$constraints[] = [$key->getRelatedTable()->getPhpName(), $key->getTable()->getPhpName()];
				}
			}
		}

		$entities = Topological::sort($names, $constraints);
		foreach ($entities as $entity) {
			$value = 50;
			if (isset($numberConverted[$entity])) {
				$value = $numberConverted[$entity];
			}
			//echo $entity . "<br>";
			if ($entity === "Users") {
				$populator->addEntity($entity, $value, [
					"Permissions" => null,
					"ActivationCode" => null,
					"ActivatedAt" => null,
					"LastLogin" => null,
					"PersistCode" => null,
					"ResetPasswordCode" => null,
					"Activated" => function() use ($generator) {
						return $generator->boolean;
					}
				]);
			} elseif ($entity === "Groups") {
				$populator->addEntity($entity, $value, [
					"Permissions" => null
				]);
			} else {
				$extra = [];
				if (isset($customConverted[$entity])) {
					$extra = $customConverted[$entity];
				}
				$populator->addEntity($entity, $value, $extra);
			}
		}
		$populator->execute();
	}

}
