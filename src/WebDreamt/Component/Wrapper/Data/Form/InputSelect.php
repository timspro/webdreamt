<?php

namespace WebDreamt\Component\Wrapper\Data\Form;

use WebDreamt\Component\Wrapper\Data\Form;
use WebDreamt\Component\Wrapper\Select;

/**
 * A class that allows one to either select an element in the database or create a new one.
 * This is mostly intended to work with other forms.
 */
class InputSelect extends Form {

	/**
	 * Create an abbreviated form that allows you to select an already existing entry or create
	 * a new entry in a table by specifying a value for a given column. The given table must use an
	 * 'id' column.
	 * @param string $tableName The name of the table to use for the input and the select.
	 * @param string $columnName The column to use for the new input.
	 * @param array $options The alternate options for the input. Note that these options are
	 * directly passed to Select() and must fill the "value" attribute with the entry's 'id' column.
	 * @param string $class
	 * @param string $html
	 * @param array $input
	 */
	function __construct($tableName, $columnName, $options, $class = null, $html = null, $input = null) {
		parent::__construct($tableName, $class, $html, $input);
		$newOptions = ["" => 'Choose...'];
		foreach ($options as $key => $value) {
			$newOptions[$key] = $value;
		}
		$this->link('id', new Select($newOptions, 'wd-is-select'));
		$this->deny()->allow('id', $columnName);
		$this->reorder([$columnName, 'id'])->setHtmlExtra([$columnName => 'placeholder="Create or..."'])
				->setHtmlClass([$columnName => 'wd-is-input']);
		$this->setInputHook(function($column, $options, $name, &$value) use ($columnName) {
			if ($column === $columnName) {
				$value = '';
			}
		});
	}

}
