<?php

namespace WebDreamt\Component\Wrapper;

use DateTime;
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use Exception;
use WebDreamt\Box;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

/**
 * A class used as a base to render data from the database.
 */
class Data extends Wrapper {

	/**
	 * The accessibility of the column. A value of false means that the renderer gives no
	 * indication that the column exists. A value of true means normal behavior.
	 */
	const OPT_ACCESS = 'access';
	/**
	 * The visibility of the column. A value of true means "show" and a value of false means
	 * "hidden". Note that this is different from OPT_ACCESS because this indicates that some
	 * information may be sent to the client, which can be used by the browser or JavaScript to
	 * implement additional functionality.
	 */
	const OPT_VISIBLE = 'visible';
	/**
	 * The default value for the column.
	 */
	const OPT_DEFAULT = 'default';
	/**
	 * The Propel type of the column.
	 */
	const OPT_TYPE = 'type';
	/**
	 * Extra information about the type such as the set of possible values for ENUM and
	 * length for others.
	 */
	const OPT_EXTRA = 'extra';
	/**
	 * The label for the column displayed in an easily human-readable form.
	 */
	const OPT_LABEL = 'label';
	/**
	 * The method to call on the propel object to access a different object. This only applies if input is
	 * given as a Propel object.
	 */
	const OPT_PROPEL_OBJECT = 'object';
	/**
	 * A string to output when a value is retrieved from the input and it in null.
	 */
	const OPT_NULL_VALUE = 'null';

	/**
	 * An array where the keys are column names and the values are the options for each column.
	 * @var array
	 */
	protected $columns = [];
	/**
	 * The column components to display for a certain column.
	 * @var array
	 */
	protected $columnComponents = [];
	/**
	 * An array where the keys are column names and the values are arrays with values that are
	 * Components.
	 * @var array
	 */
	protected $linked = [];
	/**
	 * The name of the table.
	 * @var string
	 */
	protected $tableName;
	/**
	 * A component to show labels in.
	 * @var Component
	 */
	protected $label;
	/**
	 * Indicates if the labels should output alongside the data.
	 * @var boolean
	 */
	protected $showLabel = false;
	/**
	 * The css prefix used to identify the column label.
	 * @var string
	 */
	protected $labelClass;
	/**
	 * The css prefix used to identify column data.
	 * @var string
	 */
	protected $dataClass;
	/**
	 * The time format.
	 * @var string
	 */
	protected $timeFormat;
	/**
	 * The date time format.
	 * @var string
	 */
	protected $dateTimeFormat;
	/**
	 * The date format.
	 * @var string
	 */
	protected $dateFormat;
	/**
	 * The default time format for the class: g:i a
	 * @var string
	 */
	static public $DefaultTimeFormat = "g:i a";
	/**
	 * The default date time format for the class: g:i a, m/d/y
	 * @var string
	 */
	static public $DefaultDateTimeFormat = "g:i a, m/d/y";
	/**
	 * The default date format for the class: m/d/y
	 * @var string
	 */
	static public $DefaultDateFormat = 'm/d/y';

	/**
	 * Construct a component that represents a row from a table in the database.
	 * @param string $tableName
	 * @param Component $display
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct($tableName, Component $display = null, $htmlTag = 'div', $class = null,
			$html = null, $input = null) {
		parent::__construct($display, $htmlTag, $class, $html, $input);
		$table = Propel::getDatabaseMap()->getTable($tableName);
		$this->tableName = $tableName;
		$this->title = static::beautify($tableName);
		$this->label = new Component();
		foreach ($table->getColumns() as $column) {
			$name = $column->getName();
			$this->columns[$name] = $this->getDefaultOptions();
			$this->addColumn($column, $this->columns[$name]);
		}
	}

	/**
	 * Change the default column options. Note that this does not set the PROPEL_OBJECT property.
	 * This is instead set by addExtraComponent() and link().
	 * @param ColumnMap $column
	 * @param array $options
	 */
	protected function addColumn(ColumnMap $column, array &$options) {
		$options[self::OPT_DEFAULT] = $column->getDefaultValue();
		$options[self::OPT_TYPE] = $column->getType();
		//Set up enum.
		if ($options[self::OPT_TYPE] === PropelTypes::ENUM) {
			$options[self::OPT_EXTRA] = $column->getValueSet();
		} else {
			$options[self::OPT_EXTRA] = $column->getSize();
		}
		//Set label.
		$options[self::OPT_LABEL] = static::beautify($column->getName());
	}

	/**
	 * Get the default options.
	 * @return array
	 */
	protected function getDefaultOptions() {
		return [
			self::OPT_ACCESS => true,
			self::OPT_VISIBLE => true,
			self::OPT_DEFAULT => null,
			self::OPT_TYPE => null,
			self::OPT_EXTRA => null,
			self::OPT_PROPEL_OBJECT => null,
			self::OPT_LABEL => null,
			self::OPT_NULL_VALUE => null
		];
	}

	/**
	 * Add an extra column that will be rendered by default last.
	 * Only the label option is automatically set since information cannot be retrieved about
	 * the column from the database.
	 * @param string $column
	 * @return static
	 */
	function addExtraColumn($column) {
		$this->columns[$column] = $this->getDefaultOptions();
		$this->columns[$column][self::OPT_LABEL] = static::beautify($column);
		return $this;
	}

	/**
	 * Get the table name for the component.
	 * @return string
	 */
	function getTableName() {
		return $this->tableName;
	}

	/**
	 * Get the column names in order.
	 * @return array
	 */
	function getColumnNames() {
		return array_keys($this->columns);
	}

	/**
	 * Get an array where each key is a column name and each value is an array. In the subarray, each
	 * key is an option name (specified by the OPT constants) and each value is the value set for
	 * the option.
	 * @return array
	 */
	function getColumnOptions() {
		return $this->columns;
	}

	/**
	 * Set a CSS class prefix that will be used to identify column data. If null, then no CSS class
	 * will be used. For example, if 'wd' is used, then for an 'customer_id' column, the display component
	 * will have class 'wd-customer_id'.
	 * @param string $dataClass
	 * @return static
	 */
	function setDataClass($dataClass) {
		$this->dataClass = $dataClass;
		return $this;
	}

	/**
	 * Get the CSS class prefix for the data.
	 * @return string
	 */
	function getDataClass() {
		return $this->dataClass;
	}

	/**
	 * Set a CSS class prefix that will be used to identify the label. If null, then no CSS class
	 * will be used. For example, if 'wd' is used, then for an 'customer_id' column, the label component
	 * will have class 'wd-customer_id'.
	 * @param string $labelClass
	 * @return static
	 */
	function setLabelClass($labelClass) {
		$this->labelClass = $labelClass;
		return $this;
	}

	/**
	 * Get the CSS class prefix for the label.
	 * @return string
	 */
	function getLabelClass() {
		return $this->labelClass;
	}

	/**
	 * Set the time format used by the component. Default is 'g:i a'.
	 * @param string $format
	 * @return static
	 */
	function setTimeFormat($format) {
		$this->timeFormat = $format;
		return $this;
	}

	/**
	 * Get the time format.
	 * @return string
	 */
	function getTimeFormat() {
		return $this->timeFormat;
	}

	/**
	 * Set the date time format used by the component. Default is 'g:i a, m/d/y'.
	 * @param string $format
	 * @return static
	 */
	function setDateTimeFormat($format) {
		$this->dateTimeFormat = $format;
		return $this;
	}

	/**
	 * Get the date time format.
	 * @return string
	 */
	function getDateTimeFormat() {
		return $this->dateTimeFormat;
	}

	/**
	 * Set the date format used by the component. Default is 'm/d/y'.
	 * @param string $format
	 * @return static
	 */
	function setDateFormat($format) {
		$this->dateFormat = $format;
		return $this;
	}

	/**
	 * Get the date format.
	 * @return string
	 */
	function getDateFormat() {
		return $this->dateFormat;
	}

	/**
	 * Set if labels should be shown. Defaults to false.
	 * @param boolean $show
	 * @return static
	 */
	function setLabelable($show) {
		$this->showLabel = $show;
		return $this;
	}

	/**
	 * Get if labels should be shown.
	 * @return boolean
	 */
	function getLabelable() {
		return $this->showLabel;
	}

	/**
	 * Set a component to show labels in.
	 * @param Component $label
	 * @return static
	 */
	function setLabelComponent(Component $label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * Get the label component.
	 * @return string
	 */
	function getLabelComponent() {
		return $this->label;
	}

	/**
	 * Link a column value with a component. This will prevent the default display component from
	 * being rendered. If this is undesirable, then you can do before/after:
	 * <code>
	 * $a->link('column_name', $a->getDisplayComponent());
	 * </code>
	 * Multiple components can be linked to one column.
	 * @param string $column
	 * @param Component $component
	 * @param string $manyColumn When you want to use an ID column in another table that points to this
	 * table and there are multiple such columns, you must specify what column to actually use.
	 * @return static
	 */
	function link($column, Component $component, $manyColumn = null) {
		if (!isset($this->columns[$column])) {
			throw new Exception("Cannot link column $column since it doesn't exist.");
		}
		if (!isset($this->linked[$column])) {
			$this->linked[$column] = [];
		}
		$this->linked[$column][] = $component;

		//Set the Propel method that needs to be called to get input for the linked component.
		$propel = null;
		while ($component instanceof Wrapper) {
			if ($component instanceof Data) {
				$table = Propel::getDatabaseMap()->getTable($this->getTableName());
				$columnMap = $table->getColumn($column);
				$propel = 'get' . $columnMap->getRelatedTable()->getPhpName();
				if (!method_exists($table->getPhpName(), $propel)) {
					$propel .= 'RelatedBy' . $columnMap->getPhpName();
				}
				break;
			} else if ($component instanceof Group) {
				$display = $component->getDisplayComponent();
				if ($display instanceof Data) {
					$table = Propel::getDatabaseMap()->getTable($display->getTableName());
					$propel = 'get' . Box::now()->pluralize($table->getPhpName());
					if ($manyColumn) {
						$propel .= 'RelatedBy' . $table->getColumn($manyColumn)->getPhpName();
					}
				}
				break;
			}
			$component = $component->getDisplayComponent();
		}
		$this->columns[$column][self::OPT_PROPEL_OBJECT] = $propel;
		return $this;
	}

	/**
	 * Unlink all linked components for a given column.
	 * @param string $column
	 * @return static
	 */
	function unlink($column) {
		if (isset($this->linked[$column])) {
			unset($this->linked[$column]);
		}
		return $this;
	}

	/**
	 * Get the linked components as an array with keys of column names and values of components.
	 * @return array
	 */
	function getLinkedComponents() {
		return $this->linked;
	}

	/**
	 * Make columns visible. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return static
	 */
	function show($columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_VISIBLE, true);
		return $this;
	}

	/**
	 * Hides columns. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return static
	 */
	function hide($columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_VISIBLE, false);
		return $this;
	}

	/**
	 * Enable the columns to be sent to the client. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return static
	 */
	function allow($columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_ACCESS, true);
		return $this;
	}

	/**
	 * Disable the columns from being sent to the client. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return static
	 */
	function deny($columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_ACCESS, false);
		return $this;
	}

	/**
	 * Set the default values, which are used when a key cannot be found in the input.
	 * Can use a Propel object to set the default values.
	 * @param array|ActiveRecordInterface $columns
	 * @return static
	 */
	function setDefaultValues($columns) {
		if ($columns instanceof ActiveRecordInterface) {
			foreach ($this->columns as $column => $options) {
				$value = $columns->getByName($column, TableMap::TYPE_FIELDNAME);
				$this->columns[$column][self::OPT_DEFAULT] = $value;
			}
		} else {
			$this->mergeOptions($columns, self::OPT_DEFAULT);
		}
		return $this;
	}

	/**
	 * Set the labels. Can set a label to null, in which case it won't be shown.
	 * @param array $columns
	 * @return static
	 */
	function setLabels(array $columns) {
		$this->mergeOptions($columns, self::OPT_LABEL);
		return $this;
	}

	/**
	 * Set a value to be returned if the value retrieved from the input is null.
	 * @param array $columns
	 * @return static
	 */
	function setNullValues(array $columns) {
		$this->mergeOptions($columns, self::OPT_NULL_VALUE);
		return $this;
	}

	/**
	 * Reorder the columns of the component. The numerical indexes of the passed array will be used as
	 * the positions of the columns in the new ordering. Any gaps in the new ordering will be
	 * filled in with columns in the old ordering that don't have a newly specified position. These
	 * columns will be used in the order that the columns appear in the old ordering. Thus,
	 * if given ['a', 'b', 'c', 'd', 'e'] as the initial columns and passed [2 => 'a', 0 => 'e', 4 => 'd']
	 * as the parameter, we would have ['e', 'b', 'a', 'c', 'd'].
	 * @param array $columns The key is the index of the column in the new list of columns. The value
	 * is the column name.
	 * @return static
	 */
	function reorder(array $columns) {
		$count = count($this->columns);

		$holder = [];
		foreach ($columns as $column) {
			if (!isset($this->columns[$column])) {
				throw new Exception("Unknown column $column");
			}
			$holder[$column] = $this->columns[$column];
			unset($this->columns[$column]);
		}

		$newColumns = [];
		for ($i = 0; $i < $count; $i++) {
			if (isset($columns[$i]) && isset($holder[$columns[$i]])) {
				$newColumns[$columns[$i]] = $holder[$columns[$i]];
			} else if ($this->columns) {
				reset($this->columns);
				$newColumns[key($this->columns)] = array_shift($this->columns);
			}
		}
		//Make sure everything is kept (such as in the case of an invalid index.
		if ($this->columns) {
			$newColumns = array_merge($newColumns, $this->columns);
		}
		$this->columns = $newColumns;
		return $this;
	}

	/**
	 * Specify a different name to use for a column. This will also change the label for the
	 * column unless $changeLabel is set to false. All future reference to this column should use the
	 * new column name.
	 * @param array $oldToNewColumns The key should be the old column name and the value should be the
	 * new column name.
	 * @param boolean $changeLabel
	 * @return static
	 */
	function alias(array $oldToNewColumns, $changeLabel = true) {
		$newColumns = [];
		foreach ($this->columns as $oldColumn => $option) {
			if (isset($oldToNewColumns[$oldColumn])) {
				$newColumn = $oldToNewColumns[$oldColumn];
				$newColumns[$newColumn] = $option;
				if ($changeLabel) {
					$option[self::OPT_LABEL] = static::beautify($newColumn);
				}
			} else {
				$newColumns[$oldColumn] = $option;
			}
		}

		$newLinked = [];
		foreach ($this->linked as $oldColumn => $linked) {
			if (isset($oldToNewColumns[$oldColumn])) {
				$newLinked[$oldToNewColumns[$oldColumn]] = $linked;
			} else {
				$newLinked[$oldColumn] = $linked;
			}
		}

		$this->linked = $newLinked;
		$this->columns = $newColumns;
		return $this;
	}

	/**
	 * Get the value for an option and column.
	 * @param string $column
	 * @param string $option See OPT constants.
	 * @return mixed
	 */
	function getOption($column, $option) {
		return $this->columns[$column][$option];
	}

	/**
	 * Set the value for an option and column.
	 * @param string $column
	 * @param string $option See OPT constants.
	 * @param mixed $value
	 * @return static
	 */
	function setOption($column, $option, $value) {
		$this->columns[$column][$option] = $value;
		return $this;
	}

	/**
	 * Get the option values for the columns. If $columns is null or an empty array, then will return an
	 * array of option values for all columns.
	 * @param array $columns
	 * @param string $option An option as given by the OPT constants in the Data or child class.
	 * @return array|string
	 */
	function getOptions($columns, $option) {
		$ret = [];
		foreach ($this->columns as $column => $options) {
			if (count($columns) === 0 || in_array($column, $columns)) {
				$ret[$column] = $options[$option];
			}
		}
		return $ret;
	}

	/**
	 * Applies the $options for each $columns. Also, see mergeOptions() if you want to put the column
	 * names and option values in the same array.
	 * @param array $columns An array where the column names are values.
	 * @param array|string $options If an array, then keys are assumed to be option names and values are
	 * option values. If a string, then assumes $options is an option name.
	 * @param string $value If $options is a string instead of an array, then $value is
	 * used as the value for the option specified by the string $options.
	 * @return static
	 */
	function setOptions($columns, $options, $value = null) {
		//Coerce $columns into an array.
		if (count($columns) === 0) {
			$columns = array_keys($this->columns);
		} else {
			foreach ($columns as $column) {
				if (!isset($this->columns[$column])) {
					throw new Exception('Specified an invalid column when setting options.');
				}
			}
		}

		if (!is_array($options)) {
			foreach ($columns as $column) {
				$this->columns[$column][$options] = $value;
			}
		} else {
			foreach ($columns as $column) {
				$this->columns[$column] = array_merge($this->columns[$column], $options);
			}
		}
		return $this;
	}

	/**
	 * Merges in the values contained in the $columns array. If you don't want to put the values
	 * in $columns, see setOptions().
	 * @param array $columns An array where each key is a column name and each value is option
	 * value for the option specified by $option.
	 * @param string $option See OPT constants.
	 * @return static
	 */
	function mergeOptions(array $columns, $option) {
		if (count(array_intersect_key($columns, $this->columns)) !== count($columns)) {
			throw new Exception('Specified an invalid column when setting options.');
		}
		foreach ($columns as $name => $value) {
			$this->columns[$name][$option] = $value;
		}
		return $this;
	}

	/**
	 * Render the child component. Child classes may want to override this method along with
	 * renderColumn().
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 * @return string
	 */
	protected function renderSpecial($input = null, $included = null) {
		$output = null;
		foreach ($this->columns as $column => $options) {
			if ($options[self::OPT_ACCESS]) {
				//Set the input of the label regardless as it might be used somewhere in the display
				//component (such as via addExtraComponent()).
				$this->label->setInput($options[self::OPT_LABEL]);
				if ($this->showLabel && $options[self::OPT_LABEL] !== null) {
					if (!$options[self::OPT_VISIBLE]) {
						$this->label->useHtml('style="display:none"');
					}
					if ($this->labelClass !== null) {
						$this->label->useClass($this->labelClass . "-$column");
					}
					$output .= $this->label->render(null, $this);
				}
				$value = $this->getValueFromInput($column, $input);
				$linked = $this->renderLinkedComponents($column, $value);
				if ($linked === null) {
					if (!$options[self::OPT_VISIBLE]) {
						$this->display->useHtml('style="display:none"');
					}
					if ($this->dataClass) {
						$this->display->useCssClass($this->dataClass . "-$column");
					}
					$output .= $this->renderColumn($column, $value, $included);
				} else {
					$output .= $linked;
				}
			}
		}
		//Reset the label input to null to remove side-effects.
		$this->label->setInput(null);
		return $output;
	}

	/**
	 * Render a column.
	 * @param string $column
	 * @param mixed $value
	 * @param Component $included
	 * @return string
	 */
	protected function renderColumn($column, $value, Component $included = null) {
		return $this->display->render($value, $included);
	}

	/**
	 * Render any linked components for the given column. Return null if there is no component
	 * for the given column.
	 * @param string $column
	 * @param array|ActiveRecordInterface $input The input to be given to the component.
	 * @return string Null if nothing was rendered; otherwise the output.
	 */
	protected function renderLinkedComponents($column, $input = null) {
		$result = null;
		if (isset($this->linked[$column])) {
			foreach ($this->linked[$column] as $component) {
				if (!$this->columns[$column][self::OPT_VISIBLE]) {
					$component->useHtml('style="display:none"');
				}
				if ($this->dataClass) {
					$component->useCssClass($this->dataClass . "-$column");
				}
				$result .= $component->render($input, $this);
			}
		}
		return $result;
	}

	/**
	 * Output the HTML of the labels. This takes into account OPT_ACCESS and OPT_VISIBLE, but not
	 * if labels are set to be shown.
	 * @return string
	 */
	function renderLabels() {
		$output = null;
		foreach ($this->columns as $column => $options) {
			if ($options[self::OPT_ACCESS]) {
				if (!$options[self::OPT_VISIBLE]) {
					$this->label->useHtml('style="display:none"');
				}
				if ($this->labelClass !== null) {
					$this->label->useCssClass($this->labelClass . "-$column");
				}
				$output .= $this->label->render($options[self::OPT_LABEL], $this);
			}
		}
		return $output;
	}

	/**
	 * Get a value from input. Note that if the $value is a DateTime object, this converts it into a string
	 * in the format specifed by either the setDateTimeFormat(), setTimeFormat(), or setDateFormat()
	 * methods depending on the type of the column in the database.
	 * @param string $key The key to use to try to get the value from the input.
	 * @param mixed $input
	 * @return string
	 */
	protected function getValueFromInput($key, $input) {
		if ($key === null) {
			return $input;
		}
		if (is_array($input) && array_key_exists($key, $input)) {
			$value = $input[$key];
			if ($value === null) {
				return $this->columns[$key][self::OPT_NULL_VALUE];
			}
			return $value;
		} else if ($input instanceof ActiveRecordInterface) {
			$object = $this->columns[$key][self::OPT_PROPEL_OBJECT];
			if ($object) {
				return $input->$object();
			} else {
				$value = $input->getByName($key, TableMap::TYPE_FIELDNAME);
				if ($value instanceof DateTime) {
					$type = $this->columns[$key][self::OPT_TYPE];
					if ($type === PropelTypes::DATE) {
						$format = $this->dateFormat ? : Data::$DefaultDateFormat;
					} else if ($type === PropelTypes::TIME) {
						$format = $this->timeFormat ? : Data::$DefaultTimeFormat;
					} else {
						$format = $this->dateTimeFormat ? : Data::$DefaultDateTimeFormat;
					}
					return $value->format($format);
				}
				if ($value === null) {
					return $this->columns[$key][self::OPT_NULL_VALUE];
				}
				return $value;
			}
		}
		if (isset($this->columns[$key][self::OPT_DEFAULT])) {
			return $this->columns[$key][self::OPT_DEFAULT];
		}
		return null;
	}

}
