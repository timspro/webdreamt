<?php

namespace WebDreamt;

require_once __DIR__ . '/../../bootstrap.php';

class BuildTest extends Test {

	/** @var Build */
	protected static $build;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$build = self::$a->build();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		parent::nuke();
		shell_exec("rm -rf " . self::$build->get("generatedMigrations"));
		shell_exec("rm -rf " . self::$build->get("generatedDatabase"));
		shell_exec("rm -rf " . self::$build->get("generatedClasses"));
	}

	protected function setUp() {
		parent::setUp();
		$this->nuke();
	}

	protected function tearDown() {

	}

	public function testBuild() {
		self::$build->build();
		$this->assertGreaterThan(0, $this->countTables());
	}

	public function testUpdatePropel() {
		$this->createTable();
		$this->createTable();
		$this->createTable();
		self::$build->updatePropel();
		$dir = new \DirectoryIterator(self::$build->get("generatedClasses"));
		$count = 0;
		foreach ($dir as $file) {
			if ($file->isFile()) {
				$count++;
			}
		}
		$this->assertEquals(6, $count);
	}

	public function testUpdateDatabase() {
		$build = self::$build;

		$build->build();

		shell_exec("rm -rf " . $build->get("generatedMigrations"));

		$name = self::$a->get("dbName");
		$password = self::$a->get("dbPassword");
		$host = self::$a->get("dbHost");
		$username = self::$a->get("dbUsername");
		$goodOutput = shell_exec("mysqldump --no-data --skip-comments --user=$username --password=$password " .
				"--host=$host $name");
		$this->nuke();

		$goodOutput = str_replace(" COLLATE utf8_unicode_ci", "", $goodOutput);
		$goodOutput = str_replace("timestamp", "datetime", $goodOutput);
		$goodOutput = str_replace(" COLLATE=utf8_unicode_ci", "", $goodOutput);
		$goodOutput = str_replace(" NULL DEFAULT NULL", " DEFAULT NULL", $goodOutput);

		$build->updateDatabase();
		self::$db->exec("DROP TABLE propel_migration");
		$maybeOutput = shell_exec("mysqldump --no-data --skip-comments --user=$username --password=$password " .
				"--host=$host $name");

		$this->assertEquals($goodOutput, $maybeOutput);
	}

}
