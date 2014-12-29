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
	public function testAddData() {
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
	public function testBigData() {
		self::setupSchema();
		$generator = Factory::create();
		self::$box->filler()->setNumber([
			"job" => 10,
			"service" => 10,
			"service_job" => 5,
			"customer" => 10,
			"location" => 10,
			"customer_location" => 5,
			"driver" => 10,
			"groups" => 0,
			"users" => 0,
			"users_groups" => 0,
			"job" => 20,
			"vehicles" => 10
				], true)->setRules([
			"vehicles" => [
				'mileage_oil_last' => function () use ($generator ) {
					return $generator->numberBetween(0, 20000);
				}
			]
		])->addData();

		$mileages = $this->column('SELECT mileage_oil_last FROM vehicles');
		foreach ($mileages as $mileage) {
			$this->assertLessThan(20000, $mileage);
		}

		$states = $this->column('SELECT billing_state FROM customer');
		$this->assertNotContains('', $states);
	}

	/**
	 * @group Filler
	 * @expectedException Exception
	 */
	public function testBadTableName() {
		$this->createTable("bigger");

		self::$build->updatePropel();
		self::$build->loadAllClasses();
		self::$box->filler()->setNumber(["Bigger" => 100])->addData();
	}

	/**
	 * @group Filler
	 * @expectedException Exception
	 */
	public function testBadColumnName() {
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
