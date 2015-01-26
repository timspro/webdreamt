<?php

namespace WebDreamt\Component\Wrapper\Data\Form;

use WebDreamt\Component\Wrapper\Data\Form;
use WebDreamt\Component\Wrapper\Select;

class InputSelect extends Form {

	/**
	 * Create an abbreviated form that allows you to select from options or create a new entry.
	 * @param string $tableName The name of the table to use for the input and the select
	 * @param string $columnName The column to use for the input
	 * @param array $options The ids of choices
	 * @param string $class
	 * @param string $html
	 * @param array $input
	 */
	public function __construct($tableName, $columnName, $options, $class = null, $html = null,
			$input = null) {
		parent::__construct($tableName, $class, $html, $input);
		$newOptions = ["" => 'Choose...'];
		foreach ($options as $key => $value) {
			$newOptions[$key] = $value;
		}
		$this->link('id', new Select($newOptions, 'wd-is-select'));
		$this->deny()->allow('id', $columnName);
		$this->reorder([$columnName, 'id'])->setHtmlExtra([$columnName => 'placeholder="Create or..."'])
				->setHtmlClass([$columnName => 'wd-is-input']);
		$this->setLabels(['id' => null]);
	}

}
