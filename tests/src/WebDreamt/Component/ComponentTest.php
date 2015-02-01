<?php

namespace WebDreamt;

use WebDreamt\Component\Custom;
use WebDreamt\Component\Wrapper;
require_once __DIR__ . '/../../../bootstrap.php';

class ComponentTest extends Test {

	protected $component;

	function setUp() {
		parent::setUp();
		$this->component = new Component();
		$this->set(Component::class);
	}

	/**
	 * @group Component
	 */
	function testConstruct() {
		$component = new Component("span", 'test', 'data-test=""', 'dog');
		$output = $component->render('cat');
		$this->checkCount($output, [
			'span' => 1,
			'.test' => 1,
			'[data-test=""]' => 1
		]);
		$this->checkHtml($output, 'span', 'dog');
	}

	/**
	 * @group Component
	 */
	function testHtml() {
		$this->assertEquals('', $this->component->getHtml());
		$this->assertEquals(null, is_callable($this->component->getHtmlCallback()));
		$this->assertEquals('div', $this->component->getHtmlTag());
		$this->ret($this->component->appendHtml('data-place=""'));
		$this->ret($this->component->setHtml('data-test=""'));
		$this->ret($this->component->appendHtml('data-stay=""'));
		$this->ret($this->component->setHtmlCallback(function($value) {
					return "data-input='$value'";
				}));
		$this->ret($this->component->setHtmlTag('li'));
		$this->assertEquals('data-test="" data-stay=""', $this->component->getHtml());
		$this->assertEquals(true, is_callable($this->component->getHtmlCallback()));
		$this->assertEquals('li', $this->component->getHtmlTag());
		$this->ret($this->getMethod($this->component, 'useHtml', 'data-dog=""'));
		$output = $this->component->render('monkey');
		$this->checkCount($output, [
			'li' => 1,
			'[data-input="monkey"]' => 1,
			'[data-place]' => 0,
			'[data-stay]' => 1,
			'[data-dog]' => 1
		]);
		$output = $this->component->render('monkey');
		$this->checkCount($output, [
			'li' => 1,
			'[data-input="monkey"]' => 1,
			'[data-place]' => 0,
			'[data-stay]' => 1,
			'[data-dog]' => 0
		]);
	}

	/**
	 * @group Component
	 */
	function testClass() {
		$this->assertEquals('', $this->component->getCssClass());
		$this->assertEquals(null, is_callable($this->component->getCssClassCallback()));
		$this->ret($this->component->appendCssClass('test-a'));
		$this->ret($this->component->setCssClass('test-class'));
		$this->ret($this->component->appendCssClass('test-b'));
		$this->ret($this->component->setCssClassCallback(function($value) {
					return "class-$value";
				}));
		$this->assertEquals('test-class test-b', $this->component->getCssClass());
		$this->assertEquals(true, is_callable($this->component->getCssClassCallback()));
		$this->ret($this->getMethod($this->component, 'useCssClass', 'class-dog'));
		$output = $this->component->render('monkey');
		$this->checkCount($output, [
			'.test-class' => 1,
			'.class-monkey' => 1,
			'.test-a' => 0,
			'.test-b' => 1,
			'.class-dog' => 1
		]);
		$output = $this->component->render('monkey');
		$this->checkCount($output, [
			'.test-class' => 1,
			'.class-monkey' => 1,
			'.test-a' => 0,
			'.test-b' => 1,
			'.class-dog' => 0
		]);
	}

	/**
	 * @group Component
	 */
	function testAfterOpening() {
		$this->assertEquals('', $this->component->getAfterOpeningTag());
		$this->ret($this->component->appendAfterOpeningTag('<div class="cat"></div>'));
		$this->ret($this->component->setAfterOpeningTag('<div class="dog"></div>'));
		$this->ret($this->component->appendAfterOpeningTag('<div class="hamster"></div>'));
		$this->assertEquals('<div class="dog"></div><div class="hamster"></div>', $this->component->getAfterOpeningTag());
		$this->ret($this->getMethod($this->component, 'useAfterOpeningTag', '<div class="bird"></div>'));
		$output = $this->component->render();
		$this->checkIndex($output, [
			'.dog' => 0,
			'.hamster' => 1,
			'.bird' => 2
		]);
		$output = $this->component->render();
		$this->checkCount($output, [
			'.dog' => 1,
			'.hamster' => 1,
			'.bird' => 0
		]);
	}

	/**
	 * @group Component
	 */
	function testBeforeClosing() {
		$this->assertEquals('', $this->component->getBeforeClosingTag());
		$this->ret($this->component->prependBeforeClosingTag('<div class="cat"></div>'));
		$this->ret($this->component->setBeforeClosingTag('<div class="dog"></div>'));
		$this->ret($this->component->prependBeforeClosingTag('<div class="hamster"></div>'));
		$this->assertEquals('<div class="hamster"></div><div class="dog"></div>', $this->component->getBeforeClosingTag());
		$this->ret($this->getMethod($this->component, 'useBeforeClosingTag', '<div class="bird"></div>'));
		$output = $this->component->render();
		$this->checkIndex($output, [
			'.dog' => 2,
			'.hamster' => 1,
			'.bird' => 0
		]);
		$output = $this->component->render();
		$this->checkCount($output, [
			'.dog' => 1,
			'.hamster' => 1,
			'.bird' => 0
		]);
	}

	/**
	 * @group Component
	 */
	function testTitle() {
		$this->assertEquals('', $this->component->getTitle());
		$this->ret($this->component->setTitle('Test'));
		$this->assertEquals('Test', $this->component->getTitle());
	}

	/**
	 * @group Component
	 */
	function testInput() {
		$this->assertEquals(null, $this->component->getInput());
		$this->ret($this->component->setInput('dog'));
		$this->assertEquals('dog', $this->component->getInput());
		$this->assertEquals('<div>dog</div>', $this->component->render('cat'));
	}

	/**
	 * @group Component
	 */
	function testExtraComponent() {
		$this->assertEquals(1, count($this->component->getComponents()));
		$this->ret($this->component->addExtraComponent(new Component('div', 'a', null, '')));
		$this->ret($this->component->addExtraComponent(new Component('div', 'b', null, '')));
		$this->ret($this->component->addExtraComponent(new Component('div', 'c', null, '')));
		$this->assertEquals(4, count($this->component->getComponents()));
		$this->assertEquals(0, $this->component->getInputIndex());
		$this->component->setInput('<div class="me"></div>');
		$this->checkIndex($this->component->render(), [
			'.me' => 0,
			'.a' => 1,
			'.b' => 2,
			'.c' => 3
		]);
		$this->ret($this->component->setInputIndex(3));
		$this->assertEquals(3, $this->component->getInputIndex());
		$this->checkIndex($this->component->render(), [
			'.a' => 0,
			'.b' => 1,
			'.c' => 2,
			'.me' => 3
		]);
		$this->ret($this->component->setInputIndex(1));
		$this->assertEquals(1, $this->component->getInputIndex());
		$this->checkIndex($this->component->render(), [
			'.a' => 0,
			'.me' => 1,
			'.b' => 2,
			'.c' => 3,
		]);
	}

	/**
	 * @group Component
	 */
	function testExtraComponentBefore() {
		$this->ret($this->component->addExtraComponent(new Component('div', 'a', null, ''), false));
		$this->ret($this->component->addExtraComponent(new Component('div', 'b', null, ''), false));
		$this->ret($this->component->addExtraComponent(new Component('div', 'c', null, ''), false));
		$this->component->setInput('<div class="me"></div>');
		$this->checkIndex($this->component->render(), [
			'.c' => 0,
			'.b' => 1,
			'.a' => 2,
			'.me' => 3
		]);
	}

	/**
	 * @group Component
	 */
	function testBeautify() {
		$this->assertEquals($this->getMethod($this->component, 'beautify', 'some_other_id'), 'Some Other ID');
	}

	/**
	 * @group Component
	 */
	function testRender() {
		$this->assertEquals($this->component->__toString(), $this->component->render());
	}

	/**
	 * @group Component
	 */
	function testBlank() {
		$this->component->setHtmlTag(null);
		$this->assertEquals('dog', $this->component->render('dog'));
	}

	/**
	 * @group Component
	 */
	function testKey() {
		$this->assertEquals(null, $this->component->getKey());
		$this->component->setKey('test');
		$this->assertEquals('test', $this->component->getKey());
		$output = $this->component->render(['test' => 'value']);
		$this->assertEquals('<div>value</div>', $output);
		$output = $this->component->render(['one' => 'value']);
		$this->assertEquals('<div></div>', $output);
	}

	/**
	 * @group Component
	 */
	function testNullInput() {
		$this->assertEquals(null, $this->component->getOnNullInput());
		$this->component->setOnNullInput('None');
		$output = $this->component->render();
		$this->checkHtml($output, [
			'div' => 'None'
		]);
		$this->component->setOnNullInput(null);
		$this->checkHtml($this->component->render(), [
			'div' => ''
		]);
	}

	/**
	 * @group Component
	 */
	function testSelfClosing() {
		$this->assertEquals(false, $this->component->getSelfClosing());
		$this->ret($this->component->setSelfClosing(true));
		$this->assertEquals(true, $this->component->getSelfClosing());
		$output = $this->component->render();
		$this->assertEquals('<div />', $output);
	}

	/**
	 * @group Component
	 */
	function testRenderedBy() {
		$this->assertEquals(null, $this->component->getRenderedBy());
		$wrapper = new Wrapper();
		$outer = new Wrapper($wrapper);
		$custom = new Custom(function($value, $included) use ($outer) {
			$this->assertEquals($outer, $included->getRenderedBy());
		}, true);
		$wrapper->setDisplayComponent($custom);
		$outer->render();
		$this->assertEquals(null, $wrapper->getRenderedBy());
		$this->assertEquals(null, $custom->getRenderedBy());
	}

}
