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
	 * @group ComData
	 */
	function testLinkPropel() {
		//So, let's test the limit of the database interactivity...
		//For some customers, display all contracts where they were a buyer and show if possible, the
		//contract(s) associated in the "move" table where they were a seller.
		//First, setup common components.
		$agent = new Data('agent');
		$agent->setDataClass('agent')->setLabelable(true);
		$location = new Data('location');
		$location->setDataClass('location')->setLabelable(true);
		$sellerCustomer = new Data('customer');
		$sellerCustomer->setDataClass('customer')->setLabelable(true);
		$buyerCustomer = $sellerCustomer;
		$move = new Data('move');
		$move->hide('id', 'buyer_contract_id');
		$services = new Data('service_contract');
		$services->hide('contract_id');
		$services->link('service_id', new Data('service'));

		//Let's create the topmost customer component.
		$topCustomer = new Data('customer');
		$topCustomer->setDataClass('top-customer')->setLabelable(true);
		//Link in the buyer contracts. Note that we will finish setting this up when make the contract
		//component.
		$topCustomer->addExtraColumn('bought_contracts');

		//Now, create the bought contract component.
		$boughtContract = new Data('contract');
		$boughtContract->setDataClass('bought-contract')->setLabelable(true);
		$boughtContract->link('location_id', $location)
				->link('seller_customer_id', $sellerCustomer)
				->link('buyer_agent_id', $agent)
				->link('seller_agent_id', $agent)
				->hide('buyer_customer_id');
		$boughtContract->addExtraColumn('services')->link('services', new Group($services), 'contract_id');
		$boughtContract->addExtraColumn('sold_contracts');
		//Link in move component.
		$boughtContract->link('sold_contracts', new Group($move), 'seller_contract_id');
		$topCustomer->link('bought_contracts', new Group($boughtContract), 'buyer_customer_id');

		//Test that things are linked up correctly.
		$option = $boughtContract->getOption('services', Data::OPT_PROPEL_COLUMN);
		$this->assertEquals('service_contract.contract_id', $option);
		$option = $topCustomer->getOption('bought_contracts', Data::OPT_PROPEL_COLUMN);
		$this->assertEquals('contract.buyer_customer_id', $option);
		$option = $boughtContract->getOption('sold_contracts', Data::OPT_PROPEL_COLUMN);
		$this->assertEquals('move.seller_contract_id', $option);

		//Create the sold contract component.
		$soldContract = new Data('contract');
		$soldContract->setDataClass('sold-contract')->setLabelable(true);
		$soldContract->link('location_id', $location)
				->link('buyer_customer_id', $buyerCustomer)
				->link('buyer_agent_id', $agent)
				->link('seller_agent_id', $agent)
				->hide('seller_customer_id');
		$soldContract->addExtraColumn('services')->link('services', new Group($services), 'contract_id');
		//Link in sold contracts.
		$move->link('seller_contract_id', $soldContract);

		$option = $soldContract->getOption('services', Data::OPT_PROPEL_COLUMN);
		$this->assertEquals('service_contract.contract_id', $option);
		$option = $soldContract->getOption('location_id', Data::OPT_PROPEL_COLUMN);
		$this->assertEquals('contract.location_id', $option);
		$option = $soldContract->getOption('buyer_customer_id', Data::OPT_PROPEL_COLUMN);
		$this->assertEquals('contract.buyer_customer_id', $option);
		$option = $move->getOption('seller_contract_id', Data::OPT_PROPEL_COLUMN);
		$this->assertEquals('move.seller_contract_id', $option);
		//Get the data.
		$data = \CustomerQuery::create()->find();
		//Render
		$customers = new Group($topCustomer);
		$output = $customers->render($data);
		//Check the output.
		$this->output(__DIR__ . '/output/complex.html', $output);
		$this->checkExists($output, [
			'.sold-contract-id',
			'.top-customer-id',
			'.customer-id',
			'.location-id',
			'.bought-contract-id'
		]);
	}

	/**
	 * @group ComData
	 */
	function testAlias() {
		$data = new Data('service');
		$data->link('price', new Component());
		$data->alias(['description' => 'extra', 'price' => 'test']);
		$this->assertContains('extra', $data->getColumnNames());
		$this->assertContains('test', $data->getColumnNames());
		$this->assertEquals(true, isset($data->getLinkedComponents()['test']));
		$data->hide('id', 'test');
		$data->alias(['id' => 'name', 'name' => 'id', 'test' => 'test2']);
		$this->assertEquals(true, isset($data->getLinkedComponents()['test2']));
		$this->assertEquals(false, $data->getOption('name', Data::OPT_VISIBLE));
		$this->assertEquals(false, $data->getOption('test2', Data::OPT_VISIBLE));
		$this->assertEquals(true, $data->getOption('id', Data::OPT_VISIBLE));
		$values = [false, true, true, false];
		$this->assertEquals($values, array_values($data->getOptions(null, Data::OPT_VISIBLE)));
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
		$service = \ServiceQuery::create()->findPK(1);
		$data = new Data('service');
		$data->setDefaultValues($service)->setDataClass('wd');
		$output = $data->render();
		$this->checkHtml($output, [
			'.wd-id' => $service->getId(),
			'.wd-name' => $service->getName(),
			'.wd-description' => $service->getDescription(),
			'.wd-price' => $service->getPrice()
		]);
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
