<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper\Group;
use WebDreamt\Component\Wrapper\Group\Table;
require_once __DIR__ . '/../../../bootstrap.php';

class TableTest extends Test {

	static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::setUpDatabase();
	}

	static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::tearDownDatabase();
	}

	function setUp() {
		$this->set(Table::class);
	}

	/**
	 * @group ComTable
	 */
	function testBasic() {
		$table = new Table();
		$table->getRowComponent()->setCssClass('wd-test');
		$this->ret($table->setRowComponent(new Group(null, "tr", 'wd-row')));
		$table->getCellComponent()->setCssClass('wd-other');
		$this->ret($table->setCellComponent(new Component("td", 'wd-cell')));

		$multipleTable = [];
		for ($i = 1; $i <= 12; $i++) {
			$multipleTable[$i] = [];
			for ($j = 1; $j <= 12; $j++) {
				$multipleTable[$i][$j] = $i * $j;
			}
		}
		$output = $table->render($multipleTable);
		$this->checkCount($output, [
			'.wd-cell' => 144,
			'.wd-row' => 12,
			'th' => 0,
			'thead' => 0,
			'tr' => 12,
			'tbody' => 1,
			'td' => 144,
			'table' => 1
		]);
		$this->checkHtml($output, [
			'.wd-row:last-child .wd-cell:first-child' => 12,
			'.wd-row:first-child .wd-cell:last-child' => 12,
			'.wd-row:last-child .wd-cell:last-child' => 144,
			'.wd-row:first-child .wd-cell:first-child' => 1,
		]);

		$this->assertEquals(false, $table->getHeaderable());
		$this->ret($table->setHeaderable(true));
		$output = $table->render($multipleTable);
		$this->checkHtml($output, [
			'th:last-child' => 12,
			'th:first-child' => 1,
		]);
		$this->checkCount($output, [
			'th' => 12,
			'thead' => 1
		]);
	}

	/**
	 * @group ComTable
	 */
	function testPropel() {
		$customers = \CustomerQuery::create()->find();
		$table = new Table('customer');
		$table->getRowComponent()->setDataClass('wd')->setCssClass('wd-row');
		$table->getCellComponent()->setCssClass('wd-cell');
		$output = $table->render($customers);
		$numRows = count($customers);
		$numColumns = 9;
		$firstCustomer = $customers[0];
		$lastCustomer = $customers[$numRows - 1];
		$this->checkCount($output, [
			'.wd-cell' => $numRows * $numColumns,
			'.wd-row' => $numRows,
			'th' => 0,
			'thead' => 0,
			'tr' => $numRows,
			'tbody' => 1,
			'td' => $numRows * $numColumns,
			'table' => 1,
		]);
		$this->checkHtml($output, [
			'.wd-row:last-child .wd-cell:first-child' => $lastCustomer->getId(),
			'.wd-row:first-child .wd-cell:first-child' => $firstCustomer->getId(),
			'.wd-row:first-child .wd-company_name' => $firstCustomer->getCompanyName(),
			'.wd-row:last-child .wd-last_name' => $lastCustomer->getLastName()
		]);
		$table->setHeaderable(true);
		$output = $table->render($customers);
		$this->checkHtml($output, [
			'th:last-child' => 'Updated At',
			'th:first-child' => 'ID',
			'.wd-header-id' => 'ID',
			'.wd-header-company_name' => 'Company Name'
		]);
	}

	/**
	 * @group ComTable
	 */
	function testSetHeaders() {
		$data = $this->all('SELECT * FROM customer');
		$table = new Table();
		$this->assertEquals(null, $table->getHeaders());
		$headers = array_keys(array_keys($data[0]));
		$this->ret($table->setHeaders($headers));
		$this->assertEquals($headers, $table->getHeaders());
		$output = $table->render($data);
		$this->checkHtml($output, [
			'th:first-child' => 0,
			'th:last-child' => 8
		]);
	}

}
