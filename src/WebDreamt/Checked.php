<?php

namespace WebDreamt;

class Checked {

	private $myValue;

	public function __construct($checkedValue = null) {
		$this->myValue = $checkedValue;
	}

	function check($currentValue) {
		$ret = "value='$currentValue'";
		if ($this->myValue === $currentValue) {
			$ret .= " CHECKED";
		}
		return $ret;
	}

}
