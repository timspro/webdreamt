<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper\Group;
use WebDreamt\Component\Wrapper\Group\Select;
use WebDreamt\Test;
require_once __DIR__ . '/../../../bootstrap.php';

class GroupTest extends Test {

	/**
	 * @group ComGroup
	 */
	function testGroup() {
		$this->set(Group::class);
		$group = new Group(new Component(), 'span', 'test', 'data-test=""', ['a', 'b', 'c']);
		$this->assertEquals('', $group->getTitle());
		$component = new Component();
		$this->ret($group->setDisplayComponent($component->setTitle('apple')));
		$this->assertEquals('apples', $group->getTitle());
		$output = $group->render(['e', 'f']);
		$this->checkCount($output, [
			'span' => 1,
			'.test' => 1,
			'[data-test=""]' => 1,
			'div' => 3
		]);
		$group->setInput(null);
		$output = $group->render(['e', 'f']);
		$this->checkCount($output, [
			'span' => 1,
			'.test' => 1,
			'[data-test=""]' => 1,
			'div' => 2
		]);
	}

	/**
	 * @group ComSelect
	 */
	function testSelect() {
		$this->set(Select::class);
		$select = new Select(null, 'test', 'data-test=""', ['a', 'b', 'c']);
		$output = $select->render(['q']);
		$this->checkCount($output, [
			'.test' => 1,
			'[data-test=""]' => 1,
			'option' => 3,
			'selected' => 0
		]);
		$select->setSelected('c');
		$this->checkHtml($select->render(), [
			'[selected=""]' => 'c'
		]);
		$select->setInput(null)->getDisplayComponent()->setKey('value');
		$data = [
			['id' => '0', 'value' => 'cat'],
			['id' => '1', 'value' => 'dog'],
			['id' => '2', 'value' => 'lizard']
		];
		$output = $select->render($data);
		$this->checkIndex($output, [
			'[value=0]' => 0,
			'[value=1]' => 1,
			'[value=2]' => 2
		]);
		$this->checkHtml($output, [
			'[value=0]' => 'cat',
			'[value=1]' => 'dog',
			'[value=2]' => 'lizard'
		]);
		$this->ret($select->setSelected('2'));
		$output = $select->render($data);
		$this->checkHtml($output, [
			"[selected='']" => 'lizard'
		]);
	}

	/**
	 * @group ComGroup
	 */
	function testIndexClass() {
		$array = [];
		for ($i = 0; $i < 10; $i++) {
			$array[] = $i;
		}
		$group = new Group();
		$this->set(Group::class);
		$this->assertEquals(null, $group->getIndexClass());
		$this->ret($group->setIndexClass('test'));
		$this->assertEquals('test', $group->getIndexClass());
		$output = $group->render($array);
		$this->checkHtml($output, [
			'.test-0' => '0',
			'.test-3' => '3',
			'.test-6' => '6',
			'.test-9' => '9'
		]);
	}

}
