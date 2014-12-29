<?php

namespace WebDreamt;

use WebDreamt\Component\Custom;
use WebDreamt\Test;
require_once __DIR__ . '/../../../bootstrap.php';

class CustomTest extends Test {

	/**
	 * @group ComCustom
	 */
	function testCustom() {
		$component = new Custom(function ($input) {
			return $input['key'];
		}, null, 'div', 'test', 'data-test=""', ['key' => 'value']);
		$output = $component->render(['key' => 'monkey']);
		$this->checkCount($output, [
			'div' => 1,
			'.test' => 1,
			'[data-test=""]' => 1
		]);
		$this->checkHtml($output, [
			'div' => 'value'
		]);
		$component->setInput(null);
		$output = $component->render();
		$this->checkCount($output, [
			'div' => 1,
			'.test' => 1,
			'[data-test=""]' => 1
		]);
		$this->checkHtml($output, [
			'div' => ''
		]);
	}

	/**
	 * @group ComCustom
	 */
	function testAlways() {
		$component = new Custom(function ($input) {
			if (!isset($input['key'])) {
				return 'None';
			} else {
				return $input['key'];
			}
		}, true, 'div');
		$output = $component->render(['key' => 'value']);
		$this->assertEquals('<div>value</div>', $output);
		$this->assertEquals('<div>None</div>', $component->render());
	}

}
