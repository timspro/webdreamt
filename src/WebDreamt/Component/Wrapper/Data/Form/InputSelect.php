<?php

namespace WebDreamt\Component\Wrapper\Data\Form;

use WebDreamt\Component\Wrapper\Data\Form;

class InputSelect extends Form {

	/**
	 * Create an abbreviated form that allows you to select from options or create a new entry.
	 * @param string $tableName
	 * @param string $columnName
	 * @param array $options
	 * @param string $class
	 * @param string $html
	 * @param array $input
	 */
	public function __construct($tableName, $columnName, $options, $class = null, $html = null,
			$input = null) {
		parent::__construct($tableName, "$class wd-input-select", $html, $input);
		$this->link('id', new Select($options));
		$this->deny()->allow('id', $columnName);
		$this->reorder([$columnName, 'id']);
		$this->setLabels(['id' => null]);
	}

}
