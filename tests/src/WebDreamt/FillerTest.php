<?php

namespace WebDreamt;

use Exception;
use Faker\Factory;
use WebDreamt\Test\DatabaseTest;
require_once __DIR__ . '/../../bootstrap.php';

class FillerTest extends DatabaseTest {

	/**
	 * @group Filler
	 */
	function testAddData() {
		for ($i = 0; $i < 10; $i++) {
			$this->createTable();
		}
		$this->createTable("bigger");

		self::$build->updatePropel();
		self::$build->loadAllClasses();
		self::$box->filler()->setNumber(["bigger" => 100])->addData();

		$this->forTables(function($table) {
			$this->assertGreaterThan(0, $this->countRows($table));
		});

		$this->assertEquals($this->countRows("bigger"), 100);
		//Check enum.
		$this->inColumn('SELECT gender FROM bigger', 'male');

		$bigger = new \Bigger();
		$bigger->setGender('female');
		$bigger->save();
		$id = $bigger->getId();
		$bigger2 = \BiggerQuery::create()->findPk($id);
		$this->assertEquals($bigger2->getGender(), 'female');
	}

	/**
	 * @group Filler
	 */
	function testBigData() {
		self::setUpSchema();
		$generator = Factory::create();

		self::$box->filler()->setNumber([
			"contract" => 20,
			"service" => 10,
			"service_contract" => 5,
			"customer" => 10,
			"location" => 10,
			"agent" => 10,
			"groups" => 0,
			"users" => 0,
			"users_groups" => 0,
				], true)->setRules([
			"agent" => [
				'salary' => function () use ($generator ) {
					return $generator->numberBetween(0, 20000);
				}
			]
		])->addData();

		$salaries = $this->column('SELECT salary FROM agent');
		foreach ($salaries as $salary) {
			$this->assertLessThan(20000, $salary);
		}

		$types = $this->column('SELECT type FROM customer');
		$this->assertNotContains('', $types);
	}

	/**
	 * @group Filler
	 * @expectedException Exception
	 */
	function testBadTableName() {
		$this->createTable("bigger");

		self::$build->updatePropel();
		self::$build->loadAllClasses();
		self::$box->filler()->setNumber(["Bigger" => 100])->addData();
	}

	/**
	 * @group Filler
	 * @expectedException Exception
	 */
	function testBadColumnName() {
		$this->createTable("bigger");

		self::$build->updatePropel();
		self::$build->loadAllClasses();
		self::$box->filler()->setNumber([
			"bigger" => 100
		])->setRules([
			"bigger" => ["Bigs" => null]
		])->addData();
	}

}
