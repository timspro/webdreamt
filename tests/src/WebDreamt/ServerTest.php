<?php

namespace WebDreamt;

use Cartalyst\Sentry\Sentry;
require_once __DIR__ . '/../../bootstrap.php';

class ServerTest extends Test {

	/** @var Builder */
	protected static $build;
	/** @var Server */
	protected static $server;
	/** @var Sentry */
	protected static $sentry;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$sql = file_get_contents(__DIR__ . '/test.sql');
		self::$a->db()->exec($sql);
		self::$build = self::$a->builder();
		$build = self::$build;
		$build->updatePropel();
		require_once __DIR__ . "/../../../db/Propel/generated-conf/config.php";
		$build->loadAllClasses();
		/*
		  self::$a->filler()->addData([
		  "Job" => 10,
		  "Service" => 10,
		  "ServiceJob" => 5,
		  "Customer" => 10,
		  "Location" => 10,
		  "CustomerLocation" => 5,
		  "Driver" => 10,
		  "Groups" => 0,
		  "Users" => 0,
		  "UsersGroups" => 0,
		  "Job" => 20,
		  "Vehicles" => 10
		  ]);
		 */
		self::$sentry = self::$a->sentry();
		$sentry = self::$sentry;
		$sentry->getThrottleProvider()->disable();
		$sentry->createGroup([
			'name' => 'Administrator'
		]);
		$sentry->createGroup([
			'name' => 'User'
		]);
		$admin = $sentry->createUser([
			'email' => 'admin@email.com',
			'password' => 'test',
			'activated' => true
		]);
		$admin->addGroup($sentry->findGroupByName('Administrator'));
		$user = $sentry->createUser([
			'email' => 'user@email.com',
			'password' => 'test',
			'activated' => true
		]);
		$user->addGroup($sentry->findGroupByName('User'));
		self::$server = self::$a->server();
		self::$server->addAction('Administrator', 'driver', 'create');
		self::$server->addAction('Administrator', 'driver', 'update');
		self::$server->addAction('Administrator', 'driver', 'delete');
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::$build->deleteDatabase();
		self::$build->removeDirectory(self::$build->DB);
	}

	protected function setUp() {
		parent::setUp();
	}

	protected function tearDown() {

	}

	/**
	 * @group Server
	 */
	public function testOkay() {
		self::$sentry->authenticate([
			'email' => 'admin@email.com',
			'password' => 'test'
		]);
		$object = self::$server->run('driver', 'create', [
			'first_name' => "John",
			'last_name' => 'Smith',
			'salary' => '1000'
		]);
		$this->assertEquals(1, $this->countRows('driver'));
		self::$server->run('driver', 'update', [
			'id' => $object->getId(),
			'first_name' => "Peter"
		]);
		$this->inColumn("SELECT first_name FROM driver", "Peter");
		self::$server->run('driver', 'delete', ['id' => $object->getId()]);
		$this->assertEquals(0, $this->countRows('driver'));
	}

	/**
	 * @group Server
	 */
	public function testInvalidCreate() {
		self::$sentry->authenticate([
			'email' => 'user@email.com',
			'password' => 'test'
		]);
		$this->setExpectedException('Exception');
		self::$server->run('driver', 'create', [
			'first_name' => "John",
			'last_name' => 'Smith',
			'salary' => '1000'
		]);
	}

	/**
	 * @group Server
	 */
	public function testInvalidUpdate() {
		self::$sentry->authenticate([
			'email' => 'user@email.com',
			'password' => 'test'
		]);
		$this->setExpectedException('Exception');
		self::$server->run('driver', 'update', [
			'id' => '1',
			'first_name' => "Peter",
		]);
	}

	/**
	 * @group Server
	 */
	public function testInvalidDelete() {
		self::$sentry->authenticate([
			'email' => 'user@email.com',
			'password' => 'test'
		]);
		$this->setExpectedException('Exception');
		self::$server->run('driver', 'delete', [
			'id' => '1'
		]);
	}

}
