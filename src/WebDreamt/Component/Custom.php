<?php

namespace WebDreamt\Component;

use WebDreamt\Component;

/**
 * A class that allows one to basically use a function to render input.
 */
class Custom extends Component {

	/**
	 * The function to use
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
	 * two parameters: the input and the class name that are passed to the render function.
	 * @param boolean $always If false, then the function will only be called when the input is not null.
	 * If true, then the function will be called everytime.
	 */
	function __construct($function, $always = false) {
		parent::__construct();
		$this->function = $function;
		$this->always = $always;
	}

	/**
	 * Renders the custom component.
	 * @param array $input
	 * @param string $included
	 * @return string
	 */
	function renderMe($input = null, $included = null) {
		$function = $this->function;
		if ($this->always || $input !== null) {
			$function($input, $included);
		}
		return '';
	}

}
