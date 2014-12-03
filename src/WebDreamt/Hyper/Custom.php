<?php

namespace WebDreamt\Hyper;

class Custom extends Component {

	protected $function;

	function __construct($function) {
		$this->function = $function;
	}

	function render($input = null, $included = null) {
		if ($this->input) {
			$input = $this->input;
		}
		$function = $this->function;
		if ($input) {
			return $function($input);
		}
		return '';
	}

}
