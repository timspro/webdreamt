<?php

namespace WebDreamt;

use WebDreamt\Component\Wrapper;
use WebDreamt\Component\Wrapper\Modal;
use WebDreamt\Component\Wrapper\Panel;
use WebDreamt\Test;
require_once __DIR__ . '/../../../bootstrap.php';

class WrapperTest extends Test {

	/**
	 * @group ComWrapper
	 */
	function testConstruct() {
		$wrapper = new Wrapper(new Component('li'), 'ul', 'test', 'data-test=""', 'dog');
		$output = $wrapper->render('bird');
		$this->checkCount($output, [
			'ul' => 1,
			'li' => 1,
			'.test' => 1,
			'[data-test=""]' => 1
		]);
		$this->checkHtml($output, 'li', 'dog');

		$modal = new Modal(new Component('span', 'taste'), 'mod', 'data-mod=""', 'cat');
		$output = $modal->render('bird');
		$this->checkCount($output, [
			'.mod' => 1,
			'[data-mod=""]' => 1
		]);
		$this->checkHtml($output, '.taste', 'cat');

		$panel = new Panel(new Component('span', 'taste'), 'pan', 'data-pan=""', 'lizard');
		$output = $panel->render('bird');
		$this->checkCount($output, [
			'.pan' => 1,
			'[data-pan=""]' => 1
		]);
		$this->checkHtml($output, '.taste', 'lizard');
	}

	/**
	 * @group ComWrapper
	 */
	function testWrapper() {
		$this->set(Wrapper::class);
		$component = new Component();
		$component->setTitle('DIV');
		$wrapper = new Wrapper($component);
		$this->assertEquals('DIV', $wrapper->getTitle());
		$this->assertSame($component, $wrapper->getDisplayComponent());
		$span = new Component('span');
		$span->setTitle('SPAN');
		$this->ret($wrapper->setDisplayComponent($span));
		$this->assertEquals('SPAN', $wrapper->getTitle());
		$output = $wrapper->render('dog');
		$this->assertEquals('<div><span>dog</span></div>', $output);
	}

	/**
	 * @group ComWrapper
	 */
	function testModal() {
		$this->set(Modal::class);
		$component = new Component();
		$component->setTitle('Bird');
		$modal = new Modal($component);
		$this->ret($modal->addButtons(['dog' => 'dog', 'cat' => 'cat']));
		$this->ret($this->getMethod($modal, 'useButtons', [['test' => 'test']]));
		$output = $modal->render('lizard');
		$this->checkHtml($output, [
			'.dog' => 'dog',
			'.cat' => 'cat',
			'.test' => 'test',
			'.modal-title' => 'Bird',
			'.modal-body' => '<div>lizard</div>'
		]);
		$this->checkIndex($output, [
			'.dog' => 1,
			'.cat' => 2,
			'.test' => 3
		]);
		$output = $modal->render('lizard');
		$this->checkCount($output, [
			'.dog' => 1,
			'.cat' => 1,
			'.test' => 0
		]);
	}

	/**
	 * @group ComWrapper
	 */
	function testPanel() {
		$component = new Component();
		$component->setTitle("Test");
		$panel = new Panel($component);
		$output = $panel->render('dog');
		$this->checkHtml($output, [
			'.panel-body' => '<div>dog</div>',
			'.panel-title' => 'Test'
		]);
	}

}
