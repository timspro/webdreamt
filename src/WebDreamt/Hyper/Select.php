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
	}

	/**
	 * Set the selected element in the select box.
	 * @param string $value
	 * @return self
	 */
	function setSelected($value = null) {
		$this->value = $value;
		return $this;
	}

	/**
	 * Allows inserting a non-selectable first option in the select box.
	 * If null is passed, then there will be no first option in the select box, as is default.
	 * @param string $text The first option's text
	 * @return self
	 */
	function setFirstOption($text = null) {
		if ($text === null) {
			$this->setAfterOpeningTag('');
		} else {
			$this->setAfterOpeningTag("<option value='' disabled='' selected=''>$text</option>\n");
		}
		return $this;
	}

}
