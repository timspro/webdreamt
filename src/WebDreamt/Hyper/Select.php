<?php

namespace WebDreamt\Hyper;

class Select extends Group {

	protected $value = null;

	/**
	 * Construct a select box.
	 * @param string $table
	 */
	function __construct($table = null) {
		parent::__construct($table, 'select', 'option');
		$this->addCssClass('form-control');
		$this->setChildHtml(function($value) {
			$id = $this->getValueFromInput('id', $value);
			if ($id) {
				if ($value && $id === $this->value) {
					return "selected value='$id'";
				} else {
					return "value='$id'";
				}
			} else if ($value && $value === $this->value) {
				return 'selected';
			}
			return '';
		});
		$this->setAfterOpeningTag("<option value='' disabled='' selected=''></option>\n");
	}

	/**
	 * Set the selected element in the select box.
	 * @param string $value
	 */
	function setSelected($value = null) {
		$this->value = $value;
	}

	/**
	 * Changes the first option in the select box, which defaults to a blank entry.
	 * If null is passed, then there will be no first option in the select box.
	 * @param string $text
	 */
	function setFirstOption($text = '') {
		if ($text === null) {
			$this->setAfterOpeningTag('');
		} else {
			$this->setAfterOpeningTag("<option value='' disabled='' selected=''>$text</option>\n");
		}
	}

}
