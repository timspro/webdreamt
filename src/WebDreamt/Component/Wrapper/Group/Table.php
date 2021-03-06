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
	 * The header component
	 * @var Component
	 */
	protected $header;
	/**
	 * Indicates whether to use headers or not.
	 * @var boolean
	 */
	protected $headerable = false;
	/**
	 * Headers to use for the table
	 * @var array
	 */
	protected $headers;

	/**
	 * Create a table. If a table name is provided, then the rows will be based on a data component
	 * that automatically has the label class 'wd-header'.
	 * If no table name is provided, then the row component is a group component.
	 * In both cases, labels/headers won't be displayed if headerable is false, which is default.
	 * @param string $tableName A name of a table in the database. Can be null.
	 */
	function __construct($tableName = null, $class = null, $html = null, $input = null) {
		$header = new Component('th', $class, $html);
		$this->header = $header;
		$cell = new Component('td');
		if ($tableName) {
			$row = new Data($tableName, $cell, 'tr');
			$row->hide('id')->setLabelComponent($header)->setLabelClass('wd-header');
		} else {
			$row = new Group($cell, 'tr');
		}
		parent::__construct($row, 'table', $class, $html, $input);

		$this->appendCssClass('table');
	}

	/**
	 * Get the cell component.
	 * @return Component
	 */
	function getCellComponent() {
		return $this->display->getDisplayComponent();
	}

	/**
	 * Set the cell component.
	 * @param Component $cell
	 * @return static
	 */
	function setCellComponent(Component $cell) {
		$this->display->setDisplayComponent($cell);
		return $this;
	}

	/**
	 * Get the row component. This has the same effect as getDisplay().
	 * @return Component
	 */
	function getRowComponent() {
		return $this->display;
	}

	/**
	 * Set the row component. This has the same effect as setDisplay().
	 * @param Group $row
	 * @return static
	 */
	function setRowComponent(Group $row) {
		$this->display = $row;
		return $this;
	}

	/**
	 * Get the header component.
	 * @return Component
	 */
	function getHeaderComponent() {
		return $this->header;
	}

	/**
	 * Set the header component.
	 * @param Component $header
	 * @return static
	 */
	function setHeaderComponent(Component $header) {
		$this->header = $header;
		return $this;
	}

	/**
	 * Set whether to use headers or not.
	 * @param boolean $headerable
	 * @return static
	 */
	function setHeaderable($headerable) {
		$this->headerable = $headerable;
		return $this;
	}

	/**
	 * Get whether to use headers or not.
	 * @return boolean
	 */
	function getHeaderable() {
		return $this->headerable;
	}

	/**
	 * Set headers for the table. Note that this won't work if the row component is a data component.
	 * This also automatically sets headerable to true.
	 * @param array $headers
	 * @return static
	 */
	function setHeaders($headers) {
		$this->headerable = true;
		$this->headers = $headers;
		return $this;
	}

	/**
	 * Get the set headers.
	 * @return array
	 */
	function getHeaders() {
		return $this->headers;
	}

	/**
	 * Render the table.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	function render($input = null, $included = null) {
		if ($this->headerable) {
			$opening = '<thead>';
			if ($this->display instanceof Data) {
				$opening .= $this->display->renderLabels();
			} else if ($this->headers !== null) {
				foreach ($this->headers as $value) {
					$opening .= $this->header->render($value, $this);
				}
			} else {
				foreach ($input as $key => $value) {
					$opening .= $this->header->render(static::beautify($key), $this);
				}
			}
			$opening .= '</thead><tbody>';
		} else {
			$opening = '<tbody>';
		}

		$closing = '</tbody>';
		$this->useAfterOpeningTag($opening)->useBeforeClosingTag($closing);
		return parent::render($input, $included);
	}

}
