<?php

namespace WebDreamt\Test;

use WebDreamt\Builder;
use WebDreamt\Test;

/**
 * An extension of the Test class that truncates the database before every test and removes
 * the db directory.
 */
class DatabaseTest extends Test {

	/** @var Builder */
	protected static $build;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$build = self::$box->builder();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::$build->deleteDatabase();
		self::$build->removeDirectory(self::$build->DB);
	}

	protected function setUp() {
		parent::setUp();
		self::$build->deleteDatabase();
		self::$build->removeDirectory(self::$build->DB);
		self::$build->setupFiles();
	}

}
