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
	 * If used as the value for OPT_LABEL_ACCESS, then it indicates that the Form class should
	 * label the column instead of the Data class.
	 */
	const FORM_LABEL = 0;
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
	 * An input with type 'text'
	 */
	const HTML_PASSWORD = 'password';
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
	protected static $formCount = 0;
	/**
	 * Indicates if the form can handle multiple items.
	 * @var boolean
	 */
	protected $multiple = false;
	/**
	 * The form ID
	 * @var int
	 */
	protected $id;
	/**
	 * A function to give control to change form inputs.
	 * @var callable
	 */
	protected $inputHook;
	/**
	 * Linked select components.
	 * @var array
	 */
	protected $formComponent = [];
	/**
	 * Indicate if the form should use JavaScript to submit.
	 * @var boolean
	 */
	protected $javascript = true;
	/**
	 * Indicate if the form should use AJAX to submit.
	 * @var boolean
	 */
	protected $ajax = false;
	/**
	 * Indicate if the form deletes the object.
	 * @var boolean
	 */
	protected $deleteForm = false;

	/**
	 * Construct a Form.
	 * @param string $tableName
	 * @param string $class
	 * @param string $html
	 * @param array $input
	 */
	function __construct($tableName, $class = null, $html = null, $input = null) {
		$display = new Wrapper($this->input, 'div', "form-group", null);
		parent::__construct($tableName, $display, 'form', "wd-form $class", "role='form' $html", $input);
		$display->addExtraComponent($this->label, false);
		$this->useLabel = false;
	}

	protected function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options[self::OPT_LABEL_ACCESS] = self::FORM_LABEL;
		$options[self::OPT_DISABLE] = false;
		$options[self::OPT_REQUIRE] = false;
		$options[self::OPT_HTML_TYPE] = null;
		$options[self::OPT_HTML_CLASS] = null;
		$options[self::OPT_HTML_EXTRA] = null;
		return $options;
	}

	protected function addColumn(ColumnMap $column, array &$options) {
		parent::addColumn($column, $options);
		$name = $column->getName();
		if ($name === 'id' || substr($name, -3) === '_id') {
			$options[self::OPT_LABEL_ACCESS] = false;
		}
		if ($name === 'created_at' || $name === 'updated_at') {
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
	 * Makes the form a "delete" form. This is just a form that has its inputs disabled by default and
	 * has a hidden input with the name "$formId:::delete". When paired with server automate, this
	 * will delete the input. Note that if $delete is true, then automatically disables the inputs.
	 * If $delete is false, then automatically enables the inputs.
	 * @param boolean $delete
	 * @return static
	 */
	function setDeleteForm($delete) {
		$this->deleteForm = $delete;
		if ($delete) {
			$this->disable();
		} else {
			$this->enable();
		}
		return $this;
	}

	/**
	 * Get whether this form deletes the object.
	 * @return boolean
	 */
	function getDeleteForm() {
		return $this->deleteForm;
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
	 * Get the ID of this form. This will return null if the form is not being rendered.
	 * @return int
	 */
	function getId() {
		return $this->id;
	}

	/**
	 * Show labels for the given columns. Defaults to all.
	 * @param array $columns
	 * @return static
	 */
	function allowLabels($columns = null) {
		$columns = is_array($columns) ? $columns : func_get_args();
		$this->setOptions($columns, self::OPT_LABEL_ACCESS, self::FORM_LABEL);
		if (count($this->linked) !== 0) {
			$this->setOptions(array_keys($this->linked), self::OPT_LABEL_ACCESS, true);
		}
		return $this;
	}

	/**
	 * Removes linked components for a given column.
	 * @param string $column
	 * @return static
	 */
	function unlink($column) {
		if ($this->columns[$column][self::OPT_LABEL_ACCESS]) {
			$this->columns[$column][self::OPT_LABEL_ACCESS] = self::FORM_LABEL;
		}
		return parent::unlink($column);
	}

	/**
	 * Render the form.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	function render($input = null, Component $included = null) {
		//Get an ID for the form.
		static::$formCount++;
		$id = static::$formCount;
		$this->id = $id;
		$this->useAfterOpeningTag("<input type='hidden' class='wd-form-id' name='$id' value='" .
				$this->tableName . "'/>");
		if ($this->deleteForm) {
			$this->useAfterOpeningTag("<input type='hidden' class='wd-form-delete' name='$id:::delete'/>");
		}
		//Add a button to create another entry.
		if ($this->multiple) {
			$this->useBeforeClosingTag("<button type='button' class='btn btn-default wd-multiple'>"
					. "Add Another</button>");
		}

		$component = $included;
		$modal = false;
		$form = false;
		while ($component !== null) {
			//If this was included by a Modal, we need to change where the button is placed.
			if ($component instanceof Modal) {
				$component->useButtons(['btn-primary wd-modal-submit' => 'Submit']);
				$modal = true;
				break;
			}
			//If this was included from another Form, we need to specify how this relates to the other form.
			if ($component instanceof Form) {
				$this->setHtmlTag('div');
				$name = "$id:with:" . $component->getId();
				$value = $component->getOption($component->getRenderedColumn(), self::OPT_PROPEL_COLUMN);
				if ($value !== null) {
					$this->useAfterOpeningTag("<input class='wd-form-relation' type='hidden' name='$name' "
							. "value='$value'/>");
				}
				$form = true;
				break;
			}
			$component = $component->getRenderedBy();
		}
		//If not included by a modal or a form, then add a submit button.
		if (!$modal && !$form) {
			$class = '';
			if ($this->javascript) {
				if ($this->ajax) {
					$class = 'wd-form-ajax';
				} else {
					$class = 'wd-form-submit';
				}
				$this->useBeforeClosingTag("<button type='button' class='btn btn-default $class'>Submit</button>");
			} else {
				$this->useBeforeClosingTag("<button type='submit' class='btn btn-default'>Submit</button>");
			}
		}
		//If not included by a form, then just set the HTML tag.
		if (!$form) {
			$this->setHtmlTag('form');
		} else {
			$this->useCssClass('wd-subform');
		}

		$result = parent::render($input, $included);

		$this->id = null;

		return $result;
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
	 * @param boolean $autoPropel If false, then will not try to automatically figure out
	 * the related Propel method to call on the object.
	 * @return static
	 */
	function link($column, Component $component, $manyColumn = null, $autoPropel = true) {
		parent::link($column, $component, $manyColumn, $autoPropel);
		if ($component instanceof Select) {
			$this->formComponent[$column] = array_pop($this->linked[$column]);
		} else if ($this->columns[$column][self::OPT_LABEL_ACCESS] !== false) {
			$this->columns[$column][self::OPT_LABEL_ACCESS] = true;
		}
		return $this;
	}

	/**
	 * Renders the column.
	 * @param string $column
	 * @param mixed $value
	 * @return string
	 */
	protected function renderColumn($column, $value) {
		$options = $this->columns[$column];
		$selectComponent = isset($this->formComponent[$column]) ? $this->formComponent[$column] : null;
		$name = $this->id . ":" . $column;
		$label = $selectComponent ? $selectComponent->getTitle() : $options[self::OPT_LABEL];
		if ($options[self::OPT_LABEL_ACCESS] !== false && $label !== null) {
			$this->label->useHtml("for='$name'")->setHtmlTag('label');
		} else {
			$this->label->setHtmlTag(null)->setInput('');
		}
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
		if ($selectComponent !== null) {
			$this->display->setDisplayComponent($selectComponent->useHtml($attributes)->useCssClass($class));
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
				case self::HTML_PASSWORD:
					$component = new Component('input', $classes, "type='password' value='$value' $attributes");
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

	/**
	 * If true, then the form will use JavaScript to submit the form.
	 * @param boolean $javascript
	 * @return static
	 */
	function setUseJavascript($javascript) {
		$this->javascript = $javascript;
		return $this;
	}

	/**
	 * Indicate if the form will use JavaScript to submit.
	 * @return boolean
	 */
	function getUseJavascript() {
		return $this->javascript;
	}

	/**
	 * Indicate if the form will use AJAX.
	 * @return boolean
	 */
//	function getUseAjax() {
//		return $this->ajax;
//	}

	/**
	 * If true, then the form will use AJAX to submit the form. Note that this also sets if the form
	 * uses JavaScript.
	 * @param boolean $ajax
	 * @return static
	 */
//	function setUseAjax($ajax) {
//		$this->ajax = $ajax;
//		$this->javascript = $ajax;
//		return $this;
//	}
}
