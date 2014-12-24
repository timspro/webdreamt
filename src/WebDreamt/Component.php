<?php

namespace WebDreamt;

class Component {

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
	protected $htmlTag;
	/**
	 * CSS classes for the top-level element.
	 * @var string
	 */
	protected $class;
	/**
	 * An array of components to render. Note that null means the child component.
	 * @var array
	 */
	protected $components;
	/**
	 * The input to be passed to the render method. If set, then overrides what is passed via the render()
	 * method.
	 * @var array
	 */
	protected $input;
	/**
	 * A callback to add input-dependent classes.
	 * @var callable
	 */
	protected $cssCallback;
	/**
	 * A callback to add input-dependent HTML.
	 * @var callable
	 */
	protected $htmlCallback;
	/**
	 * A string used after the opening tag.
	 * @var string
	 */
	protected $withAfterOpening;
	/**
	 * A string used before the closing tag.
	 * @var string
	 */
	protected $withBeforeClosing;
	/**
	 * A string used for the HTML of the element.
	 * @var string
	 */
	protected $withHtml;
	/**
	 * A string used for the class of the element.
	 * @var string
	 */
	protected $withCssClass;

	/**
	 * Get a new component.
	 * @param string $htmlTag
	 */
	function __construct($htmlTag = 'div', $class = null, $html = null) {
		$this->htmlTag = $htmlTag;
		$this->class = $class;
		$this->html = $html;
		$this->components = [null];
	}

	/**
	 * Set the title of the component. The effect of this depends on the child component.
	 * @param string $title
	 * @return self
	 */
	function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Get the title of the component.
	 * @return string $title
	 */
	function getTitle() {
		return $this->title;
	}

	/**
	 * Set a string to go after the opening tag.
	 * @param string $after
	 * @return self
	 */
	function setAfterOpeningTag($after) {
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
	 * Set a string to go after the opening tag and will be reset by render().
	 * @param string $after
	 * @return self
	 */
	protected function useAfterOpeningTag($after) {
		$this->withAfterOpening .= " $after";
		return $this;
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
	 * Get the string set to go before closing tag.
	 * @return string
	 */
	function getBeforeClosingTag() {
		return $this->beforeClosing;
	}

	/**
	 * Set a string to go before the closing tag and will be reset by render().
	 * @param string $before
	 * @return self
	 */
	protected function useBeforeClosingTag($before) {
		$this->withBeforeClosing .= " $before";
		return $this;
	}

	/**
	 * Set the CSS class(es) of the top-level element. This will overwrite any CSS classes the child
	 * component sets and so appendCssClass() should be preferred.
	 * @param string $className
	 * @return self
	 */
	function setCssClass($className) {
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
	function appendCssClass($className) {
		$this->class .= " $className";
		return $this;
	}

	/**
	 * Set the CSS class(es) to be used with the top-level element. These will be reset by render().
	 * @param string $className
	 */
	protected function useCssClass($className) {
		$this->withCssClass .= " $className";
		return $this;
	}

	/**
	 * Set a callback that can generate input-dependent classes.
	 * @param callable $callable This is called with the input and object that are passed to the
	 * render function.
	 * @return self
	 */
	function setCssClassCallback($callable) {
		$this->cssCallback = $callable;
		return $this;
	}

	/**
	 * Get the class callback.
	 * @return string
	 */
	function getCssClassCallback() {
		return $this->cssCallback;
	}

	/**
	 * Set the HTML tag for the topmost element. Can be null, in which case no tag is displayed.
	 * @param string $htmlTag
	 * @return self
	 */
	function setHtmlTag($htmlTag) {
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
	function setHtml($html) {
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
	function appendHtml($html) {
		$this->html .= $html;
		return $this;
	}

	/**
	 * Set HTML to use with the top-level element. This will be reset by render().
	 * @param string $html
	 * @return self
	 */
	protected function useHtml($html) {
		$this->withHtml .= " $html";
		return $this;
	}

	/**
	 * Add a callback function to generate HTML attributes based on input.
	 * @param callable $callable This takes as a parameter the current input and class object that
	 * called the render() method and should return the HTML attributes.
	 * @return self
	 */
	function setHtmlCallback($callable) {
		$this->htmlCallback = $callable;
		return $this;
	}

	/**
	 * Get the HTML callback if set.
	 * @return callable
	 */
	function getHtmlCallback() {
		return $this->htmlCallback;
	}

	/**
	 * Set the input of the component. Note that input set this way will override input passed to the
	 * render method.
	 * @param array|ActiveRecordInterface $input
	 * @return self
	 */
	function setInput($input) {
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
	 * @return self
	 */
	function setChildComponentIndex($newIndex) {
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
		return $this;
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
	 * @return self
	 */
	function addExtraComponent(Component $component) {
		$this->components[] = $component;
		return $this;
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
	 * @param Component $included The component that is calling render(). Should be null if render()
	 * is not called by a component.
	 */
	function render($input = null, Component $included = null) {
		if ($this->input) {
			$input = $this->input;
		}
		$htmlTag = $this->htmlTag;
		if ($htmlTag !== null) {
			//Get HTML
			$htmlCallback = $this->htmlCallback;
			$htmlCallback = $htmlCallback ? $htmlCallback($input, $included) . ' ' : '';
			//Get CSS classes
			$cssCallback = $this->cssCallback;
			$cssCallback = $cssCallback ? $cssCallback($input, $included) . ' ' : '';
			$classes = $cssCallback . $this->class . $this->withCssClass;
			$classes = $classes ? "classes='$classes'" : '';
			echo "<$htmlTag $htmlCallback" . $this->html . $this->withHtml . " class='$classes'>";
		}
		echo $this->afterOpening . $this->withAfterOpening;
		$this->renderComponents($input, $included);
		echo $this->beforeClosing . $this->withBeforeClosing;
		if ($htmlTag !== null) {
			echo "</$htmlTag>";
		}
		$this->withHtml = null;
		$this->withCssClass = null;
		$this->withAfterOpening = null;
		$this->withBeforeClosing = null;
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
	 * Change underscores into spaces in a column or table name and capitalize the result.
	 * Also, this will change ' Id' to ' ID' if the string is the last part of the resulting name.
	 * @param string $name
	 * @return string
	 */
	static protected function beautify($name) {
		$return = ucwords(str_replace('_', ' ', $name));
		if (substr($return, -3) === ' Id') {
			$return = substr($return, 0, strlen($return) - 2) . 'ID';
		}
		return $return;
	}

}
