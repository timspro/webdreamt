<?php

namespace WebDreamt;

/**
 * A class that allows for objects to be easily rendered in a different order than the order
 * they appear in the code. This is most useful when dealing with components.
 */
class Store {

	protected $store = [];

	/**
	 * Execute and return the result of the function for the given key.
	 * @param string $key
	 * @return mixed
	 */
	function get($key) {
		return $this->store[$key]();
	}

	/**
	 * Set a function to execute for a particular key.
	 * @param string $key
	 * @param function $callable
	 */
	function set($key, $callable) {
		$this->store[$key] = $callable;
	}

}
