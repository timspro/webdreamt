<?php

namespace WebDreamt;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\TableMap;

class Styler {

	public function __construct(Box $box) {
		$this->vendor = $box->VendorDirectory;
	}

	/**
	 * Makes a Bootstrap-compatible form.
	 * @param TableMap $table
	 * @param ActiveRecordInterface $record
	 * @param boolean $edit
	 * @param string $formHtml
	 * @param boolean $retrieveFK
	 * @return string
	 */
	public function form(TableMap $table, ActiveRecordInterface $record = null, $edit = false,
			$formHtml = "", $showFields = null, $hideFields = null, $retrieveFK = true) {
		$html = "<form $formHtml role='form'>";
		foreach ($table->getColumns() as $column) {
			$phpName = $column->getPhpName();
			$spacedName = preg_replace('/(?<! )(?<!^)[A-Z]/', ' $0', $phpName);
			$value = '';
			if ($record) {
				$value .= "value='" . $record->getByName($phpName) . "'";
			}
			$html .= "<div class='form-group'><label class='sr-only' for='$phpName'>$spacedName</label>" .
					"<input class='form-control' type='text' id='$phpName' name='$phpName' $value/></div>";
		}
		$html .= "</form>";
		return $html;
	}

	public function display(TableMap $table, ActiveRecordInterface $record) {

	}

	public function table(TableMap $table, ActiveRecordInterface $records = null) {

	}

}
