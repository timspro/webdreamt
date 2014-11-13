<?php

namespace WebDreamt\Hyper;

class Select extends Group {

	function __construct($table) {
		parent::__construct($table, 'select', 'option');
		$this->addCssClass('form-control');
	}

}
