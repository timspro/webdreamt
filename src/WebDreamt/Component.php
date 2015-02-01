<?php

namespace WebDreamt;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use WebDreamt\Component\Icon;
use WebDreamt\Component\Wrapper;

/**
 * An object-oriented representation of an HTML block.
 */
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
	 * The title of the component. The fffect depends on child component class.
	 * @var string
	 */
	protected $title;
	/**
	 * HTML attributes for the top-level element.
	 * @var string
	 */
	protected $html;
	/**
	 * The HTML tag of the top-level element, not including the HTML tag and the classes.
	 * @var string
	 */
	protected $htmlTag;
	/**
	 * CSS classes for the top-level element.
	 * @var string
	 */
	protected $class;
	/**
	 * An array of components to render. Note that null represents where the component renders the input
	 * in relation to the extra components added.
	 * @var array
	 */
	protected $components = [null];
	/**
	 * The input to be passed to the render method. If not null, then overrides what is passed
	 * via the render() method.
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
	 * A string used after the opening tag and that is reset by render().
	 * @var string
	 */
	protected $withAfterOpening;
	/**
	 * A string used before the closing tag and that is reset by render().
	 * @var string
	 */
	protected $withBeforeClosing;
	/**
	 * A string used for the HTML of the element and that is reset by render().
	 * @var string
	 */
	protected $withHtml;
	/**
	 * A string used for the class of the element and that is reset by render().
	 * @var string
	 */
	protected $withCssClass;
	/**
	 * A key used to get input.
	 * @var string
	 */
	protected $key;
	/**
	 * A string to output when the input is null.
	 * @var string
	 */
	protected $nullInput;
	/**
	 * Indicates if the tag is self-closing.
	 * @var boolean
	 */
	protected $selfClosing = false;
	/**
	 * The component that is currently rendering this component. Null if this component is not being
	 * rendered.
	 * @var Component
	 */
	protected $renderedBy;
	/**
	 * The icon container for the component. Note that this is lazily initiated, so use getIconContainer()
	 * to access it, especially for the potential first time.
	 * @var Wrapper
	 */
	protected $iconContainer;
	/**
	 * The group(s) that can access the component.
	 * @var string|array
	 */
	protected $groups;

	/**
	 * Make a new component using the given HTML tag, class string, HTML attributes, and component input.
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct($htmlTag = 'div', $class = null, $html = null, $input = null) {
		$this->htmlTag = $htmlTag;
		$this->class = $class;
		$this->html = $html;
		$this->input = $input;
	}

	/**
	 * Set the title of the component. The effect of this depends on the child component class;
	 * the title is not displayed automatically.
	 * @param string $title
	 * @return static
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
	 * @return static
	 */
	function setAfterOpeningTag($after) {
		$this->afterOpening = $after;
		return $this;
	}

	/**
	 * Get the string set to go after the opening tag.
	 * @return string
	 */
	function getAfterOpeningTag() {
		return $this->afterOpening;
	}

	/**
	 * Append onto the string that goes after the opening tag.
	 * @param string $after
	 * @return static
	 */
	function appendAfterOpeningTag($after) {
		$this->afterOpening .= $after;
		return $this;
	}

	/**
	 * Set a string to go after the opening tag. This will be reset by render().
	 * @param string $after
	 * @return static
	 */
	protected function useAfterOpeningTag($after) {
		$this->withAfterOpening .= $after;
		return $this;
	}

	/**
	 * Set a string to go before the closing tag.
	 * @param string $before
	 * @return static
	 */
	function setBeforeClosingTag($before) {
		$this->beforeClosing = $before;
		return $this;
	}

	/**
	 * Get the string set to go before the closing tag.
	 * @return string
	 */
	function getBeforeClosingTag() {
		return $this->beforeClosing;
	}

	/**
	 * Prepend onto the string that goes before the closing tag.
	 * @param string $before
	 * @return static
	 */
	function prependBeforeClosingTag($before) {
		$this->beforeClosing = $before . $this->beforeClosing;
		return $this;
	}

	/**
	 * Set a string to go before the closing tag. This will be reset by render().
	 * @param string $before
	 * @return static
	 */
	protected function useBeforeClosingTag($before) {
		$this->withBeforeClosing = $before . $this->withBeforeClosing;
		return $this;
	}

	/**
	 * Set the CSS class(es) of the top-level element. This will overwrite any CSS classes the child
	 * component sets and so appendCssClass() should be preferred.
	 * @param string $className
	 * @return static
	 */
	function setCssClass($className) {
		$this->class = $className;
		return $this;
	}

	/**
	 * Get the CSS class of the top-level element.
	 * @return static
	 */
	function getCssClass() {
		return $this->class;
	}

	/**
	 * Append the CSS class(es) to the top-level element. If you append multiple classes, just separate
	 * them with a space. A space is automatically prefixed to $className.
	 * @param string
	 * @return static
	 */
	function appendCssClass($className) {
		$this->class .= " $className";
		return $this;
	}

	/**
	 * Set the CSS class(es) to be used with the top-level element. These will be reset by render().
	 * A space is automatically prefixed to $className.
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
	 * @return static
	 */
	function setCssClassCallback($callable) {
		$this->cssCallback = $callable;
		return $this;
	}

	/**
	 * Get the class callback if set.
	 * @return string
	 */
	function getCssClassCallback() {
		return $this->cssCallback;
	}

	/**
	 * Set the HTML tag for the topmost element. Can be null, in which case no tag (and so no class
	 * and no additional HTML) is displayed.
	 * @param string $htmlTag
	 * @return static
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
	 * Set the HTML of the top-level element. Note use setCssClass() or appendCssClass() to add classes.
	 * This will overwrite any other HTML that is added, so appendHtml() should be preferred.
	 * @param string $html
	 * @return static
	 */
	function setHtml($html) {
		$this->html = $html;
		return $this;
	}

	/**
	 * Get the HTML of the top-level element without the HTML tag and any classes.
	 * @return string
	 */
	function getHtml() {
		return $this->html;
	}

	/**
	 * Append on to the HTML of the top-level element. Use setCssClass or appendCssClass to
	 * add classes. A space is automatically prefixed to $html.
	 * @param string $html
	 * @return static
	 */
	function appendHtml($html) {
		$this->html .= " $html";
		return $this;
	}

	/**
	 * Set HTML to use with the top-level element. This will be reset by render(). A space is
	 * automatically prefixed to $html.
	 * @param string $html
	 * @return static
	 */
	protected function useHtml($html) {
		$this->withHtml .= " $html";
		return $this;
	}

	/**
	 * Add a callback function to generate HTML attributes based on input.
	 * @param callable $callable This takes as a parameter the current input and class object that
	 * called the render() method and should return the HTML attributes as a string.
	 * @return static
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
	 * Set the input of the component. Input set this way will override input passed to the
	 * render method. Note that setting this to null will just cause the component to use the input
	 * provided by the render method as is default. In cases where you want the component to ignore
	 * that input, you should instead call setInput() with '' or [].
	 * @param array|ActiveRecordInterface $input
	 * @return static
	 */
	function setInput($input) {
		$this->input = $input;
		return $this;
	}

	/**
	 * Get the input of the component as set with setInput().
	 * @return array
	 */
	function getInput() {
		return $this->input;
	}

	/**
	 * Set the index of where the input is rendered in relation to added extra components.
	 * @param int $newIndex If invalid (negative or larger than the array can handle), then the
	 * child component will appear last.
	 * @return static
	 */
	function setInputIndex($newIndex) {
		$array = [];
		//Assume that we are moving the component forward in the array or keeping at the same spot.
		$backward = false;
		$processed = false;
		foreach ($this->components as $index => $component) {
			if ($index === $newIndex) {
				//We need to take into account if the component is being moved forward or backward in the
				//array. If backward, then put the current component before this component.
				if ($backward) {
					$array[] = $component;
					$array[] = null;
				} else {
					$array[] = null;
					$array[] = $component;
				}
				$processed = true;
				continue;
			}
			if ($component) {
				$array[] = $component;
			} else {
				//The $component is null so we know that the component is be moving farther back into the
				//array if we haven't added it already.
				$backward = true;
			}
		}
		//If the new index was invalid, resulting in this component not being added, then just append it
		//to the end.
		if (!$processed) {
			$array[] = null;
		}
		$this->components = $array;
		return $this;
	}

	/**
	 * Get the index of where the input is rendered in relation to the added extra components.
	 * @return int
	 */
	function getInputIndex() {
		foreach ($this->components as $index => $component) {
			if (!$component) {
				return $index;
			}
		}
		return null;
	}

	/**
	 * Append/prepend an extra component to the list of components to be rendered.
	 * @param Component $component
	 * @param boolean $after Indicates whether the extra component should go after or before this
	 * component and all other components.
	 * @return static
	 */
	function addExtraComponent(Component $component, $after = true) {
		if ($after) {
			$this->components[] = $component;
		} else {
			array_unshift($this->components, $component);
		}
		return $this;
	}

	/**
	 * Get the components to be rendered. Note that null represents where the component renders the input
	 * in relation to the extra components added.
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
	 * @return static
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
	 * Set if the tag is self-closing.
	 * @param boolean $selfClosing
	 * @return static
	 */
	function setSelfClosing($selfClosing) {
		$this->selfClosing = $selfClosing;
		return $this;
	}

	/**
	 * Get if the tag is self-closing.
	 * @return boolean
	 */
	function getSelfClosing() {
		return $this->selfClosing;
	}

	/**
	 * Set the value that should be displayed on null input. If the input is null and this has been
	 * called with a non-null value, then that value will be displayed instead.
	 * @param string $nullInput
	 * @return static
	 */
	function setOnNullInput($nullInput) {
		$this->nullInput = $nullInput;
		return $this;
	}

	/**
	 * Get the value that is displayed on null input.
	 * @return string
	 */
	function getOnNullInput() {
		return $this->nullInput;
	}

	/**
	 * Set the group(s) that can access this component. Can be null in which case group membership is
	 * not checked.
	 * @param string|array $groups
	 * @return static
	 */
	function setGroups($groups) {
		$this->groups = $groups;
		return $this;
	}

	/**
	 * Get the group(s) that can access this component.
	 * @return array
	 */
	function getGroups() {
		return $this->groups;
	}

	/**
	 * Render the component.
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
		if ($this->groups !== null) {
			if (!Box::get()->server()->checkGroups($this->groups)) {
				return '';
			}
		}
		$output = null;
		$htmlTag = $this->htmlTag;
		$this->renderedBy = $included;
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
			$output .= "<$htmlTag" . $html . "$classes";
			if ($this->selfClosing) {
				$output .= ' />';
			} else {
				$output .= '>';
			}
		}
		if (!$this->selfClosing) {
			$output .= $this->afterOpening . $this->withAfterOpening;
			$value = $this->getValueFromInput($this->key, $input);
			if ($value === null && $this->nullInput !== null) {
				$output .= $this->nullInput;
			} else {
				$output .= $this->renderComponents($value, $included);
			}
			$output .= $this->withBeforeClosing . $this->beforeClosing;
			if ($htmlTag !== null) {
				$output .= "</$htmlTag>";
			}
		}
		$this->withHtml = null;
		$this->withCssClass = null;
		$this->withAfterOpening = null;
		$this->withBeforeClosing = null;
		$this->renderedBy = null;
		return $output;
	}

	/**
	 * Get the component that is currently rendering this component. Return null if the
	 * component is not currently being rendered.
	 * @return Component
	 */
	function getRenderedBy() {
		return $this->renderedBy;
	}

	/**
	 * Get a value from the input using a key. If key is null, then the $input is returned. If
	 * $input[$key] is not set, then null is returned.
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
				$output .= $this->renderInput($input, $included);
			} else {
				$output .= $component->render($input, $this);
			}
		}
		return $output;
	}

	/**
	 * Render the input given to this component. Child component classes will likely override
	 * this function.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderInput($input = null, Component $included = null) {
		return $input;
	}

	/**
	 * Get the icon container component.
	 * @return Component
	 */
	function getIconContainer() {
		if (!$this->iconContainer) {
			$this->iconContainer = new Wrapper(new Component(null, null, null, ''), 'span', 'wd-icon');
			$this->addExtraComponent($this->iconContainer);
			$this->appendCssClass('wd-relative');
		}
		return $this->iconContainer;
	}

	/**
	 * Add an icon to the icon container.
	 * @param Icon $icon
	 * @return Component
	 */
	function addIcon(Icon $icon) {
		$this->getIconContainer()->addExtraComponent($icon);
		return $this;
	}

	/**
	 * Change underscores into spaces in a column or table name and capitalize the result.
	 * Also, this will change ' Id' to ' ID' if the string is the last part of the resulting name or
	 * 'Id' to 'ID' if that is the entire string.
	 * @param string $name
	 * @return string
	 */
	static protected function beautify($name) {
		$return = ucwords(str_replace('_', ' ', $name));
		if (strlen($return) === 2) {
			if ($return === 'Id') {
				$return = 'ID';
			}
		} else {
			if (substr($return, -3) === ' Id') {
				$return = substr($return, 0, strlen($return) - 2) . 'ID';
			}
		}
		return $return;
	}

}
