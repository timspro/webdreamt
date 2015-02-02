<?php

namespace WebDreamt\Component\Wrapper;

use DateTime;
use Exception;
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use WebDreamt\Box;
use WebDreamt\Builder;
use WebDreamt\Component;
use WebDreamt\Component\Icon;
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
	 * The default value for the column that is displayed when the value cannot be retrieved from
	 * the input.
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
	 * Whether the label is sent in the HTML or not.
	 */
	const OPT_LABEL_ACCESS = 'label_access';
	/**
	 * The method to call on the propel object to access a different object. This only applies if input is
	 * given as a Propel object.
	 */
	const OPT_PROPEL_OBJECT = 'object';
	/**
	 * The column that is ultimately used to get the Propel object. This is obvious for the case that the
	 * column is in the data component's table, but is useful for noting many-to-many relationships.
	 */
	const OPT_PROPEL_COLUMN = 'column';
	/**
	 * A string to output when a value is retrieved from the input and it is null.
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
	 * The class name for the table.
	 * @var string
	 */
	protected $className;
	/**
	 * The primary keys for the table.
	 * @var array
	 */
	protected $primaryKeys;
	/**
	 * A component to show labels in.
	 * @var Component
	 */
	protected $label;
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
	 * The icons
	 * @var Component
	 */
	protected $iconContainer;
	/**
	 * The default time format for the class: g:i a
	 * @var string
	 */
	static public $DefaultTimeFormat = "g:i a";
	/**
	 * The default date time format for the class: g:i a, n/d/y
	 * @var string
	 */
	static public $DefaultDateTimeFormat = "g:i a, n/d/y";
	/**
	 * The default date format for the class: n/d/y
	 * @var string
	 */
	static public $DefaultDateFormat = 'n/d/y';

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

		Builder::loadMaps();

		$this->label = new Component();
		if ($tableName !== null) {
			$table = Propel::getDatabaseMap()->getTable($tableName);
			$this->tableName = $tableName;
			$this->className = $table->getPhpName();
			$this->primaryKeys = $table->getPrimaryKeys();
			$this->title = static::beautify($tableName);
			foreach ($table->getColumns() as $column) {
				$name = $column->getName();
				$this->columns[$name] = $this->getDefaultOptions();
				$this->addColumn($column, $this->columns[$name]);
			}
		}
	}

	/**
	 * Change the default column options. Note that this does not set the PROPEL_OBJECT property.
	 * This is instead set by addExtraComponent() and link().
	 * @param ColumnMap $column
	 * @param array $options
	 */
	protected function addColumn(ColumnMap $column, array &$options) {
		if (substr($column->getName(), -3) === '_id' || $column->getName() === 'id') {
			$options[self::OPT_VISIBLE] = false;
		}

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
			self::OPT_LABEL_ACCESS => false
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
	 * Set the date time format used by the component. Default is 'g:i a, n/d/y'.
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
	 * Set the date format used by the component. Default is 'n/d/y'.
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
	 * being rendered. If this is undesirable, then you can specify that the data component should
	 * also do the default behavior by calling link() with null as the $component as in:
	 * <code>
	 * $a->link('column_name', null);
	 * </code>
	 * Multiple components can be linked to one column. Linked components will also attempt to use
	 * a method to get the appropriate input based on the column linked to if the linked component is
	 * data. This will also make the column visible.
	 * @param string $column A column name of this component.
	 * @param Component $component Can be any component. However, there is unique functionality for
	 * data components.
	 * @param string $manyColumn Specifies a column in the linked component to link back
	 * to this component, which will be used instead of the link column to retrieve input.
	 * @param boolean $autoPropel If false, then will not try to automatically figure out
	 * the related Propel method to call on the object.
	 * @return static
	 */
	function link($column, Component $component, $manyColumn = null, $autoPropel = true) {
		if (!isset($this->columns[$column])) {
			throw new Exception("Cannot link column $column since it doesn't exist.");
		}
		if (!isset($this->linked[$column])) {
			$this->linked[$column] = [];
		}
		$this->linked[$column][] = $component;
		$this->columns[$column][self::OPT_VISIBLE] = true;

		//Set the Propel method that needs to be called to get input for the linked component.
		$propel = null;
		if ($autoPropel) {
			while ($component instanceof Wrapper) {
				if ($component instanceof Data) {
					$thisTableName = $this->getTableName();
					$thisTable = Propel::getDatabaseMap()->getTable($thisTableName);
					if ($manyColumn === null) {
						$thisColumnMap = $thisTable->getColumn($column);
						$propel = 'get' . $thisColumnMap->getRelatedTable()->getPhpName();
						if (!method_exists($thisTable->getPhpName(), $propel)) {
							$propel .= 'RelatedBy' . $thisColumnMap->getPhpName();
						}
						$this->columns[$column][self::OPT_PROPEL_COLUMN] = "$thisTableName.$column";
					} else {
						$compTableName = $component->getTableName();
						$compTable = Propel::getDatabaseMap()->getTable($compTableName);
						$propel = 'get' . Box::get()->pluralize($compTable->getPhpName());
						if (!method_exists($thisTable->getPhpName(), $propel)) {
							$propel .= 'RelatedBy' . $compTable->getColumn($manyColumn)->getPhpName();
						}
						$this->columns[$column][self::OPT_PROPEL_COLUMN] = "$compTableName.$manyColumn";
					}
					break;
				}
				$component = $component->getDisplayComponent();
			}
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
	 * Make labels visible. Defaults to making labels visible for all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return static
	 */
	function allowLabels($columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_LABEL_ACCESS, true);
		return $this;
	}

	/**
	 * Hide labels. Defaults to making labels hidden for all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return static
	 */
	function denyLabels($columns = null) {
		$this->setOptions(is_array($columns) ? $columns : func_get_args(), self::OPT_LABEL_ACCESS, false);
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
	 * Set the labels. Any labels set with this method will automatically be shown.
	 * @param array $columns
	 * @return static
	 */
	function setLabels(array $columns) {
		$this->mergeOptions($columns, self::OPT_LABEL);
		$this->allowLabels(array_keys($columns));
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
		return isset($this->columns[$column][$option]) ? $this->columns[$column][$option] : null;
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
				$ret[$column] = isset($options[$option]) ? $options[$option] : null;
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
	protected function renderInput($input = null, $included = null) {
		$output = null;
		$label = $this->getLabelComponent();
		foreach ($this->columns as $column => $options) {
			$this->renderedColumn = $column;
			if ($options[self::OPT_ACCESS]) {
				//Set the input of the label regardless as it might be used somewhere in the display
				//component (such as via addExtraComponent()).
				$label->setInput($options[self::OPT_LABEL]);
				if ($options[self::OPT_LABEL_ACCESS] && $options[self::OPT_LABEL] !== null) {
					//Similar to renderComponent() but uses labelClass
					if (!$options[self::OPT_VISIBLE]) {
						$label->useHtml('style="display:none"');
					}
					if ($this->labelClass !== null) {
						$label->useClass($this->labelClass . "-$column");
					}
					$output .= $label->render(null, $this);
				}
				$value = $this->getValueFromInput($column, $input);
				$linked = $this->renderLinkedComponents($column, $value, $included);
				if ($linked === null) {
					$output .= $this->renderComponent($column, null, $value, $included);
				} else {
					$output .= $linked;
				}
			}
		}
		//Reset the label input to null to remove side-effects.
		$label->setInput(null);
		$this->renderedColumn = null;
		return $output;
	}

	/**
	 * Get the column name that is currently being rendered. Useful for linked components.
	 * @return string
	 */
	function getRenderedColumn() {
		return $this->renderedColumn;
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
	protected function renderLinkedComponents($column, $input = null, $included = null) {
		$result = null;
		if (isset($this->linked[$column])) {
			//Note that this code is a bit sloppy and likely could be refactored with code around where
			//renderLinkedComponents() is called from renderSpecial().
			foreach ($this->linked[$column] as $component) {
				$result .= $this->renderComponent($column, $component, $input, $included);
			}
		}
		return $result;
	}

	/**
	 * Either renders an arbirtary component or the default component.
	 * @param string $column
	 * @param Component $component If null, then renders the default component.
	 * @param mixed $input
	 * @param string $included
	 * @return string
	 */
	protected function renderComponent($column, $component, $input = null, $included = null) {
		if ($component === null) {
			$render = $this->display;
		} else {
			$render = $component;
		}
		//Set up HTML.
		if (!$this->columns[$column][self::OPT_VISIBLE]) {
			$render->useHtml('style="display:none"');
		}
		if ($this->dataClass) {
			$render->useCssClass($this->dataClass . "-$column");
		}
		//Render
		if ($component === null) {
			$result = $this->renderColumn($column, $input, $included);
		} else {
			$result = $component->render($input, $this);
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
	 * @param boolean $noObject
	 * @return string
	 */
	protected function getValueFromInput($key, $input, $noObject = false) {
		if ($key === null) {
			return $input;
		}
		$className = '\\' . $this->className;
		if (is_array($input) && array_key_exists($key, $input)) {
			$value = $input[$key];
			if ($value === null) {
				return $this->getOption($key, self::OPT_NULL_VALUE);
			}
			return $value;
		} else if ($input instanceof $className) {
			$object = $this->getOption($key, self::OPT_PROPEL_OBJECT);
			if ($object && !$noObject) {
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
					return $this->getOption($key, self::OPT_NULL_VALUE);
				}
				return $value;
			}
		}
		if (isset($this->columns[$key][self::OPT_DEFAULT])) {
			return $this->columns[$key][self::OPT_DEFAULT];
		}
		return null;
	}

	/**
	 * Add an icon.
	 * @param Icon $icon
	 * @param string $url The URL to send get parameters. If null, then this functionality is not used.
	 * @param boolean $data Indicates if the request will return useful data that can be appended
	 * to the form document.
	 * @return static
	 */
	function addIcon(Icon $icon, $url = null, $data = false) {
		if ($url !== null) {
			$type = $icon->getType();
			if ($type === Icon::TYPE_DELETE) {
				$action = 'delete';
			} else if ($type === Icon::TYPE_EDIT) {
				$action = 'update';
			}

			$icon = new Wrapper($icon, 'a', 'wd-url');
			if ($data) {
				$icon->appendHtml('data-wd-return=""');
			}
			$class = $this->className;
			$icon->setHtmlCallback(function ($input) use ($url, $action, $class) {
				$paramString = "href='$url?";
				foreach ($this->primaryKeys as $key) {
					$key = $key->getName();
					$paramString .= "pk-$key=" . $this->getValueFromInput($key, $input, true) . "&";
				}
				$paramString .= "class=$class&action=$action'";
				return $paramString;
			});
		}

		$this->getIconContainer()->addExtraComponent($icon);
		return $this;
	}

	/**
	 * Get the primary keys from the GET parameters of the URL.
	 * @return array
	 */
	static function getPrimaryKeysFromUrl() {
		if (count($_GET) > 0 && isset($_GET['action']) && isset($_GET['class'])) {
			$pks = [];
			foreach ($_GET as $key => $value) {
				if (substr($key, 0, 3) === 'pk-') {
					$pks[substr($key, 3)] = $value;
				}
			}
			return $pks;
		}
		return null;
	}

	/**
	 * Get an object based on the GET parameters of the URL.
	 * @return ActiveRecordInterface
	 */
	static function getObjectFromUrl() {
		$pks = static::getPrimaryKeysFromUrl();
		if ($pks !== null) {

			$class = $_GET['class'];
			$query = $class . "Query";
			if (count($pks) === 1) {
				$object = call_user_func_array([$query::create(), "findPk"], $pks);
			} else {
				$object = call_user_func([$query::create(), "findPk"], array_values($pks));
			}

			return $object;
		}
		return null;
	}

}
