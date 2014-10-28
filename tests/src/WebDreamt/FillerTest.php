<?php

namespace WebDreamt;

require_once __DIR__ . '/../../bootstrap.php';

class FillerTest extends Test {

	/** @var Filler */
	protected static $filler;
	/** @var Builder */
	protected static $build;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$filler = self::$a->filler();
		self::$build = self::$a->builder();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::$build->deleteDatabase();
		self::$build->removeDirectory(self::$build->PropelProject . '../', true);
	}

	public function testAddData() {
		for ($i = 0; $i < 10; $i++) {
			$this->createTable();
		}
		$this->createTable("bigger");

		self::$build->updatePropel();
		$gen = self::$build->GeneratedClasses . "Base/";
		foreach (scandir($gen) as $file) {
			$require = $gen . $file;
			if ($file !== '.' && $file !== ".." && is_file($require)) {
				require_once $require;
			}
		}
		foreach (scandir(self::$build->GeneratedClasses) as $file) {
			$require = self::$build->GeneratedClasses . $file;
			if ($file !== '.' && $file !== ".." && is_file($require)) {
				require_once $require;
			}
		}

		self::$filler->addData(["Bigger" => 100]);

		$this->forTables(function($table) {
			$this->assertGreaterThan(0, $this->countRows($table));
		});

		$this->assertEquals($this->countRows("bigger"), 100);
	}

}
