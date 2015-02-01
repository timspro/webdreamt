<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

/**
 * Create a Bootstrap panel.
 */
class Panel extends Wrapper {

	/**
	 * Construct a panel. Note that the constructor does not allow you to specify the HTML tag
	 * and requires a value for $display (which can be null). This class will also use its title as the
	 * panel header.
	 * @param Component $display
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct(Component $display, $class = null, $html = null, $input = null) {
		parent::__construct($display, 'div', "panel panel-default $class", $html, $input);
	}

	/**
	 * Render the input by giving it to the display component to fill the panel's body.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderInput($input = null, Component $included = null) {
		$output = '';
		if ($this->title !== null) {
			$output .= '<div class="panel-heading"><span class="panel-title">' . $this->title . '</span></div>';
		}
		$output .= '<div class="panel-body">' . $this->display->render($input, $this) . '</div>';
		return $output;
	}

}
