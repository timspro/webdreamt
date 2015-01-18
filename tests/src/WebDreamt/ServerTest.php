<?php

namespace WebDreamt;

use Cartalyst\Sentry\Sentry;
use WebDreamt\Test;
require_once __DIR__ . '/../../bootstrap.php';

class ServerTest extends Test {

	/** @var Builder */
	protected static $build;
	/** @var Server */
	protected static $server;
	/** @var Sentry */
	protected static $sentry;

	static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::setUpSchema();
		self::$sentry = self::$box->sentry();
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
		self::$server = self::$box->server();
		$tables = ['agent', 'location', 'contract', 'service_contract', 'service', 'customer'];
		self::$server->allow('Administrator', $tables, ['create', 'update', 'delete']);
	}

	static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::tearDownDatabase();
	}

	protected function tearDown() {
		$this->truncateTables(["agent", "location"]);
	}

	/**
	 * @group Server
	 */
	function testOkay() {
		self::$sentry->authenticate([
			'email' => 'admin@email.com',
			'password' => 'test'
		]);
		$object = self::$server->run('agent', 'create', [
			'first_name' => "John",
			'last_name' => 'Smith',
			'salary' => '1000'
		]);
		$this->assertEquals(1, $this->countRows('agent'));
		self::$server->run('agent', 'update', [
			'id' => $object->getId(),
			'first_name' => "Peter"
		]);
		$this->inColumn("SELECT first_name FROM agent", "Peter");
		self::$server->run('agent', 'delete', ['id' => $object->getId()]);
		$this->assertEquals(0, $this->countRows('agent'));
	}

	/**
	 * @group Server
	 */
	function testInvalidCreate() {
		self::$sentry->authenticate([
			'email' => 'user@email.com',
			'password' => 'test'
		]);
		$this->setExpectedException('Exception');
		self::$server->run('agent', 'create', [
			'first_name' => "John",
			'last_name' => 'Smith',
			'salary' => '1000'
		]);
	}

	/**
	 * @group Server
	 */
	function testInvalidUpdate() {
		self::$sentry->authenticate([
			'email' => 'user@email.com',
			'password' => 'test'
		]);
		$this->setExpectedException('Exception');
		self::$server->run('agent', 'update', [
			'id' => '1',
			'first_name' => "Peter",
		]);
	}

	/**
	 * @group Server
	 */
	function testInvalidDelete() {
		self::$sentry->authenticate([
			'email' => 'user@email.com',
			'password' => 'test'
		]);
		$this->setExpectedException('Exception');
		self::$server->run('agent', 'delete', [
			'id' => '1'
		]);
	}

	/**
	 * @group Server
	 */
	function testBatchSimple() {
		self::$sentry->authenticate([
			'email' => 'admin@email.com',
			'password' => 'test'
		]);

		self::$server->batch([
			"1" => 'agent',
			"1.first_name" => "Johnny",
			"1.last_name" => "Smith",
			"1.salary" => '10000.50',
			"2" => 'agent',
			"2.first_name" => "Alex",
			"2.last_name" => "Smith",
			"2.salary" => '20000.50',
			'3' => 'location',
			'3.city' => 'Richmond',
			'3.state' => 'Virginia',
			'3.zip' => '10000',
			'3.street_address' => 'A Road'
		]);

		$data = $this->all('SELECT id, salary FROM agent ORDER BY salary');
		$id = $data[0]['id'];
		$this->assertEquals('10000.50', $data[0]['salary']);
		$this->assertEquals('20000.50', $data[1]['salary']);

		self::$server->batch([
			"4" => 'location',
			"4.city" => 'Richmond',
			"4.state" => 'California',
			'4.zip' => "20000",
			'4.street_address' => 'Monkey Lane',
			'5' => 'agent',
			'5.id' => $id,
			'5.first_name' => 'James',
			'5.last_name' => "Monroe",
			'5.salary' => '30000.50'
		]);

		$this->is('SELECT salary FROM agent WHERE id = ' . $id, '30000.50');
		$this->assertEquals(2, $this->countRows('location'));
	}

	/**
	 * @group ServerTest
	 */
	function testBatchWith() {
		self::$sentry->authenticate([
			'email' => 'admin@email.com',
			'password' => 'test'
		]);

		$date = '1990-10-20 11:27:33';
		self::$server->batch([
			"1" => 'contract',
			"1.location_id" => "",
			"1.seller_customer_id" => "",
			"1.buyer_customer_id" => '',
			"1.created_at" => $date,
			"2" => 'location',
			"2.with.1" => 'contract.location_id',
			"2.street_address" => "100 Main Branch",
			"2.city" => "Arlington",
			"2.state" => 'Virginia',
			"2.zip" => '21500',
			'3' => 'customer',
			'3.with.1' => 'contract.buyer_customer_id',
			'3.first_name' => 'John',
			'3.last_name' => 'Smith',
			'4' => 'customer',
			'4.with.1' => 'contract.seller_customer_id',
			'4.first_name' => 'Jane',
			'4.last_name' => 'Doe',
			'5' => 'service_contract',
			'5.with.1' => 'service_contract.contract_id',
			'5.contract_id' => '',
			'5.service_id' => '',
			'6' => 'service',
			'6.with.5' => 'service_contract.service_id',
			'6.name' => 'Inspection',
			'6.price' => '5000.00'
		]);

		$contract = \ContractQuery::create()->filterByCreatedAt($date)->find()[0];
		$location = $contract->getLocation();
		$this->assertEquals('Arlington', $location->getCity());
		$buyer = $contract->getCustomerRelatedByBuyerCustomerId();
		$this->assertEquals('John', $buyer->getFirstName());
		$this->assertEquals('Smith', $buyer->getLastName());
		$seller = $contract->getCustomerRelatedBySellerCustomerId();
		$this->assertEquals('Jane', $seller->getFirstName());
		$this->assertEquals('Doe', $seller->getLastName());
		$service = $contract->getServices()[0];
		$this->assertEquals('Inspection', $service->getName());
		$this->assertEquals('5000.00', $service->getPrice());

		//Update
		$date = '1991-12-19 15:10:20';
		self::$server->batch([
			"1" => 'contract',
			"1.id" => '1',
			"1.location_id" => "",
			"1.seller_customer_id" => "",
			"1.buyer_customer_id" => '',
			"1.created_at" => $date,
			"2" => 'location',
			"2.id" => '1',
			"2.with.1" => 'contract.location_id',
			"2.street_address" => "100 Main Branch",
			"2.city" => "Leesburg",
			"2.state" => 'Virginia',
			"2.zip" => '21500',
			'3' => 'customer',
			'3.id' => '1',
			'3.first_name' => 'John',
			'3.last_name' => 'Ay',
			'4' => 'customer',
			'4.with.1' => 'contract.seller_customer_id',
			'4.id' => '2',
			'4.first_name' => 'Jane',
			'4.last_name' => 'Bee',
			'5' => 'service_contract',
			'5.with.1' => 'service_contract.contract_id',
			'5.contract_id' => '',
			'5.service_id' => '',
			'5.in_database' => '1',
			'6' => 'service',
			'6.id' => '1',
			'6.with.5' => 'service_contract.service_id',
			'6.name' => 'Inspection',
			'6.price' => '6000.00'
		]);

		$contract = \ContractQuery::create()->filterByCreatedAt($date)->find()[0];
		$location = $contract->getLocation();
		$this->assertEquals('Leesburg', $location->getCity());
		$buyer = $contract->getCustomerRelatedByBuyerCustomerId();
		$this->assertEquals('John', $buyer->getFirstName());
		$this->assertEquals('Ay', $buyer->getLastName());
		$seller = $contract->getCustomerRelatedBySellerCustomerId();
		$this->assertEquals('Jane', $seller->getFirstName());
		$this->assertEquals('Bee', $seller->getLastName());
		$service = $contract->getServices()[0];
		$this->assertEquals('Inspection', $service->getName());
		$this->assertEquals('6000.00', $service->getPrice());

		$this->assertEquals(1, $this->countRows('contract'));
		$this->assertEquals(1, $this->countRows('location'));
		$this->assertEquals(2, $this->countRows('customer'));
		$this->assertEquals(1, $this->countRows('service'));
		$this->assertEquals(1, $this->countRows('service_contract'));
	}

}
