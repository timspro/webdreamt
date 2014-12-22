<?php

namespace WebDreamt\Test;

use WebDreamt\Builder;

/**
 * An extension of the Test class that truncates the database before every test and removes
 * the db directory.
 */
class DatabaseTest extends Test {

	/** @var Builder */
	protected static $build;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$build = self::$a->builder();
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
