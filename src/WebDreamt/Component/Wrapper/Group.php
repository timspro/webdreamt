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
	 * Construct a Group. This sets the title to the plural form of the display component's title.
	 * This takes as input something that is iterable.
	 * @param Component $display
	 */
	function __construct(Component $display, $htmlTag = 'div', $class = null, $html = null) {
		parent::__construct($display, $htmlTag, $class, $html);
		$this->title = Box::now()->pluralize($this->getTitle());
	}

	protected function renderMe($input = null, $included = null) {
		if (!$input) {
			return;
		}
		foreach ($input as $key => $value) {
			$this->display->render($value, $this);
		}
	}

}
