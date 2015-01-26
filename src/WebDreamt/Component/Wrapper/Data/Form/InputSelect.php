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
		parent::__construct($tableName, "$class wd-input-select", $html, $input);
		$this->link('id', new Select(array_merge(["" => 'Choose'], $options)));
		$this->deny()->allow('id', $columnName);
		$this->reorder([$columnName, 'id'])->setHtmlExtra([$columnName => 'placeholder="Create or..."']);
		$this->setLabels(['id' => null]);
	}

}
