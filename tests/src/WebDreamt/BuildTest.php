<?php

namespace WebDreamt;

use DirectoryIterator;
use WebDreamt\Test\DatabaseTest;
require_once __DIR__ . '/../../bootstrap.php';

class BuildTest extends DatabaseTest {

	protected static $baseDir;

	/**
	 * @group Build
	 */
	function testBuild() {
		self::$box->db()->exec("DROP DATABASE " . self::$box->DatabaseName);
		self::$build->build();
		$this->assertGreaterThan(0, $this->countTables());
	}

	/**
	 * @group Build
	 */
	function testUpdatePropel() {
		$this->createTable();
		$this->createTable();
		$this->createTable();
		self::$build->updatePropel();
		$dir = new DirectoryIterator(self::$build->GeneratedClasses);
		$count = 0;
		foreach ($dir as $file) {
			if ($file->isFile()) {
				$count++;
			}
		}
		$this->assertEquals(3, $count);
		$file = file_get_contents(self::$build->BuildSchema);
		preg_match('/size="\[([^\"]*)\]"/', $file, $check);
		$this->assertEquals("'male','female'", $check[1]);
	}

	/**
	 * @group Build
	 */
	function testUpdateDatabase() {
		$build = self::$build;
		$build->build();

		$name = self::$box->DatabaseName;
		$password = self::$box->DatabasePassword;
		$host = self::$box->DatabaseHost;
		$username = self::$box->DatabaseUsername;
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

	/**
	 * @group Build
	 */
	function testDeleteData() {
		$build = self::$build;
		parent::createTable("addtest");
		$db = self::$box->db();
		$stmt = $db->prepare("INSERT INTO addtest(letters, number, big)"
				. " VALUES('helo',2,''),('money',5,'town'),('big',8,'tes');");
		$stmt->execute();
		$this->assertEquals($this->countRows("addtest"), 3);
		$build->deleteData();
		$this->assertEquals($this->countRows("addtest"), 0);
	}

	/**
	 * @group Build
	 */
	function testManyToMany() {
		self::$db->exec("CREATE TABLE red (id INT PRIMARY KEY AUTO_INCREMENT);"
				. "CREATE TABLE blue (id INT PRIMARY KEY AUTO_INCREMENT);"
				. "CREATE TABLE red_blue (red_id INT, blue_id INT,
					FOREIGN KEY (red_id) REFERENCES red(id),
					FOREIGN KEY (blue_id) REFERENCES blue(id),
					PRIMARY KEY(red_id, blue_id));");
		self::$build->updatePropel();
		$count = substr_count(file_get_contents(self::$build->BuildSchema), "isCrossRef");
		$this->assertEquals(1, $count);
	}

	/**
	 * Check to see that Build::guarantee works when the generated schema is missing.
	 * @group Build
	 */
	function testGuaranteeWithSchema() {

	}

	/**
	 * Check to see that Build::guarantee works when the generated schema is old.
	 * @group Build
	 */
	function testGuaranteeWithoutSchema() {

	}

}
