<?php

namespace WebDreamt\Component;

use WebDreamt\Component;

/**
 * A class that allows one to set a component around another component.
 */
class Wrapper extends Component {

	/**
	 * The component to display.
	 * @var Component
	 */
	protected $display;

	/**
	 * Construct a wrapper. The title is automatically set to the title of the displayed component.
	 * @param Component $display Specify a component which the wrapper goes around. Can be null, which
	 * indicates that the wrapper should just use the default component given by new Component().
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 * @param string
	 */
	function __construct(Component $display = null, $htmlTag = 'div', $class = null, $html = null,
			$input = null) {
		if ($display === null) {
			$display = new Component();
		}
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
	 * @return static
	 */
	function setDisplayComponent(Component $display) {
		$this->display = $display;
		$this->title = $display->getTitle();
		return $this;
	}

	/**
	 * Render the input by calling the display component's render method.
	 * @param mixed $input
	 * @param Component $included
	 */
	protected function renderSpecial($input = null, Component $included = null) {
		return $this->display->render($input, $this);
	}

}
