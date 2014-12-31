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

	/**
	 * @group ComData
	 */
	function testData() {
		$customers = $this->all('SELECT * FROM customer LIMIT 10');
		$data = new Data('customer', new Component('td'), 'tr', 'test', 'data-test=""');
		$group = new Group($data, 'table');
		$output = $group->render($customers);
		$this->checkCount($output, [
			'tr' => 10,
			'td' => 10 * count($customers[0])
		]);
	}

}
