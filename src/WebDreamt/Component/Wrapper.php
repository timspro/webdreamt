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
	 * Construct a wrapper. Note that title is automatically set to the title of the displayed
	 * component.
	 * @param Component $display Specify a component which the wrapper goes around.
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 * @param string
	 */
	function __construct(Component $display, $htmlTag = 'div', $class = null, $html = null, $input = null) {
		parent::__construct($htmlTag, $class, $html, $input);
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
	 * Set the display component. This also changes the title to the title of the new display component.
	 * @param Component $display
	 * @return self
	 */
	function setDisplayComponent(Component $display) {
		$this->display = $display;
		$this->title = $display->getTitle();
		return $this;
	}

	/**
	 * Renders the wrapper component.
	 * @param mixed $input
	 * @param Component $included
	 */
	protected function renderSpecial($input = null, Component $included = null) {
		return $this->display->render($input, $this);
	}

}
