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
	function __construct(Component $display, $htmlTag = 'div', $class = null, $html = null) {
		parent::__construct($htmlTag, $class, $html);
		$this->display = $display;
		$this->title = $display->getTitle();
	}

	/**
	 * Get the display component.
	 * @return Component
	 */
	function getDisplayComponent() {
		return $this->display;
	}

	/**
	 * Set the display component.
	 * @param Component $display
	 */
	function setDisplayComponent(Component $display) {
		$this->display = $display;
	}

	function renderSpecial($input = null, $included = null) {
		$this->display->render($input, $this);
	}

}
