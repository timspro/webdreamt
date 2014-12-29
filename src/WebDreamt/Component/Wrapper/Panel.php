<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

/**
 * Create a Bootstrap panel.
 */
class Panel extends Wrapper {

	/**
	 * Construct a panel.
	 * @param Component $display
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct(Component $display, $class = null, $html = null, $input = null) {
		parent::__construct($display, 'div', "panel panel-default $class", $html, $input);
	}

	/**
	 * Render the panel.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderSpecial($input = null, Component $included = null) {
		$output = '';
		$output .= '<div class="panel-heading"><span class="panel-title">' . $this->title . '</span></div>';
		$output .= '<div class="panel-body">' . $this->display->render($input, $this) . '</div>';
		return $output;
	}

}
