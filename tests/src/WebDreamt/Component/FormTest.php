<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper\Data\Form;
use WebDreamt\Component\Wrapper\Group\Select;
use WebDreamt\Component\Wrapper\Modal;
require_once __DIR__ . '/../../../bootstrap.php';

class FormTest extends Test {

	static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::setUpDatabase();
	}

	static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::tearDownDatabase();
	}

	function setUp() {
		$this->set(Form::class);
	}

	/**
	 * @group ComForm
	 */
	function testBasic() {
		$customerForm = new Form('customer');
		$customerForm->allow();
		$output = $customerForm->setDataClass('wd')->render();
		$this->checkExists($output, [
			'.wd-first_name > input[type="text"]',
			'.wd-last_name > input[type="text"]',
			'.wd-company_name > input[type="text"]',
			'.wd-active > select',
			'.wd-type > select',
			'.wd-created_at > input.wd-datetime-control',
			'.wd-first_name > input[name="1-first_name"]'
		]);
		$this->checkCount($output, [
			'option' => 5
		]);
		$this->checkHtml($output, [
			'.wd-active option:first-child' => 'No',
			'.wd-active option:last-child' => 'Yes',
			'.wd-type option:first-child' => 'Person',
			'.wd-type option:last-child' => 'Nonprofit'
		]);

		$serviceForm = new Form('service');
		$output = $serviceForm->setDataClass('wd')->render();
		$this->checkExists($output, [
			'.wd-price > input[type="number"]'
		]);

		$contractForm = new Form('contract');
		$output = $contractForm->setDataClass('wd')->render();
		$this->checkExists($output, [
			'.wd-completed_time > input.wd-time-control',
			'.wd-completed_date > input.wd-date-control'
		]);
	}

	/**
	 * @group ComForm
	 */
	function testHook() {
		$customerForm = new Form('customer');
		$this->assertEquals(null, $customerForm->getInputHook());
		$this->ret($customerForm->setInputHook(function($column, $options, &$name, &$value, &$possible) {
					$name = $column;
					$value = $column;
					$this->assertEquals(true, $options[Form::OPT_ACCESS]);
					if ($column === 'type') {
						$possible[] = 'Test';
					}
				}));
		$customerForm->setDataClass('wd')->allow();
		$output = $customerForm->render();
		//Want to make sure that rendering twice doesn't cause wierdness in internal variables modified
		//by input hook.
		$output = $customerForm->render();

		$this->checkExists($output, [
			'.wd-id > input[name="id"]',
			'.wd-id > input[value="id"]',
			'.wd-updated_at > input[name="updated_at"]',
			'.wd-updated_at > input[value="updated_at"]'
		]);
		$this->checkCount($output, [
			'option' => 6
		]);
		$this->checkHtml($output, [
			'.wd-type option:last-child' => 'Test'
		]);
	}

	/**
	 * @group ComForm
	 */
	function testOptions() {
		$customerForm = new Form('customer');
		$customerForm->deny()->allow('id', 'first_name', 'last_name', 'company_name', 'phone', 'active');
		$customerForm->hide('first_name', 'last_name')->show('last_name');
		$this->ret($customerForm->disable('last_name', 'id')->enable('last_name'));
		$this->ret($customerForm->required('company_name', 'phone')->optional('company_name'));
		$this->ret($customerForm->setHtmlClass(['first_name' => 'wd-textarea'])
						->setHtmlExtra(['company_name' => 'wd-data'])
						->setHtmlType(['first_name' => Form::HTML_TEXTAREA]));
		$customerForm->setDataClass('wd');
		$output = $customerForm->render();
		$this->checkExists($output, [
			'.wd-first_name[style]',
			'.wd-last_name > input[disabled]',
			'.wd-phone > input[required]',
			'.wd-company_name > input[wd-data]',
			'.wd-first_name > textarea.wd-textarea',
		]);
		$this->checkCount($output, [
			'.wd-created_at' => 0,
			'[style]' => 2,
			'[required]' => 1,
			'[disabled]' => 1,
			'.wd-textarea' => 1,
		]);
	}

	/**
	 * @group ComForm
	 */
	function testMultiple() {
		$customerForm = new Form('customer');
		$this->assertEquals(false, $customerForm->getMultiple());
		$this->ret($customerForm->setMultiple(true));
		$output = $customerForm->render();
		$this->checkExists($output, [
			'.wd-multiple'
		]);
	}

	/**
	 * @group ComForm
	 */
	function testModal() {
		$customerForm = new Form('customer');
		$modal = new Modal($customerForm);
		$output = $modal->render();
		$this->checkCount($output, [
			'form' => 1,
			'button' => 3
		]);
	}

	/**
	 * @group ComForm
	 */
	function testInput() {
		$agent = $this->all('SELECT * FROM agent')[0];
		$form = new Form('agent');
		$output = $form->setDataClass('wd')->render($agent);
		$this->checkExists($output, [
			'.wd-first_name > input[value="' . $agent['first_name'] . '"]',
			'.wd-last_name > input[value="' . $agent['last_name'] . '"]'
		]);
	}

	/**
	 * @group ComForm
	 */
	function testLinkPropel() {
		$contract = \ContractQuery::create()->find()[0];
		$agents = \AgentQuery::create()->find();
		$data = [];
		foreach ($agents as $agent) {
			$array = [];
			$array['id'] = $agent->getId();
			$array['name'] = $agent->getLastName() . ', ' . $agent->getFirstName();
			$data[] = $array;
		}

		$contractForm = new Form('contract');
		$contractForm->setDataClass('wd-form');
		$locationForm = new Form('location');
		$locationForm->setDataClass('wd');
		$contractForm->link('location_id', $locationForm);
		$select = new Select('name');
		$select->setInput($data);
		$contractForm->link('buyer_agent_id', $select);
		$output = $contractForm->render($contract);
		$this->output(__DIR__ . '/output/form.html', $output);
		$this->checkCount($output, [
			'.wd-form-buyer_agent_id option[value]' => count($agents),
			'.wd-city' => 1,
			'form' => 1
		]);
		$location = $contract->getLocation();
		$this->checkHtml($output, [
			'option:first-child' => $agents[0]->getLastName() . ', ' . $agents[0]->getFirstName(),
		]);
		$this->checkExists($output, [
			'.wd-form-id > input[value="' . $contract->getId() . '"]',
			'.wd-form-buyer_customer_id > input[value="' . $contract->getBuyerCustomerId() . '"]',
			'.wd-city > input[value="' . $location->getCity() . '"]',
			'.wd-state > input[value="' . $location->getState() . '"]'
		]);
	}

}
