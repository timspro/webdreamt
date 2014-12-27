<?php

namespace WebDreamt;

use WebDreamt\Test\Test;
require_once __DIR__ . '/../../bootstrap.php';

class ComponentTest extends Test {

	protected $component;

	function setUp() {
		parent::setUp();
		$this->component = new Component();
	}

	function ret($test) {
		$this->assertTrue($test instanceof Component);
	}

	/**
	 * @group Component
	 */
	function testHtml() {
		$this->assertEquals('data-test=""', $this->component->getHtml());
		$this->assertEquals(null, is_callable($this->component->getHtmlCallback()));
		$this->assertEquals('div', $this->component->getHtmlTag());
		$this->ret($this->component->setHtml('data-test=""'));
		$this->ret($this->component->setHtmlCallback(function($value) {
					echo "data-input='$value'";
				}));
		$this->ret($this->component->setHtmlTag('li'));
		$this->assertEquals('data-test=""', $this->component->getHtml());
		$this->assertEquals(true, is_callable($this->component->getHtmlCallback()));
		$this->assertEquals('li', $this->component->getHtmlTag());
		$output = $this->component->render('monkey');
		$this->assertEquals(1, $this->countElements('[data-test]', $output));
		$this->assertEquals(1, $this->countElements('[data-input="monkey"]', $output));
	}

	/**
	 * @group Component
	 */
	function testClass() {
		$this->assertEquals('', $this->component->getCssClass());
		$this->assertEquals(null, is_callable($this->component->getCssClassCallback()));
		$this->ret($this->component->setCssClass('test-class'));
		$this->ret($this->component->setCssClassCallback(function($value) {
					echo "$value";
				}));
		$this->assertEquals(true, is_callable($this->component->getCssClassCallback()));
		$output = $this->component->render('monkey');
		echo $output;
		$this->assertEquals(1, $this->countElements('.test-class', $output));
		$this->assertEquals(1, $this->countElements('.class-monkey', $output));
	}

}
