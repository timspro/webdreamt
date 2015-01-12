<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper\Group;
use WebDreamt\Test;
require_once __DIR__ . '/../../../bootstrap.php';

class GroupTest extends Test {

	function setUp() {
		parent::setUp();
		$this->set(Group::class);
	}

	/**
	 * @group ComGroup
	 */
	function testGroup() {
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
	 * @group ComGroup
	 */
	function testIndexClass() {
		$array = [];
		for ($i = 0; $i < 10; $i++) {
			$array[] = $i;
		}
		$group = new Group();
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

	/**
	 * @group ComGroup
	 */
	function testFirst() {
		$group = new Group();
		$this->assertEquals(null, $group->getFirstComponent());
		$this->assertEquals(false, $group->getUseFirst());
		$this->ret($group->setUseFirst(true));
		$this->assertEquals(true, $group->getUseFirst());
		$group->getFirstComponent()->setCssClass('test');

		$output = $group->render(['a', 'b', 'c']);
		echo $output;
		$this->checkCount($output, [
			'div' => 4
		]);
		$this->checkHtml($output, [
			'.test' => 'a'
		]);

		$this->ret($group->setFirstComponent(new Component('span')));
		$output = $group->render(['d', 'e', 'f']);
		$this->checkHtml($output, [
			'span' => 'd'
		]);
	}

}
