<?php

namespace WebDreamt;

use DOMDocument;
use PDO;
use WebDreamt\Hyper\Form;
use WebDreamt\Hyper\Select;
require_once __DIR__ . '/../../bootstrap.php';

class FormTest extends Test {

	protected static $build;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$sql = file_get_contents(__DIR__ . '/test.sql');
		self::$a->db()->exec($sql);
		self::$build = self::$a->builder();
		self::$build->updatePropel();
		self::$build->loadAllClasses();
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
				], true);
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::$build->deleteDatabase();
		self::$build->removeDirectory(self::$build->DB);
		//self::$build->removeDirectory(__DIR__ . '/output/');
	}

	public function output($filename, $output) {
		file_put_contents(__DIR__ . '/output/' . $filename, $output);
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($output);
		file_put_contents(__DIR__ . '/output/' . $filename, $doc->saveXML($doc->documentElement));
	}

	/**
	 * @group Form
	 */
	public function testForm() {
		$form = new Form('job');
		$output = $form->render();
		$this->output('job-form.html', $output);
	}

	/**
	 * @group Form
	 */
	public function testLinkedForm() {
		$form = new Form('job');
		$driverSelect = new Select('driver');
		$locationSelect = new Select('location');

		$stmt = self::$a->db()->prepare("SELECT id, CONCAT(last_name, ', ', first_name) as last_name FROM driver");
		$stmt->execute();
		$driverSelect->setDisplay('last_name')->setInput($stmt->fetchAll(PDO::FETCH_ASSOC));
		$form->link('driver_id', $driverSelect);
		$form->link('driver_id', new Form('driver'));

		$stmt = self::$a->db()->prepare("SELECT street_address FROM location");
		$stmt->execute();
		$locationSelect->setInput($stmt->fetchAll(PDO::FETCH_COLUMN));
		$form->link('location_id', $locationSelect);

		$form->link('customer_id', new Form('customer'));

		$output = $form->render();
		$this->output('job-form-linked.html', $output);
	}

}
