<?php

namespace WebDreamt\Component\Wrapper\Data;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\ColumnMap;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;
use WebDreamt\Component\Wrapper\Data;
use WebDreamt\Component\Wrapper\Group\Select;
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
	protected static $count = 0;
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
	protected $inputHook = null;
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
		parent::__construct($display, $tableName, 'form', null, 'role="form"');
		$this->setLabelComponent(new Component('label'), null);
	}

	protected function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options[self::OPT_DISABLE] = false;
		$options[self::OPT_REQUIRE] = false;
		$options[self::OPT_HTML_TYPE] = '';
		$options[self::OPT_HTML_CLASS] = '';
		$options[self::OPT_HTML_EXTRA] = '';
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
	 * @return self
	 */
	function required(array $columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_REQUIRE, true);
		return $this;
	}

	/**
	 * Makes columns not required. Defaults to all columns.
	 * @param array $columns
	 * @return self
	 */
	function optional(array $columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_REQUIRE, false);
		return $this;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function disable(array $columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_DISABLE, false);
		return $this;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function enable(array $columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_DISABLE, true);
		return $this;
	}

	/**
	 * Sets if the form should submit multiple items.
	 * @param boolean $multiple
	 * @return self
	 */
	function setMultiple($multiple = false) {
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
	 * (5) an array of possible values; the last three are passed by reference and so are modifiable.
	 * @param callable $function
	 * @return self
	 */
	function setInputHook($function = null) {
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
	 */
	function render($input = null, Component $included = null) {
		if ($included instanceof Modal) {
			$this->setHtmlTag('div');
			$included->useButtons(['btn-primary wd-btn-submit' => 'Submit']);
		} else {
			$this->setHtmlTag('form');
		}
		//Get an ID for the form.
		static::$count++;
		$count = static::$count;
		$this->count = $count;
		$this->useAfterOpeningTag("<input type='hidden' name='$count' value='" . $this->tableName . "'/>");
		if ($this->multiple) {
			$this->useBeforeClosingTag("<button type='button' class='btn btn-default'>Add Another</button>");
		}
		parent::render($input, $included);
	}

	/**
	 * Link a column value with a component. This will prevent the default display component from
	 * being rendered. If this is undesirable, then you can do:
	 * <code>
	 * $a->link('col', $b);
	 * $b->addExtraComponent($a->getDisplay());
	 * </code>
	 * @param string $column
	 * @param Component $component
	 * @param boolean $propelInput If this is true, then link() configures the render function to
	 * retrieve related data from a Propel object to give as input to $component based on the class
	 * of $component.
	 * @param string $manyColumn When you want to use an ID column in another table that points to this
	 * table and there are multiple such columns, you must specify what column to actually use.
	 * @return self
	 */
	function link($column, Component $component, $propelInput = true, $manyColumn = null) {
		if ($component instanceof Select) {
			$this->selectComponent[$column] = $component;
		} else {
			parent::link($column, $component, $propelInput, $manyColumn);
		}
		return $this;
	}

	/**
	 * Renders the column.
	 * @param string $column
	 * @param mixed $value
	 */
	function renderColumn($column, $value) {
		$options = $this->columns[$column];
		$selectComponent = isset($this->selectComponent[$column]) ? $this->selectComponent[$column] : null;
		$name = $this->count . "-" . $column;
		$label = $selectComponent ? $selectComponent->getHeader() : $options[self::OPT_LABEL];
		ob_start();
		$this->label->useHtml("for='$name'")->render($label, $this);
		$labelHtml = ob_get_clean();
		$hidden = $options[self::OPT_VISIBLE] ? '' : 'style="display:none"';
		$disabled = $options[self::OPT_DISABLE] ? 'disabled=""' : '';
		$required .= $options[self::OPT_REQUIRE] && $options[self::OPT_VISIBLE] ? 'required=""' : '';
		$type = $options[self::OPT_HTML_TYPE];
		$class = $options[self::OPT_HTML_CLASS];
		$extra = $options[self::OPT_HTML_EXTRA];
		$possibleValues = '';
		if ($this->inputHook) {
			$function = $this->inputHook;
			$function($column, $options, &$name, &$value, &$possibleValues);
		}
		$attributes = "name='$name' $disabled $required $extra";
		$classes = "form-control $class";
		$cssPrefix = $this->dataClass ? "class='" . $this->dataClass . "-$column'" : '';
		$this->display->useHtml("$hidden $cssPrefix")->setAfterOpeningTag($labelHtml);
		if (isset($selectComponent)) {
			$this->display->setDisplayComponent($selectComponent->useHtml($attributes));
		} else {
			switch ($type) {
				case self::HTML_NUMBER:
					$component = new Component('input', $classes, "type='number' value='$value' $attributes");
					$this->display->setDisplayComponent($component->setInput(''));
					break;
				case self::HTML_TEXT:
					$component = new Component('input', $classes, "value='$value' $attributes");
					$this->display->setDisplayComponent($component->setInput(''));
					break;
				case self::HTML_TEXTAREA:
					$this->display->setDisplayComponent(new Component('textarea', $classes, $attributes));
					break;
				case self::HTML_BOOLEAN:
					$value = $value ? 'Yes' : 'No';
					$possibleValues = ['No', 'Yes'];
				case self::HTML_SELECT:
					$component = new Select('form-control', $attributes);
					if (!$possibleValues) {
						$possibleValues = $options[self::OPT_EXTRA];
					}
					$component->setSelected($value)->setInput($possibleValues);
					$this->display->setDisplayComponent($component);
			}
		}
		$this->display->render($value, $this);
	}

}
