<?php

namespace WebDreamt\Hyper;

/**
 * A class that allows one to basically use a function to render input.
 * Note that only the parent's setInput() method will have an effect since this class
 * isn't associated with a table and the render function is essentially user-provided.
 */
class Custom extends Component {

	/**
	 * The function to use
	 * @var callable
	 */
	protected $function;
	/**
	 * Indicates if the function should be called when the input is null.
	 * @var boolean
	 */
	protected $onNull;

	/**
	 * Give a function that will be used to render any input.
	 * Note this automatically is not placed in an HTML wrapper.
	 * @param callable $function The function should output its output.
	 * @param boolean $onNull If true, then the function will be called when the input is null. If, false
	 * then the function will be called only when the input is not null.
	 */
	function __construct($function, $onNull = false) {
		parent::__construct(null);
		$this->function = $function;
		$this->onNull = $onNull;
		$this->wrapper = '';
	}

	/**
	 * Renders the Custom Component.
	 * @param array $input
	 * @param string $included
	 * @return string
	 */
	function renderChild($input = null, $included = null) {
		if ($this->input) {
			$input = $this->input;
		}
		$function = $this->function;
		if ($this->onNull || $input === null) {
			$function($input);
		}
		return '';
	}

}
