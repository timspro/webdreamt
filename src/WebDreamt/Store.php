<?php

namespace WebDreamt;

/**
 * A class that allows for objects to be easily rendered in a different order than the order
 * they appear in the code. This is most useful when dealing with components.
 */
class Store {

	protected $store = [];

	/**
	 * If $key has been set to a function, then get() executes the function and keeps the result
	 * as the new value for the key. If $key is set to a value, then returns the value.
	 * @param string $key
	 * @return mixed
	 */
	function get($key) {
		if (is_callable($this->store[$key])) {
			$this->store[$key] = $this->store[$key]();
		}
		return $this->store[$key];
	}

	/**
	 * Set a variable to retrieve or a function to execute for a particular key.
	 * @param string $key
	 * @param mixed $value
	 */
	function set($key, $value) {
		$this->store[$key] = $value;
	}

}
