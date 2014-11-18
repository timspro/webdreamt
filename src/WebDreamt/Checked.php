<?php

namespace WebDreamt;

class Checked {

	private $myValue;

	public function __construct($checkedValue = null) {
		$this->myValue = $checkedValue;
	}

	function select($currentValue) {
		$ret = "value='$currentValue'";
		if ($this->myValue && $this->myValue === $currentValue) {
			$ret .= " CHECKED";
		}
		return $ret;
	}

}
