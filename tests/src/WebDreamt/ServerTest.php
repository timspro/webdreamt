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
		self::$server->allow('Administrator', ['driver', 'location'], ['create', 'update', 'delete']);
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
		$this->truncateTables(["driver", "location"]);
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

	/**
	 * @group Server
	 */
	public function testBatch() {
		self::$sentry->authenticate([
			'email' => 'admin@email.com',
			'password' => 'test'
		]);

		self::$server->batch([
			"1" => 'driver',
			"1-first_name" => "Johnny",
			"1-last_name" => "Smith",
			"1-salary" => '10000.50',
			"2" => 'driver',
			"2-first_name" => "Alex",
			"2-last_name" => "Smith",
			"2-salary" => '20000.50',
			'3' => 'location',
			'3-city' => 'Richmond',
			'3-state' => 'Virginia',
			'3-zip' => '10000',
			'3-street_address' => 'A Road'
		]);

		$data = $this->all('SELECT id, salary FROM driver ORDER BY salary');
		$id = $data[0]['id'];
		$this->assertEquals('10000.50', $data[0]['salary']);
		$this->assertEquals('20000.50', $data[1]['salary']);

		self::$server->batch([
			"4" => 'location',
			"4-city" => 'Richmond',
			"4-state" => 'California',
			'4-zip' => "20000",
			'4-street_address' => 'Monkey Lane',
			'5' => 'driver',
			'5-id' => $id,
			'5-first_name' => 'James',
			'5-last_name' => "Monroe",
			'5-salary' => '30000.50'
		]);

		$this->is('SELECT salary FROM driver WHERE id = ' . $id, '30000.50');
		$this->assertEquals(2, $this->countRows('location'));
	}

}
