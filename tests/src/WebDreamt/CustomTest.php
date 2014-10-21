<?php

namespace WebDreamt;

require_once __DIR__ . '/../../bootstrap.php';

class CustomTest extends Test {

	public function testGetDbName() {
		$this->assertEquals("webdreamt", self::$a->get("dbName"));
	}

	public function testGetDbHost() {
		$this->assertEquals("localhost", self::$a->get("dbHost"));
	}

	public function testGetDbUsername() {
		$this->assertEquals("root", self::$a->get("dbUsername"));
	}

	public function testGetDbPassword() {
		$this->assertEquals("", self::$a->get("dbPassword"));
	}

	public function testSentry() {
		$this->assertInstanceOf('\Cartalyst\Sentry\Sentry', self::$a->sentry());
	}

	public function testPdo() {
		$pdo = self::$a->db();
		$dbName = self::$a->get("dbName");
		$this->assertInstanceOf('\PDO', $pdo);
		$this->inColumn("SHOW DATABASES", $dbName);
		$this->is("SELECT DATABASE()", $dbName);
	}

}
