<?php

namespace WebDreamt\Extra;

class Select {

	private $myValue;

	public function __construct($checkedValue = null) {
		$this->myValue = $checkedValue;
	}

	function select($currentValue) {
		$ret = "value='$currentValue'";
		if ($this->myValue === $currentValue) {
			$ret .= " SELECTED";
		}
		return $ret;
	}

}
