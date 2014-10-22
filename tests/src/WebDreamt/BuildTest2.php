<?php

namespace WebDreamt;

use ReflectionMethod;
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . "/BuildTest.php";

class BuildTest2 extends BuildTest {

	protected static $baseDir;

	public static function setUpBeforeClass() {
		//Call grandparent setUpBeforeClass method.
		$method = new ReflectionMethod(get_parent_class(get_parent_class()), 'setUpBeforeClass');
		$method->invoke(null);
		self::$baseDir = __DIR__ . "/base/";
		mkdir(self::$baseDir);
		self::$a->DatabaseDirectory = self::$baseDir;
		self::$build = self::$a->build();
	}

	public static function tearDownAfterClass() {
		self::$build->removeDirectory(self::$baseDir);
		rmdir(self::$baseDir);
		parent::tearDownAfterClass();
	}

}
