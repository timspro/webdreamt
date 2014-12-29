<?php

namespace WebDreamt;

use WebDreamt\Test;
require_once __DIR__ . '/../../bootstrap.php';

class BoxTest extends Test {

	/**
	 * @group Box
	 */
	function testSentry() {
		$this->assertInstanceOf('\Cartalyst\Sentry\Sentry', self::$box->sentry());
	}

	/**
	 * @group Box
	 */
	function testPdo() {
		$pdo = self::$box->db();
		$dbName = self::$box->DatabaseName;
		$this->assertInstanceOf('\PDO', $pdo);
		$this->inColumn("SHOW DATABASES", $dbName);
		$this->is("SELECT DATABASE()", $dbName);
	}

}
