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
	 * A key used to get input.
	 * @var string
	 */
	protected $key;

	/**
	 * Get a new component.
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct($htmlTag = 'div', $class = null, $html = null, $input = null) {
		$this->htmlTag = $htmlTag;
		$this->class = $class;
		$this->html = $html;
		$this->components = [null];
		$this->input = $input;
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
	 * Append a string to go after the opening tag.
	 * @param string $after
	 * @return self
	 */
	function appendAfterOpeningTag($after) {
		$this->afterOpening .= $after;
		return $this;
	}

	/**
	 * Set a string to go after the opening tag and will be reset by render().
	 * @param string $after
	 * @return self
	 */
	protected function useAfterOpeningTag($after) {
		$this->withAfterOpening .= $after;
		return $this;
	}

	/**
	 * Set a string to go before the closing tag.
	 * @param string $before
	 * @return self
	 */
	function setBeforeClosingTag($before) {
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
	 * Prepend a string to go before the closing tag.
	 * @param string $before
	 * @return self
	 */
	function prependBeforeClosingTag($before) {
		$this->beforeClosing = $before . $this->beforeClosing;
		return $this;
	}

	/**
	 * Set a string to go before the closing tag and will be reset by render().
	 * @param string $before
	 * @return self
	 */
	protected function useBeforeClosingTag($before) {
		$this->withBeforeClosing = $before . $this->withBeforeClosing;
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
	 * them with a space. Note that a space is automatically prefixed to $className.
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
	 * add classes. Note that a space is automatically prefixed to $html.
	 * @param string $html
	 * @return self
	 */
	function appendHtml($html) {
		$this->html .= " $html";
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
		$before = false;
		foreach ($this->components as $index => $component) {
			if ($index === $newIndex) {
				if ($before) {
					$array[] = $component;
					$array[] = null;
				} else {
					$array[] = null;
					$array[] = $component;
				}
				continue;
			}
			if ($component) {
				$array[] = $component;
			} else {
				$before = true;
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
	 * Get the components to be rendered. Note that null represents this component.
	 * @return array
	 */
	function getComponents() {
		return $this->components;
	}

	/**
	 * Syntactic sugar for the render(...) method.
	 * @return string
	 */
	function __toString() {
		return $this->render();
	}

	/**
	 * Set the key used to get input for rendered components. Note that this is not used for the
	 * input to setHtmlCallback() and setCssCallback(). Instead, the input is passed to these functions
	 * as if a key was never set.
	 * @param string $key
	 * @return self
	 */
	function setKey($key) {
		$this->key = $key;
		return $this;
	}

	/**
	 * Get the key used to get input.
	 * @return string
	 */
	function getKey() {
		return $this->key;
	}

	/**
	 * Renders the component.
	 * @param mixed $input Any input for the component. The effect of the input depends on the child
	 * class of the component. By default, it is simply echoed.
	 * @param Component $included The component that is calling render(). Should be null if render()
	 * is not called by a component.
	 * @return string
	 */
	function render($input = null, Component $included = null) {
		if ($this->input !== null) {
			$input = $this->input;
		}
		$output = null;
		$htmlTag = $this->htmlTag;
		if ($htmlTag !== null) {
			//Get HTML
			$htmlCallback = $this->htmlCallback;
			$htmlCallback = $htmlCallback ? ' ' . $htmlCallback($input, $included) . ' ' : '';
			//Get CSS classes
			$cssCallback = $this->cssCallback;
			$cssCallback = $cssCallback ? $cssCallback($input, $included) . ' ' : '';
			$classes = $cssCallback . $this->class . $this->withCssClass;
			if ($classes) {
				$classes = " class='$classes'";
			}
			$html = $htmlCallback . $this->html . $this->withHtml;
			if ($html) {
				$html = " $html";
			}
			$output .= "<$htmlTag" . $html . "$classes>";
		}
		$output .= $this->afterOpening . $this->withAfterOpening;
		$output .= $this->renderComponents($this->getValueFromInput($this->key, $input), $included);
		$output .= $this->withBeforeClosing . $this->beforeClosing;
		if ($htmlTag !== null) {
			$output .= "</$htmlTag>";
		}
		$this->withHtml = null;
		$this->withCssClass = null;
		$this->withAfterOpening = null;
		$this->withBeforeClosing = null;
		return $output;
	}

	/**
	 * Get a value from the input using a key. If key is null, then the $input is returned. If
	 * $input[$key] is not set, than null is returned.
	 * @param string $key
	 * @param mixed $input
	 * @return string
	 */
	protected function getValueFromInput($key, $input) {
		if ($key === null) {
			return $input;
		}
		if (isset($input[$key])) {
			return $input[$key];
		}
		return null;
	}

	/**
	 * Render the components.
	 * @param array|ActiveRecordInterface $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderComponents($input = null, Component $included = null) {
		$output = null;
		foreach ($this->components as $component) {
			if (!$component) {
				$output .= $this->renderSpecial($input, $included);
			} else {
				$output .= $component->render($input, $this);
			}
		}
		return $output;
	}

	/**
	 * Renders the child class of this component.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderSpecial($input = null, Component $included = null) {
		return $input;
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
