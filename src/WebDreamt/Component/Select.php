<?php

namespace WebDreamt\Component;

use WebDreamt\Component;

class Select extends Component {

	protected $value = null;
	protected $firstOption = true;

	/**
	 * Construct a select box.
	 * @param string $table
	 */
	function __construct() {
		parent::__construct();
		$this->addCssClass('form-control');
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
	 * Get what element is being selected for.
	 * @return string
	 */
	function getSelected() {
		return $this->value;
	}

	/**
	 * Allows inserting a non-selectable first option in the select box.
	 * If null is passed, then there will be no first option in the select box, as is default.
	 * @param string $text The first option's text
	 * @return self
	 */
	function setFirstOption($text) {
		$this->firstOption = $text;
		return $this;
	}

	/**
	 * Get the first option.
	 * @return string
	 */
	function getFirstOption() {
		return $this->firstOption;
	}

	function renderMe($input = null, $included = null) {
		if (!$input) {
			return;
		}
		foreach ($input as $value) {
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
		}
	}

}
