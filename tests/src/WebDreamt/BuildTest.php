<?php

namespace WebDreamt;

require_once __DIR__ . '/../../bootstrap.php';

class BuildTest extends Test {

	/** @var Builder */
	protected static $build;
	protected static $baseDir;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$build = self::$a->builder();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::$build->deleteDatabase();
		self::$build->removeDirectory(self::$build->GeneratedMigrations);
		self::$build->removeDirectory(self::$build->GeneratedDatabase);
		self::$build->removeDirectory(self::$build->GeneratedClasses);
	}

	protected function setUp() {
		parent::setUp();
		self::$build->deleteDatabase();
	}

	protected function tearDown() {

	}

	public function testBuild() {
		self::$a->db()->exec("DROP DATABASE " . self::$a->DatabaseName);
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

		$build->removeDirectory($build->GeneratedMigrations);

		$name = self::$a->DatabaseName;
		$password = self::$a->DatabasePassword;
		$host = self::$a->DatabaseHost;
		$username = self::$a->DatabaseUsername;
		$goodOutput = shell_exec("mysqldump --no-data --skip-comments --user=$username --password=$password " .
				"--host=$host $name");
		$build->deleteDatabase();

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

	public function testDeleteData() {
		$build = self::$build;
		parent::createTable("addtest");
		$db = self::$a->db();
		$stmt = $db->prepare("INSERT INTO addtest(letters, number, big)"
				. " VALUES('helo',2,''),('money',5,'town'),('big',8,'tes');");
		$stmt->execute();
		$this->assertEquals($this->countRows("addtest"), 3);
		$build->deleteData();
		$this->assertEquals($this->countRows("addtest"), 0);
	}

	public function testManyToMany() {
		self::$db->exec("CREATE TABLE red (id INT PRIMARY KEY AUTO_INCREMENT);"
				. "CREATE TABLE blue (id INT PRIMARY KEY AUTO_INCREMENT);"
				. "CREATE TABLE red_blue (red_id INT, blue_id INT,
					FOREIGN KEY (red_id) REFERENCES red(id),
					FOREIGN KEY (blue_id) REFERENCES blue(id));");
		self::$build->updatePropel();
		$count = substr_count(file_get_contents(self::$build->BuildSchema), "isCrossRef");
		$this->assertEquals(1, $count);
	}

}
