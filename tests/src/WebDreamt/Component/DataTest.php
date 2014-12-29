<?php

namespace WebDreamt;

require_once __DIR__ . '/../../../bootstrap.php';

class DataTest extends Test {

	static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::setUpDatabase();
	}

	static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::tearDownDatabase();
	}

	/**
	 * @group ComData
	 */
	function testData() {

	}

}
