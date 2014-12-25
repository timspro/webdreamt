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
	 * Indicates the first option to display in the dropdown.
	 * @var string
	 */
	protected $firstOption = null;
	/**
	 * The option component
	 * @var Component
	 */
	protected $optionComponent;

	/**
	 * Construct a select box.
	 * @param string $table
	 */
	function __construct($class = null, $html = null) {
		$option = new Component('option', $class, $html);
		parent::__construct($option, 'select');
		$this->addCssClass('form-control');
		$option->setHtmlCallback(function($value) {
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

	/**
	 * Render the component.
	 * @param array $input
	 * @param string $included
	 */
	function render($input = null, $included = null) {
		$this->useAfterOpeningTag($this->firstOption);
		parent::render($input, $included);
	}

}
