<?php

namespace WebDreamt;

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
		self::$a->filler()->addData(["Bigger" => 100]);

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
		self::$a->filler()->addData([
			"Job" => 10,
			"Service" => 10,
			"ServiceJob" => 5,
			"Customer" => 10,
			"Location" => 10,
			"CustomerLocation" => 5,
			"Driver" => 10,
			"Groups" => 0,
			"Users" => 0,
			"UsersGroups" => 0,
			"Job" => 20,
			"Vehicles" => 10
				], true);
	}

}
