<?php

namespace WebDreamt\Cache;

/**
 * A class that stubs the methods of a Component and renders the template when __toString is called.
 */
class Resource {

	private $filename;
	private $input;

	/**
	 * Constructs a Resource object.
	 * @param string $filename
	 * @param array $input
	 */
	function __construct($filename, $input) {
		$this->filename = $filename;
		$this->input = $input;
	}

	/**
	 * Any method calls simply returns the Resource class.
	 * @param string $name
	 * @param array $arguments
	 * @return Resource
	 */
	function __call($name, $arguments) {
		return $this;
	}

	/**
	 * Renders the template.
	 * @return string
	 */
	function __toString() {
		$input = $this->input;
		ob_start();
		require $this->filename;
		return ob_end_flush();
	}

}
