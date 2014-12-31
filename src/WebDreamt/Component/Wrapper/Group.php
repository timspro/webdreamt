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
		$this->title = Box::now()->pluralize($display->getTitle());
	}

	/**
	 * Set the display component. Note that the title is set to the plural fom of the display component's
	 * title.
	 * @param Component $display
	 * @return self
	 */
	function setDisplayComponent(Component $display) {
		$this->display = $display;
		$this->title = Box::now()->pluralize($display->getTitle());
		return $this;
	}

	/**
	 * Render the group.
	 * @param string|array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderSpecial($input = null, Component $included = null) {
		if (!$input) {
			return;
		}
		$output = '';
		foreach ($input as $key => $value) {
			$output .= $this->display->render($value, $this);
		}
		return $output;
	}

}
