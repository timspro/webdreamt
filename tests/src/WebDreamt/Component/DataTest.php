<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper\Data;
use WebDreamt\Component\Wrapper\Group;
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

	function setUp() {
		$this->set(Data::class);
	}

	/**
	 * @group ComData
	 */
	function testBasic() {
		$customers = $this->all('SELECT * FROM customer LIMIT 10');
		$data = new Data('customer', new Component('td'), 'tr', 'test', 'data-test=""');
		$group = new Group($data, 'table');
		$output = $group->render($customers);
		$this->checkCount($output, [
			'tr' => 10,
			'.test' => 10,
			'[data-test=""]' => 10,
			'td' => 10 * count($customers[0])
		]);
		$this->assertEquals('Customers', $group->getTitle());
		$this->assertEquals($data->getTableName(), 'customer');
		$this->assertEquals(array_keys($customers[0]), $data->getColumnNames());
	}

	/**
	 * @group ComData
	 */
	function testExtraComponent() {
		$first = new Component('div', 'first', 'data-test=""');
		$data = new Data('customer', null, 'div', 'second');
		$third = new Component('div', 'third', 'data-test=""');
		$this->ret($data->addExtraComponent($first)->addExtraComponent($third));
		$this->ret($data->addExtraComponent(new Component('span', 'x', null, 'x'), 'phone'));
		$data->addExtraComponent(new Component('span', 'y', null, 'y'), 'phone');
		$data->addExtraComponent(new Component('span', 'hint', null, 'Select '), '');
		$data->setChildComponentIndex(1);
		$data->getComponents([$first, $data, $third]);
		$data->getColumnComponents();
	}

	function testOptions() {

	}

	function testAlias() {

	}

	function testReorder() {

	}

	function testDataClass() {

	}

	function testDateFormat() {

	}

	function testLabel() {
		$data = new Data();
	}

	function testLink() {

	}

}
