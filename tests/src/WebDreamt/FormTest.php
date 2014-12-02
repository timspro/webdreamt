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
		//Get information about drivers.
		$driverQuery = "SELECT id, CONCAT(last_name, ', ', first_name) as last_name FROM driver "
				. "ORDER BY last_name";
		$stmt = self::$a->db()->query($driverQuery);
		$driverData = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//Get information about locations.
		$stmt = self::$a->db()->query("SELECT street_address FROM location");
		$locationData = $stmt->fetchAll(PDO::FETCH_COLUMN);

		//Set up a new job form.
		$form = new Form('job');

		//Set up a component to pick from the current drivers.
		$driverSelect = new Select('driver');
		$driverSelect->setDisplay('last_name')->setInput($driverData);
		$form->link('driver_id', $driverSelect);
		//Also link to an add form for the driver.
		$form->link('driver_id', new Form('driver'));

		//Link to a select to pick the location.
		$locationSelect = new Select('location');
		$locationSelect->setInput($locationData);
		$form->link('location_id', $locationSelect);

		//Link to a form to add the customer information.
		$form->link('customer_id', new Form('customer'));

		//Output
		$output = $form->render();
		$this->output('job-form-linked.html', $output);
	}

	/**
	 * @group Form
	 */
	public function testExtraForm() {
		//Get information about available services.
		$data = self::$a->db()->query("SELECT id, name FROM service");
		//Set up the job form.
		$jobForm = new Form('job');
		//Set up the service job form.
		$serviceJobForm = new Form('service_job');
		$serviceJobForm->setMultiple(true)
				->link('service_id', (new Select('service'))->setDisplay('name')->setInput($data))
				->link('service_id', new Form('service'))->deny('job_id');
		$jobForm->addExtraComponent($serviceJobForm);
		//Output
		$output = $jobForm->render();
		$this->output('job-form-extra.html', $output);
	}

}
