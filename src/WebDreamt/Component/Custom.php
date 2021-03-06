<?php

namespace WebDreamt\Component;

use WebDreamt\Component;

/**
 * A class that allows one to use a function to render input.
 */
class Custom extends Component {

	/**
	 * The function to use.
	 * @var callable
	 */
	protected $function;
	/**
	 * Indicates if the function should be called everytime or just when the input is not null.
	 * @var boolean
	 */
	protected $always;

	/**
	 * Give a function that will be used to render any input.
	 * @param callable $function The function should output (i.e. via echo) its output. It is called with
	 * two parameters: the input and the object that called the render function.
	 * @param boolean $always If false, then the function will only be called when the input is not null.
	 * If true, then the function will be called everytime.
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct($function, $always = false, $htmlTag = null, $class = null, $html = null,
			$input = null) {
		parent::__construct($htmlTag, $class, $html, $input);
		$this->function = $function;
		$this->always = $always;
	}

	/**
	 * Render the custom component.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderInput($input = null, Component $included = null) {
		$function = $this->function;
		if ($this->always || $input !== null) {
			return $function($input, $included);
		}
		return '';
	}

}
