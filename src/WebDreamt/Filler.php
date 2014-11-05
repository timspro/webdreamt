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
	 * @param array $number Specifies the number to add for a given Propel class, where the Propel
	 * class's name is the key and the number to add is the value.
	 */
	public function addData($number = []) {
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
		foreach ($map->getTables() as $table) {
			$names[] = $table->getPhpName();
			foreach ($table->getForeignKeys() as $key) {
				$constraints[] = [$key->getRelatedTable()->getPhpName(), $key->getTable()->getPhpName()];
			}
		}

		$entities = Topological::sort($names, $constraints);
		foreach ($entities as $entity) {
			$value = 50;
			if (isset($number[$entity])) {
				$value = $number[$entity];
			}
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
				$populator->addEntity($entity, $value);
			}
		}
		$populator->execute();
	}

}
