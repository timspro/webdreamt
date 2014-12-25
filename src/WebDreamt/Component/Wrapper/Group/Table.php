<?php

namespace WebDreamt\Component\Wrapper\Group;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper\Data;
use WebDreamt\Component\Wrapper\Group;

/**
 * A class to easily render a table based on a database table.
 */
class Table extends Group {

	/**
	 * The number column
	 * @var boolean
	 */
	protected $numberable = false;
	/**
	 * The number row header
	 * @var string
	 */
	protected $numberHeader = '#';
	/**
	 * The header component
	 * @var Component
	 */
	protected $header;

	/**
	 * Create a table.
	 * @param string $tableName
	 */
	function __construct($tableName = null, $class = null, $html = null) {
		$header = new Component('th', $class, $html);
		$this->header = $header;
		$cell = new Component('td');
		if ($tableName) {
			$row = new Data($cell, $tableName, 'tr');
			$row->hide('id')->setLabelComponent($header, null);
		} else {
			$row = new Group($cell, 'tr');
		}
		parent::__construct($row, 'table');

		$this->appendCssClass('table');
	}

	/**
	 * Get the cell component.
	 * @return Component
	 */
	function getCellComponent() {
		return $this->display->getDisplay();
	}

	/**
	 * Set the cell component.
	 * @param Component $cell
	 */
	function setCellComponent(Component $cell) {
		$this->display->setDisplayComponent($cell);
	}

	/**
	 * Get the row component.
	 * @return Component
	 */
	function getRowComponent() {
		return $this->display;
	}

	/**
	 * Set the row component.
	 * @param Component $row
	 */
	function setRowComponent(Component $row) {
		$this->display = $row;
	}

	/**
	 * Get the header component.
	 * @return Component
	 */
	function getHeaderComponent() {
		return $this->header;
	}

	/**
	 * Set the header component. This can be null, in which case no column headers are used.
	 * @param Component $header
	 */
	function setHeaderComponent(Component $header = null) {
		$this->header = $header;
	}

	/**
	 * If the parameters is true, then the table will have row numbers.
	 * @param boolean $numberable
	 */
	function setNumberable($numberable = false) {
		$this->numberable = $numberable;
		return $this;
	}

	/**
	 * Get if the table is numberable.
	 * @return boolean
	 */
	function getNumberable() {
		return $this->numberable;
	}

	/**
	 * Set the number header. Default is '#'.
	 * @param string $numberHeader Set the row number header.
	 * @return self
	 */
	function setNumberHeader($numberHeader = '#') {
		$this->numberHeader = $numberHeader;
		return $this;
	}

	/**
	 * Get the number header.
	 * @return string
	 */
	function getNumberHeader() {
		return $this->numberHeader;
	}

	protected function render($input = null, $included = null) {
		$opening = '';
		if ($this->header) {
			$opening .= '<thead>';
			ob_start();
			if ($this->display instanceof Data) {
				$this->display->printLabels();
			} else {
				foreach ($input as $key => $value) {
					$this->header->render(static::beautify($key), $this);
				}
			}
			$opening .= ob_get_clean() . '</thead><tbody>';
		}

		$closing = '</tbody></table>';
		$this->useAfterOpeningTag($opening)->useBeforeClosingTag($closing);
		parent::render($input, $included);
	}

}
