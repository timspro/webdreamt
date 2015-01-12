<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper\Select;
require_once __DIR__ . '/../../../bootstrap.php';

class SelectTest extends Test {

	/**
	 * @group ComSelect
	 */
	function testSelect() {
		$this->set(Select::class);
		$options = ['a', 'b', 'c'];
		$select = new Select($options, 'test', 'data-test=""');
		$this->assertEquals($options, $select->getOptions());
		$output = $select->render(5);
		$this->checkCount($output, [
			'.test' => 1,
			'[data-test=""]' => 1,
			'option' => 3,
			'selected' => 0
		]);
		$select->setInput(2);
		$this->checkHtml($select->render(), [
			'[selected=""]' => 'c'
		]);
		$this->assertEquals(false, $select->getNoValues());
		$this->ret($select->setNoValues(true));
		$this->assertEquals(true, $select->getNoValues());
		$select->setInput('b');
		$this->checkHtml($select->render(), [
			'[selected=""]' => 'b'
		]);
		$select->setOptions([
			'a' => 'cat',
			'b' => 'dog',
			'c' => 'lizard'
		]);
		$select->setNoValues(false);
		$output = $select->render();
		$this->checkIndex($output, [
			'[value="a"]' => 0,
			'[value="b"]' => 1,
			'[value="c"]' => 2
		]);
		$this->checkHtml($output, [
			'[value="a"]' => 'cat',
			'[value="b"]' => 'dog',
			'[value="c"]' => 'lizard',
			'[selected]' => 'dog'
		]);
	}

}
