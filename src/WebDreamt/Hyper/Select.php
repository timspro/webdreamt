<?php

namespace WebDreamt\Hyper;

class Select extends Group {

	function __construct($table) {
		parent::__construct($table, 'select', 'option');
		$this->addCssClass('form-control');
		$this->setChildHtml(function($value) {
			if (isset($value['id'])) {
				return "value='" . $value['id'] . "'";
			}
			return '';
		});
		$this->setAfterOpeningTag("<option value='' disabled='' selected=''></option>\n");
	}

}
