<?php

namespace WebDreamt\Extra;

class Promise {

	static private $values = [];
	static private $fulfilled = [];

	/**
	 * Makes a promise that the value will be given later.
	 * @param string $key
	 */
	static public function make($key) {
		self::$values[] = $key;
		self::$fulfilled[$key] = false;
		ob_start();
	}

	/**
	 * Fulfills an earlier made promise.
	 * @param string|array $key Can be an array of keys and values. In this case, $value is ignored.
	 * @param string $value
	 * @throws Exception Thrown if one tries to fulfill a promise before it is made.
	 */
	static public function fulfill($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $key => $value) {
				self::_fulfill($key, $value);
			}
		} else {
			self::_fulfill($key, $value);
		}
	}

	static private function _fulfill($key, $value) {
		if (!isset(self::$fulfilled[$key])) {
			throw new Exception("Cannot fulfill a promise before it is made.");
		}
		self::$fulfilled[$key] = $value;

		if (!in_array(false, self::$fulfilled, true)) {
			for ($i = count(self::$values) - 1; $i >= 0; $i--) {
				$contents = ob_get_contents();
				ob_end_clean();
				echo self::$fulfilled[self::$values[$i]] . $contents;
			}
			self::$values = [];
			self::$fulfilled = [];
		}
	}

}
