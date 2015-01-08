<?php

namespace WebDreamt;

use Propel\Runtime\ActiveQuery\Criteria;
use WebDreamt\Component\Custom;
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
		foreach ($columnOptions as $column => $options) {
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
		$this->ret($data->allow()->mergeOptions(['id' => false, 'name' => true, 'price' => false], Data::OPT_ACCESS));
		$output = $data->render($service);
		$this->checkCount($output, [
			'.wd-id' => 0,
			'.wd-name' => 1,
			'.wd-description' => 1,
			'.wd-price' => 0
		]);

		$this->setExpectedException('Exception');
		$data->setOptions(['a'], Data::OPT_ACCESS, false);
		$this->setExpectedException('Exception');
		$data->mergeOptions(['a' => true], Data::OPT_ACCESS);
	}

	/**
	 * @group ComData
	 */
	function testNullValues() {
		$agents = $this->all('SELECT * FROM agent LIMIT 10');
		$data = new Data('agent');
		$this->ret($data->setDataClass('data')->setNullValues(['agency' => 'None', 'salary' => 'Unknown']));
		$group = new Group($data);
		$group->setIndexClass('wd');
		$checks = [];
		foreach ($agents as $index => $agent) {
			if ($agent['agency'] === null) {
				$checks[".wd-$index .data-agency"] = 'None';
			}
			if ($agent['salary'] === null) {
				$checks[".wd-$index .data-salary"] = 'Unknown';
			}
		}
		$this->checkHtml($group->render($agents), $checks);
	}

	/**
	 * @group ComData
	 */
	function testSimpleLink() {
		$agents = $this->all('SELECT * FROM agent LIMIT 10');

		$data = new Data('agent');
		$this->assertEquals(true, empty($data->getLinkedComponents()));
		$describe = new Component('div', 'wd-label');
		$describe->setInput('We are not sure about this.');
		$this->ret($data->link('salary', $describe));
		$this->assertEquals($describe, $data->getLinkedComponents()['salary'][0]);
		$data->link('salary', $data->getDisplayComponent()->setCssClass('wd-data'));
		$data->setDataClass('wd-data');

		$group = new Group($data);
		$output = $group->render($agents);
		$this->checkHtml($output, [
			'.wd-data-salary.wd-label' => 'We are not sure about this.'
		]);
		$this->checkCount($output, [
			'.wd-data-salary.wd-label' => 10,
			'.wd-data-salary.wd-data' => 10,
		]);

		$data->unlink('salary');
		$this->checkCount($group->render($agents), [
			'.wd-data-salary.wd-label' => 0,
			'.wd-data-salary.wd-data' => 10,
		]);
	}

	/**
	 * @group ComDataLink
	 */
	function testLinkPropel() {
		//So, let's test the limit of the database interactivity...
		//For some customers, display all contracts where they were a buyer and show if possible, the
		//contract(s) associated in the "move" table where they were a seller.
//		$contracts = $this->all('SELECT * FROM contract LIMIT 10');
//		$contract = new Data('contract');
//		$contract->setDataClass('wd')->setLabelable(true);
//		$contract->link('location_id', new Data('location'));
//		$contract->addExtraColumn('buyer_contract')->addExtraColumn('seller_contract');
//		$move = new Group(new Data('move'));
//		$contract->link('buyer_contract', $move, true, 'buyer_contract_id');
//		$contract->link('seller_contract', $move, true, 'buyer_contract_id');
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
		$data = new Data('service');
		$columns = $data->getColumnNames();
		$this->assertEquals(['id', 'name', 'description', 'price'], $columns);
		$this->ret($data->reorder(['name']));
		$this->assertEquals(['name', 'id', 'description', 'price'], $data->getColumnNames());
		$data->reorder([0 => 'id', 3 => 'name']);
		$this->assertEquals(['id', 'description', 'price', 'name'], $data->getColumnNames());
		$data->reorder([1 => 'id', 3 => 'price']);
		$this->assertEquals(['description', 'id', 'name', 'price'], $data->getColumnNames());
		$data->reorder(['name', 'price']);
		$this->assertEquals(['name', 'price', 'description', 'id'], $data->getColumnNames());
		$this->setExpectedException('Exception');
		$data->reorder([0 => 'a']);
	}

	/**
	 * @group ComData
	 */
	function testPropelDateFormat() {
		$data = new Data('contract');
		$this->assertEquals(null, $data->getDateFormat());
		$this->assertEquals(null, $data->getDateTimeFormat());
		$this->assertEquals(null, $data->getTimeFormat());

		$contracts = \ContractQuery::create()->limit(5)
				->filterByCompletedTime(null, Criteria::NOT_EQUAL)
				->filterByCompletedDate(null, Criteria::NOT_EQUAL)
				->find();
		$group = new Group($data);
		$output = $group->render($contracts);

		$sqlData = new Data('contract');
		$sqlData->link('completed_time', new Custom(function($value) {
			return date(Data::$DefaultTimeFormat, strtotime($value));
		}, true, 'div'))->link('completed_date', new Custom(function($value) {
			return date(Data::$DefaultDateFormat, strtotime($value));
		}, true, 'div'))->link('created_at', new Custom(function($value) {
			return date(Data::$DefaultDateTimeFormat, strtotime($value));
		}, true, 'div'))->link('updated_at', new Custom(function($value) {
			return date(Data::$DefaultDateTimeFormat, strtotime($value));
		}, true, 'div'));
		$sqlContracts = $this->all('SELECT * FROM contract '
				. 'WHERE completed_time IS NOT NULL AND completed_date IS NOT NULL LIMIT 5');
		$sqlGroup = new Group($sqlData);
		$sqlOutput = $sqlGroup->render($sqlContracts);
		$this->assertEquals($output, $sqlOutput);

		$newDateTime = 'Y-m-d H:i:s';
		$newDate = 'Y-m-d';
		$newTime = 'H:i:s';
		$data->setDateTimeFormat($newDateTime)->setDateFormat($newDate)->setTimeFormat($newTime);
		$sqlData->unlink('created_at')->link('created_at', new Custom(function($value) use ($newDateTime) {
					return date($newDateTime, strtotime($value));
				}, true, 'div'))
				->unlink('updated_at')->link('updated_at', new Custom(function($value) use ($newDateTime) {
					return date($newDateTime, strtotime($value));
				}, true, 'div'))
				->unlink('completed_time')->link('completed_time', new Custom(function($value) use ($newTime) {
					return date($newTime, strtotime($value));
				}, true, 'div'))
				->unlink('completed_date')->link('completed_date', new Custom(function($value) use ($newDate) {
			return date($newDate, strtotime($value));
		}, true, 'div'));
		$output = $group->render($contracts);
		$sqlOutput = $sqlGroup->render($sqlContracts);
		$this->assertEquals($output, $sqlOutput);

		$this->assertEquals($newDate, $data->getDateFormat());
		$this->assertEquals($newDateTime, $data->getDateTimeFormat());
		$this->assertEquals($newTime, $data->getTimeFormat());
	}

	/**
	 * @group ComData
	 */
	function testPropelDefaultValues() {
		//Include default values.
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
