<?php

namespace WebDreamt;

use ReflectionMethod;
require_once __DIR__ . '/../../bootstrap.php';

class BuildTest extends Test {

	/** @var Builder */
	protected static $build;
	protected static $baseDir;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$build = self::$a->build();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		parent::nuke();
		self::$build->removeDirectory(self::$build->GeneratedMigrations);
		self::$build->removeDirectory(self::$build->GeneratedDatabase);
		self::$build->removeDirectory(self::$build->GeneratedClasses);
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
		$dir = new \DirectoryIterator(self::$build->GeneratedClasses);
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

		shell_exec("rm -rf " . $build->GeneratedMigrations);

		$name = self::$a->DatabaseName;
		$password = self::$a->DatabasePassword;
		$host = self::$a->DatabaseHost;
		$username = self::$a->DatabaseUsername;
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
