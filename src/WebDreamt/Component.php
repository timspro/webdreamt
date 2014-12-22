<?php

namespace WebDreamt;

class Component {

	/**
	 * The method to use to get input for the extra component.
	 */
	const EXTRA_METHOD = 'method';
	/**
	 * The extra component.
	 */
	const EXTRA_COMPONENT = 'component';

	/**
	 * A string to go after the opening tag.
	 * @var string
	 */
	protected $afterOpening;
	/**
	 * A string to go before the closing tag.
	 * @var string
	 */
	protected $beforeClosing;
	/**
	 * The title of the component. Effect depends on child component.
	 * @var string
	 */
	protected $title;
	/**
	 * HTML attributes for the top-level element.
	 * @var string
	 */
	protected $html;
	/**
	 * The HTML tag of the top-level element.
	 * @var string
	 */
	protected $htmlTag = 'div';
	/**
	 * CSS classes for the top-level element.
	 * @var string
	 */
	protected $class;
	/**
	 * An array of components to render. Note that null means the child component.
	 * @var array
	 */
	protected $components = [null];
	/**
	 * The input to be passed to the render method. If set, then overrides what is passed via the render()
	 * method.
	 * @var array
	 */
	protected $input;

	/**
	 * Set the title of the component. The effect of this depends on the child component.
	 * @param string $title
	 */
	function setTitle($title = '') {
		$this->title = $title;
	}

	/**
	 * Get the title of the component.
	 * @param string $title
	 */
	function getTitle() {
		return $this->title;
	}

	/**
	 * Set a string to go after the opening tag.
	 * @param string $after
	 * @return self
	 */
	function setAfterOpeningTag($after = '') {
		$this->afterOpening = $after;
		return $this;
	}

	/**
	 * Get the string set to go after opening tag.
	 * @return string
	 */
	function getAfterOpeningTag() {
		return $this->afterOpening;
	}

	/**
	 * Set a string to go before the closing tag.
	 * @param string $before
	 * @return self
	 */
	function setBeforeClosingTag($before = '') {
		$this->beforeClosing = $before;
		return $this;
	}

	/**
	 * Get the stirng set to go before closing tag.
	 * @return string
	 */
	function getBeforeClosingTag() {
		return $this->beforeClosing;
	}

	/**
	 * Set the CSS class(es) of the top-level element. This will overwrite any CSS classes the child
	 * component sets and so appendCssClass() should be preferred.
	 * @param string $className
	 * @return self
	 */
	function setCssClass($className = '') {
		$this->class = $className;
		return $this;
	}

	/**
	 * Get the CSS class of the top-level element.
	 * @return self
	 */
	function getCssClass() {
		return $this->class;
	}

	/**
	 * Append the CSS class(es) to the top-level element. If you append multiple classes, just separate
	 * them with a space.
	 * @param string
	 * @return self
	 */
	function appendCssClass($className = '') {
		$this->class .= " $className";
		return $this;
	}

	/**
	 * Set the HTML tag for the topmost element. Can be null, in which case no tag is displayed.
	 * @param string $htmlTag
	 * @return self
	 */
	function setHtmlTag($htmlTag = 'div') {
		$this->htmlTag = $htmlTag;
		return $this;
	}

	/**
	 * Get the HTML tag for the topmost element.
	 * @return string
	 */
	function getHtmlTag() {
		return $this->htmlTag;
	}

	/**
	 * Set the HTML of the top-level element. Note use setCssClass or appendCssClass to add classes.
	 * This will overwrite any HTML the child component sets and so appendHtml() should be preferred.
	 * @param string $html
	 * @return self
	 */
	function setHtml($html = '') {
		$this->html = $html;
		return $this;
	}

	/**
	 * Get the HTML of the top-level element.
	 * @return string
	 */
	function getHtml() {
		return $this->html;
	}

	/**
	 * Append on to the HTML of the top-level element. Use setCssClass or appendCssClass to
	 * add classes.
	 * @param string $html
	 * @return self
	 */
	function appendHtml($html = '') {
		$this->html .= $html;
		return $this;
	}

	/**
	 * Set the input of the component. Note that input set this way will override input passed to the
	 * render method.
	 * @param array|ActiveRecordInterface $input
	 * @return self
	 */
	function setInput($input = null) {
		$this->input = $input;
		return $this;
	}

	/**
	 * Get the input of the component.
	 * @return array
	 */
	function getInput() {
		return $this->input;
	}

	/**
	 * Set the child component index and thus the order that the child component appears along with
	 * extra components.
	 * @param int $newIndex
	 */
	function setChildComponentIndex($newIndex = 0) {
		$array = [];
		foreach ($this->components as $index => $component) {
			if ($index === $newIndex) {
				$array[] = null;
				continue;
			}
			if ($component) {
				$array[] = $component;
			}
		}
		$this->components = $array;
	}

	/**
	 * Get the child component index.
	 * @return int
	 */
	function getChildComponentIndex() {
		foreach ($this->components as $index => $component) {
			if (!$component) {
				return $index;
			}
		}
	}

	/**
	 * Add an extra component.
	 * @param Component $component
	 */
	function addExtraComponent(Component $component) {
		$this->components[] = $component;
	}

	/**
	 * Syntactic sugar for the render(...) method.
	 * @return string
	 */
	function __toString() {
		return $this->render();
	}

	/**
	 * Renders the component.
	 * @param mixed $input Any input for the component. The effect of the input depends on the child
	 * class of the component. By default, it is simply echoed.
	 * @param string $included The class that included the component. Null if no class.
	 */
	function render($input = null, $included = null) {
		if ($this->input) {
			$input = $this->input;
		}
		$htmlTag = $this->htmlTag;
		if ($htmlTag !== null) {
			echo "<$htmlTag " . $this->html . " class='" . $this->class . "'>";
		}
		echo $this->afterOpening;
		$this->renderComponents($input, $included);
		echo $this->beforeClosing;
		if ($htmlTag !== null) {
			echo "</$htmlTag>";
		}
	}

	/**
	 * Render the components.
	 * @param array|ActiveRecordInterface $input
	 * @param string $included
	 */
	protected function renderComponents($input = null, $included = null) {
		foreach ($this->components as $component) {
			if (!$component) {
				$this->renderMe($input, $included);
			} else {
				$component->render($input, static::class);
			}
		}
		return null;
	}

	/**
	 * Renders the child class of this component.
	 * @param array $input
	 * @param string $included
	 */
	protected function renderMe($input = null, $included = null) {
		echo $input;
	}

	/**
	 * A robust function for getting a value from a generic input.
	 * @param string $key
	 * @param array|\WebDreamt\ActiveRecordInterface $input
	 * @return type
	 */
	protected function getValueFromInput($key, $input) {
		if (is_array($input) && isset($input[$key])) {
			return $input[$key];
		} else if ($input instanceof ActiveRecordInterface) {
			return $input->getByName($key, TableMap::TYPE_FIELDNAME);
		}
		return null;
	}

}
