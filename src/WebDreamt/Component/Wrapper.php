<?php

namespace WebDreamt\Component;

use WebDreamt\Component;

/**
 * Allows one to set a component inside another component.
 */
class Wrapper extends Component {

	/**
	 * The component to display.
	 * @var Component
	 */
	protected $display;

	/**
	 * Construct a wrapper. Note that title is automatically set to the title of the display
	 * component.
	 * @param Component $display Specify a component which the wrapper goes around.
	 */
	function __construct(Component $display) {
		parent::__construct();
		$this->display = $display;
		$this->title = $display->getTitle();
	}

	function renderMe($input = null, $included = null) {
		$this->display->render($input, static::class);
	}

}
