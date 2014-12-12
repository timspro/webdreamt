<?php

namespace WebDreamt;

use WebDreamt\Test\Test;
require_once __DIR__ . '/../../bootstrap.php';

class BoxTest extends Test {

	/**
	 * @group Box
	 */
	public function testSentry() {
		$this->assertInstanceOf('\Cartalyst\Sentry\Sentry', self::$a->sentry());
	}

	/**
	 * @group Box
	 */
	public function testPdo() {
		$pdo = self::$a->db();
		$dbName = self::$a->DatabaseName;
		$this->assertInstanceOf('\PDO', $pdo);
		$this->inColumn("SHOW DATABASES", $dbName);
		$this->is("SELECT DATABASE()", $dbName);
	}

}
