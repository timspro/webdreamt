<?php

namespace WebDreamt\Common;

abstract class Store {

	private static $store;

	/**
	 * Constructs a Store instance.
	 */
	public function __construct() {
		if (!isset(self::$store)) {
			self::$store = $this;
		}
	}

	/**
	 * Gets a Store instance.
	 * @return Store
	 */
	static public function a() {
		return self::$store;
	}

	/**
	 * Checks to see if the property is defined. If not, then will use the initializer to construct
	 * one.
	 * @param string $name The property name
	 * @param callable $initializer The function to use for initialization
	 * @return mixed
	 */
	protected function factory($name, callable $initializer) {
		if (!isset($this->$name)) {
			$this->$name = $initializer();
		}
		return $this->$name;
	}

}
