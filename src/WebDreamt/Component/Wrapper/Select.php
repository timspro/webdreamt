<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

class Select extends Wrapper {

	/**
	 * The values and text to use with the option tag.
	 * @var array
	 */
	protected $options;
	/**
	 * Indicate whether option's tag should have value attribute.
	 * @var boolean
	 */
	protected $noValues = false;

	/**
	 * Construct a select component. Also, this creates a component with an HTML tag of 'option' and
	 * sets it be the select component's display component.
	 * @param array $options A key-value array of the option's value and text
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct($options, $class = null, $html = null, $input = null) {
		$option = new Component('option');
		parent::__construct($option, 'select', "form-control $class", $html, $input);
		$this->options = $options;
	}

	protected function renderSpecial($input = null, Component $included = null) {
		$output = '';
		foreach ($this->options as $key => $value) {
			if ($this->noValues) {
				if ($input === $value) {
					$this->display->useHtml("selected=''");
				}
			} else {
				if ($input === $key) {
					$this->display->useHtml("selected=''");
				}
				$this->display->useHtml("value='$key'");
			}
			$output .= $this->display->render($value, $this);
		}
		return $output;
	}

	/**
	 * Get the options.
	 * @return array
	 */
	function getOptions() {
		return $this->options;
	}

	/**
	 * Se the options.
	 * @param array $options
	 * @return static
	 */
	function setOptions($options) {
		$this->options = $options;
		return $this;
	}

	/**
	 * Get if the value attribute should be used by the select.
	 * @return boolean
	 */
	function getNoValues() {
		return $this->noValues;
	}

	/**
	 * Set if the value attribute should be used by the select.
	 * @param boolean $noValues
	 * @return static
	 */
	function setNoValues($noValues) {
		$this->noValues = $noValues;
		return $this;
	}

	/**
	 * Make the select box use Select2.js
	 * @return static
	 */
	function useSelect2() {
		$this->setCssClass('wd-select2');
		return $this;
	}

}
