<?php

namespace WebDreamt\Component\Wrapper\Data;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\ColumnMap;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;
use WebDreamt\Component\Wrapper\Data;
use WebDreamt\Component\Wrapper\Select;
use WebDreamt\Component\Wrapper\Modal;

/**
 * A class to easily render a form for a table in the database.
 */
class Form extends Data {

	/**
	 * Whether the input type is disabled.
	 */
	const OPT_DISABLE = 'disable';
	/**
	 * Whether the input type is required.
	 */
	const OPT_REQUIRE = 'require';
	/**
	 * Indicate the HTML type
	 */
	const OPT_HTML_TYPE = 'htmlType';
	/**
	 * Indicate the HTML class
	 */
	const OPT_HTML_CLASS = 'htmlClass';
	/**
	 * Indicate the HTML extra attributes
	 */
	const OPT_HTML_EXTRA = 'htmlExtra';
	/**
	 * An input with type 'text'
	 */
	const HTML_TEXT = 'text';
	/**
	 * A textarea input
	 */
	const HTML_TEXTAREA = 'textarea';
	/**
	 * A select input with No, Yes entries.
	 */
	const HTML_BOOLEAN = 'boolean';
	/**
	 * An input with type 'number'
	 */
	const HTML_NUMBER = 'number';
	/**
	 * A select input
	 */
	const HTML_SELECT = 'select';

	/**
	 * A count of the number of forms rendered.
	 * @var int
	 */
	protected static $countForm = 0;
	/**
	 * Indicates if the form can handle multiple items.
	 * @var boolean
	 */
	protected $multiple = false;
	/**
	 * The form ID
	 * @var int
	 */
	protected $count;
	/**
	 * A function to give control to change form inputs.
	 * @var callable
	 */
	protected $inputHook;
	/**
	 * Linked select components.
	 * @var array
	 */
	protected $selectComponent = [];

	/**
	 * Construct a Form.
	 * @param string $tableName
	 */
	function __construct($tableName, $class = null, $html = null) {
		$display = new Wrapper($this->input, 'div', "form-group $class", $html);
		parent::__construct($tableName, $display, 'form', null, 'role="form"');
		$this->setLabelComponent(new Component('label'), null);
	}

	protected function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options[self::OPT_DISABLE] = false;
		$options[self::OPT_REQUIRE] = false;
		$options[self::OPT_HTML_TYPE] = null;
		$options[self::OPT_HTML_CLASS] = null;
		$options[self::OPT_HTML_EXTRA] = null;
		return $options;
	}

	protected function addColumn(ColumnMap $column, array &$options) {
		parent::addColumn($column, $options);
		if ($column->getName() === 'id') {
			$options[self::OPT_VISIBLE] = false;
		}
		if ($column->getName() === 'created_at' || $column->getName() === 'updated_at') {
			$options[self::OPT_ACCESS] = false;
		}
		if ($column->isNotNull()) {
			$options[self::OPT_REQUIRE] = true;
		}
		//Set HTML options.
		switch ($options[self::OPT_TYPE]) {
			case PropelTypes::VARCHAR:
				if (intval($options[self::OPT_EXTRA]) < 255) {
					$options[self::OPT_HTML_EXTRA] = 'size="' . $options[self::OPT_EXTRA] . '"';
					$options[self::OPT_HTML_TYPE] = self::HTML_TEXT;
					break;
				}
			case PropelTypes::LONGVARCHAR:
				$options[self::OPT_HTML_EXTRA] = 'size="' . $options[self::OPT_EXTRA] . '"';
				$options[self::OPT_HTML_TYPE] = self::HTML_TEXTAREA;
				break;
			case PropelTypes::INTEGER:
				$options[self::OPT_HTML_EXTRA] = 'size="' . $options[self::OPT_EXTRA] . '"';
				$options[self::OPT_HTML_TYPE] = self::HTML_NUMBER;
				break;
			case PropelTypes::FLOAT:
			case PropelTypes::DOUBLE:
			case PropelTypes::DECIMAL:
				$options[self::OPT_HTML_EXTRA] = "step='0.01'";
				$options[self::OPT_HTML_TYPE] = self::HTML_NUMBER;
				break;
			case PropelTypes::BOOLEAN:
				$options[self::OPT_HTML_TYPE] = self::HTML_BOOLEAN;
				break;
			case PropelTypes::CHAR:
			case PropelTypes::ENUM:
				$options[self::OPT_HTML_TYPE] = self::HTML_SELECT;
				break;
			case PropelTypes::TIME:
				$options[self::OPT_HTML_TYPE] = self::HTML_TEXT;
				$options[self::OPT_HTML_CLASS] = 'wd-time-control';
				break;
			case PropelTypes::DATE:
				$options[self::OPT_HTML_TYPE] = self::HTML_TEXT;
				$options[self::OPT_HTML_CLASS] = 'wd-date-control';
				break;
			case PropelTypes::TIMESTAMP:
				$options[self::OPT_HTML_TYPE] = self::HTML_TEXT;
				$options[self::OPT_HTML_CLASS] = 'wd-datetime-control';
				break;
		}
	}

	/**
	 * Sets the HTML class string of the columns.
	 * @param array $columns
	 * @return this
	 */
	function setHtmlClass($columns) {
		$this->mergeOptions($columns, self::OPT_HTML_CLASS);
		return $this;
	}

	/**
	 * Sets the HTML type of the columns.
	 * @param array $columns
	 * @return this
	 */
	function setHtmlType($columns) {
		$this->mergeOptions($columns, self::OPT_HTML_TYPE);
		return $this;
	}

	/**
	 * Sets the extra HTML attributes of the columns.
	 * @param array $columns
	 * @return this
	 */
	function setHtmlExtra($columns) {
		$this->mergeOptions($columns, self::OPT_HTML_EXTRA);
		return $this;
	}

	/**
	 * Makes all columns required. Defaults to all columns.
	 * @param array $columns
	 * @return static
	 */
	function required($columns) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_REQUIRE, true);
		return $this;
	}

	/**
	 * Makes columns not required. Defaults to all columns.
	 * @param array $columns
	 * @return static
	 */
	function optional($columns) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_REQUIRE, false);
		return $this;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return static
	 */
	function disable($columns) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_DISABLE, false);
		return $this;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return static
	 */
	function enable($columns) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_DISABLE, true);
		return $this;
	}

	/**
	 * Sets if the form should submit multiple items.
	 * @param boolean $multiple
	 * @return static
	 */
	function setMultiple($multiple) {
		$this->multiple = $multiple;
		return $this;
	}

	/**
	 * Get if the form can submit multiple items.
	 * @return boolean
	 */
	function getMultiple() {
		return $this->multiple;
	}

	/**
	 * Sets a function that provides fine-grain control over the name, value, and possible values
	 * (for applicable inputs) of the HTML form input. The values passed to function are: (1) column name,
	 * (2) the options for the column, (3) the form input name, (4) the form value, and
	 * (5) an array of possible values; use references to modify the values.
	 * @return static
	 */
	function setInputHook($function) {
		$this->inputHook = $function;
		return $this;
	}

	/**
	 * Get the input hook.
	 * @return callable
	 */
	function getInputHook() {
		return $this->inputHook;
	}

	/**
	 * Render the form.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	function render($input = null, Component $included = null) {
		if ($included instanceof Modal) {
			$included->useButtons(['btn-primary wd-btn-submit' => 'Submit']);
		} else {
			$this->useBeforeClosingTag('<button type="button" class="btn btn-default">Submit</button>');
		}
		if ($included instanceof Form) {
			$this->setHtmlTag('div');
		} else {
			$this->setHtmlTag('form');
		}
		//Get an ID for the form.
		static::$countForm++;
		$count = static::$countForm;
		$this->count = $count;
		$this->useAfterOpeningTag("<input type='hidden' name='$count' value='" . $this->tableName . "'/>");
		if ($this->multiple) {
			$this->useBeforeClosingTag("<button type='button' class='btn btn-default wd-multiple'>Add Another</button>");
		}
		return parent::render($input, $included);
	}

	/**
	 * Link a column value with a component. This will prevent the default display component from
	 * being rendered. If this is undesirable, then you can do:
	 * <code>
	 * $a->link('column', $a->getDisplayComponent());
	 * </code>
	 * @param string $column
	 * @param Component $component
	 * @param string $manyColumn When you want to use an ID column in another table that points to this
	 * table and there are multiple such columns, you must specify what column to actually use.
	 * @return static
	 */
	function link($column, Component $component, $manyColumn = null) {
		if ($component instanceof Select) {
			$this->selectComponent[$column] = $component;
		} else {
			parent::link($column, $component, $manyColumn);
		}
		return $this;
	}

	/**
	 * Renders the column.
	 * @param string $column
	 * @param mixed $value
	 * @return string
	 */
	function renderColumn($column, $value) {
		$options = $this->columns[$column];
		$selectComponent = isset($this->selectComponent[$column]) ? $this->selectComponent[$column] : null;
		$name = $this->count . "-" . $column;
		$label = $selectComponent ? $selectComponent->getTitle() : $options[self::OPT_LABEL];
		$labelHtml = $this->label->useHtml("for='$name'")->render($label, $this);
		$disabled = $options[self::OPT_DISABLE] ? 'disabled=""' : '';
		$required = $options[self::OPT_REQUIRE] && $options[self::OPT_VISIBLE] ? 'required=""' : '';
		$type = $options[self::OPT_HTML_TYPE];
		$class = $options[self::OPT_HTML_CLASS];
		$extra = $options[self::OPT_HTML_EXTRA];
		$possibleValues = null;
		if (is_array($options[self::OPT_EXTRA])) {
			$possibleValues = $options[self::OPT_EXTRA];
		}
		if ($this->inputHook) {
			$function = $this->inputHook;
			$function($column, $options, $name, $value, $possibleValues);
		}
		$attributes = "name='$name' $disabled $required $extra";
		$classes = "form-control $class";
		$this->display->setAfterOpeningTag($labelHtml);
		if ($selectComponent !== null) {
			$this->display->setDisplayComponent($selectComponent->useHtml($attributes)->useCssClass($classes));
		} else {
			switch ($type) {
				case self::HTML_NUMBER:
					$component = new Component('input', $classes, "type='number' value='$value' $attributes");
					$this->display->setDisplayComponent($component->setInput('')->setSelfClosing(true));
					break;
				case self::HTML_TEXT:
					$component = new Component('input', $classes, "type='text' value='$value' $attributes");
					$this->display->setDisplayComponent($component->setInput('')->setSelfClosing(true));
					break;
				case self::HTML_TEXTAREA:
					$this->display->setDisplayComponent(new Component('textarea', $classes, $attributes));
					break;
				case self::HTML_BOOLEAN:
					$value = $value ? 'Yes' : 'No';
					$possibleValues = ['No', 'Yes'];
				case self::HTML_SELECT:
					$component = new Select($possibleValues, $classes, $attributes);
					$this->display->setDisplayComponent($component);
			}
		}
		return $this->display->render($value, $this);
	}

}
