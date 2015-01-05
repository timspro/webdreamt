<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper;
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
		$columnOptions = $data->getColumnOptions();
		print_r($customers[0]);
		foreach ($columnOptions as $column => $options) {
			echo $column . ' ';
			$this->assertEquals(true, array_key_exists($column, $customers[0]));
		}
	}

	/**
	 * @group ComData
	 */
	function testOptions() {
		$services = $this->all('SELECT * FROM service LIMIT 10');
		$data = new Data('service', null, 'body', null, null, $services);
		$this->ret($data->deny());
		$this->ret($data->allow('price'));
		$array = ['id' => false, 'name' => false, 'description' => false, 'price' => true];
		$this->assertEquals($array, $data->getOptions(null, Data::OPT_ACCESS));
		$output = $data->render();
		$this->checkCount($output, [
			'div' => 1
		]);
		$this->ret($data->allow());
		$this->ret($data->hide('id', 'description'));
		$array = ['id' => false, 'name' => true, 'description' => false, 'price' => true];
		$this->assertEquals($array, $data->getOptions(null, Data::OPT_VISIBLE));
		$output = $data->render();
		$this->checkCount($output, [
			'[style]' => 2,
			'div' => 4
		]);
		$this->ret($data->setLabels(['price' => 'cost']));
		$array = ["id" => 'ID', 'price' => 'cost'];
		$this->assertEquals($array, $data->getOptions(['id', 'price'], Data::OPT_LABEL));
		$this->ret($data->setLabelComponent(new Component('label')));
		$this->assertEquals(false, $data->getLabelable());
		$this->ret($data->setLabelable(true));
		$this->assertEquals(true, $data->getLabelable());
		$this->checkCount($data->render(), [
			'label' => 4,
			'[style]' => 4
		]);
		$data = new Data('customer');
		$this->ret($data->deny()->allow(['first_name', 'last_name', 'type']));
		$this->ret($data->setDefaultValues(['first_name' => 'John', 'last_name' => 'Smith']));
		$this->assertEquals(null, $data->getDataClass());
		$this->ret($data->setDataClass('wd'));
		$this->assertEquals('wd', $data->getDataClass());
		$output = $data->render();
		$this->checkHtml($output, [
			'.wd-first_name' => 'John',
			'.wd-last_name' => 'Smith',
			'.wd-type' => 'Person'
		]);
	}

	/**
	 * @group ComData
	 */
	function testDisplay() {
		$data = new Data('service');
		$service = $this->all('SELECT * FROM service LIMIT 1')[0];
		$data->setDataClass('wd');
		$output = $data->render($service);
		$this->checkHtml($output, [
			'.wd-id' => $service['id'],
			'.wd-name' => $service['name'],
			'.wd-description' => $service['description'],
			'.wd-price' => $service['price']
		]);
	}

	/**
	 * @group ComData
	 */
	function testSetMultipleOptions() {
		$data = new Data('service');
		$data->setOptions(['id', 'name', 'price'], Data::OPT_ACCESS, false);
		$service = $this->all('SELECT * FROM service LIMIT 1')[0];
		$data->setDataClass('wd');
		$output = $data->render($service);
		$this->checkCount($output, [
			'.wd-id' => 0,
			'.wd-name' => 0,
			'.wd-description' => 1,
			'.wd-price' => 0
		]);
		$data->setOptions(['id', 'name', 'price'], [Data::OPT_ACCESS => true, Data::OPT_VISIBLE => false]);
		$output = $data->render($service);
		$this->checkCount($output, [
			'.wd-id[style]' => 1,
			'.wd-name[style]' => 1,
			'.wd-description' => 1,
			'.wd-price[style]' => 1,
			'.wd-description[style]' => 0
		]);
		$this->assertEquals(false, $data->getOption('price', Data::OPT_VISIBLE));
		$this->ret($data->setOption('price', Data::OPT_VISIBLE, true));
		$this->assertEquals(true, $data->getOption('price', Data::OPT_VISIBLE));
	}

	/**
	 * @group ComData
	 */
	function testAlias() {

	}

	/**
	 * @group ComData
	 */
	function testReorder() {

	}

	/**
	 * @group ComData
	 */
	function testDateFormat() {
		$data = new Data('contract');
		$this->ret($);
		$this->ret();
		$this->ret();
	}

	/**
	 * @group ComData
	 */
	function testRenderLabel() {
		$data = new Data('service');
		$data->getLabelComponent()->setCssClass('wd-label');
		$labels = $data->renderLabels();
		$this->checkCount($labels, [
			'.wd-label' => 4
		]);
		$data->setLabelClass('wd');
		$labels = $data->renderLabels();
		$this->checkHtml($labels, [
			'.wd-id' => 'ID',
			'.wd-name' => 'Name',
			'.wd-description' => 'Description',
			'.wd-price' => 'Price'
		]);
	}

	/**
	 * @group ComData
	 */
	function testPropel() {
		//Include default values.
	}

	/**
	 * @group ComData
	 */
	function testLink() {

	}

	/**
	 * @group ComData
	 */
	function testFancy() {
		$service = $this->all('SELECT * FROM service LIMIT 1')[0];
		$data = new Data('service', new Wrapper(new Component('div', 'wd-data')));
		$label = $data->getLabelComponent()->setCssClass('wd-label');
		$data->setDataClass('wd')->getDisplayComponent()->addExtraComponent($label, false);
		$output = $data->render($service);
		$this->checkHtml($output, [
			'.wd-id > .wd-data' => $service['id'],
			'.wd-name > .wd-data' => $service['name'],
			'.wd-description > .wd-data' => $service['description'],
			'.wd-price > .wd-data' => $service['price'],
			'.wd-id > .wd-label' => 'ID',
			'.wd-name > .wd-label' => 'Name',
			'.wd-description > .wd-label' => 'Description',
			'.wd-price > .wd-label' => 'Price',
		]);
	}

}
