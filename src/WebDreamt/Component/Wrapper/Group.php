<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Box;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

/**
 * A class used to display iterable input.
 */
class Group extends Wrapper {

	/**
	 * The class prefix used to index the data.
	 * @var string
	 */
	protected $indexClass;
	/**
	 * A component to display the first input in.
	 * @type Component
	 */
	protected $first;
	/**
	 * Indicates if a different component should be used for the first column.
	 * @var boolean
	 */
	protected $useFirst = false;

	/**
	 * Construct a group. This sets the title to the plural form of the display component's title.
	 * This takes as input something that is iterable.
	 * @param Component $display
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct(Component $display = null, $htmlTag = 'div', $class = null, $html = null,
			$input = null) {
		parent::__construct($display, $htmlTag, $class, $html, $input);
		$title = $this->display->getTitle();
		if ($title !== null) {
			$this->title = Box::get()->pluralize($title);
		}
	}

	/**
	 * Set the display component. Note that the title is set to the plural fom of the display component's
	 * title.
	 * @param Component $display
	 * @return static
	 */
	function setDisplayComponent(Component $display) {
		$this->display = $display;
		$this->title = Box::get()->pluralize($display->getTitle());
		return $this;
	}

	/**
	 * Set the CSS class that will be used to identify the index. If null, then no CSS class
	 * will be used. For example, if 'wd' is used, then for the fifth element, the display component
	 * will have class 'wd-5'.
	 * @param string $indexClass
	 * @return static
	 */
	function setIndexClass($indexClass) {
		$this->indexClass = $indexClass;
		return $this;
	}

	/**
	 * Get the index class.
	 * @return string
	 */
	function getIndexClass() {
		return $this->indexClass;
	}

	/**
	 * Set the first component.
	 * @param Component $firstComponent
	 * @return static
	 */
	function setFirstComponent(Component $firstComponent) {
		$this->first = $firstComponent;
		if ($firstComponent === null) {
			$this->useFirst = false;
		} else {
			$this->useFirst = true;
		}
		return $this;
	}

	/**
	 * Get the first component.
	 * @return Component
	 */
	function getFirstComponent() {
		return $this->first;
	}

	/**
	 * Set if a different component should be used for the first input.
	 * @param boolean $first
	 * @return static
	 */
	function setUseFirst($first) {
		if ($first && $this->first === null) {
			$this->first = new Component();
		}
		$this->useFirst = $first;
		return $this;
	}

	/**
	 * Get whether a different component should be used for the first input.
	 * @return boolean
	 */
	function getUseFirst() {
		return $this->useFirst;
	}

	/**
	 * Render the group.
	 * @param string|array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderInput($input = null, Component $included = null) {
		if (!$input) {
			return;
		}
		$output = '';
		if ($this->useFirst) {
			$output .= $this->first->render(array_shift($input), $this);
		}
		foreach ($input as $index => $value) {
			if ($this->indexClass !== null) {
				$this->display->useCssClass($this->indexClass . "-$index");
			}
			$output .= $this->display->render($value, $this);
		}
		return $output;
	}

}
