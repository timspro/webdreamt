<?php

namespace WebDreamt\Component\Wrapper\Group;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper\Group;

class Select extends Group {

	/**
	 * Indicates the value to be selected for in the select.
	 * @var string
	 */
	protected $value;
	/**
	 * The option component
	 * @var Component
	 */
	protected $optionComponent;

	/**
	 * Construct a select component. Also, this creates a component with an HTML tag of 'option' and
	 * sets it be the select component's display component.
	 * @param string $key The key to use for setKey() for the option. This allows the options to be
	 * filled in using a column of an input array (while the 'id' column will automatically be filled
	 * in). Can be null in which case the options will just be filled in with the array elements.
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct($key = null, $class = null, $html = null, $input = null) {
		$option = new Component('option');
		$option->setKey($key);
		parent::__construct($option, 'select', "form-control $class", $html, $input);
		$option->setHtmlCallback(function($value, Component $included = null) {
			$component = $included ? : $this;
			$id = $component->getValueFromInput('id', $value);
			if ($this->key !== null) {
				$value = $component->getValueFromInput($this->key, $value);
			}
			if ($id !== null) {
				if ($value !== null && $id === $this->value) {
					return "selected='' value='$id'";
				} else {
					return "value='$id'";
				}
			} else if ($value !== null && $value === $this->value) {
				return 'selected=""';
			}
			return '';
		});
	}

	/**
	 * Set the selected element in the select box.
	 * @param string $value
	 * @return self
	 */
	function setSelected($value) {
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

}
