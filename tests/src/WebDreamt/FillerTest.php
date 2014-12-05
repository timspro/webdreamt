<?php

namespace WebDreamt;

use Faker\Factory;
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
		self::$a->filler()->addData(["bigger" => 100]);

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
		$sql = file_get_contents(__DIR__ . '/test.sql');
		self::$a->db()->exec($sql);
		self::$build->updatePropel();
		self::$build->loadAllClasses();
		$generator = Factory::create();
		self::$a->filler()->addData([
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
				], true, [
			"vehicles" => [
				'mileage_oil_last' => function () use ($generator ) {
					return $generator->numberBetween(0, 20000);
				}
			]
		]);

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
		self::$a->filler()->addData(["Bigger" => 100]);
	}

	/**
	 * @group Filler
	 * @expectedException Exception
	 */
	public function testBadColumnName() {
		$this->createTable("bigger");

		self::$build->updatePropel();
		self::$build->loadAllClasses();
		self::$a->filler()->addData(["bigger" => 100], false, ["bigger" => ["Bigs" => null]]);
	}

}
