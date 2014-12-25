<?php

namespace WebDreamt\Component\Wrapper;

use DateTime;
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use WebDreamt\Box;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;
use WebDreamt\Component\Wrapper\Data;

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
	 * The method to call on the propel object to access a different object. only applies if input is
	 * given as a Propel object.
	 */
	const OPT_PROPEL_OBJECT = 'object';
	/**
	 * The method to use to get input for the extra component.
	 */
	const EXTRA_METHOD = 'method';
	/**
	 * The extra component.
	 */
	const EXTRA_COMPONENT = 'component';

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
	 * The Propel table map.
	 * @var TableMap
	 */
	protected $tableMap;
	/**
	 * A component to show labels in.
	 * @var Component
	 */
	protected $label;
	/**
	 * Indicates if the labels should output alongside the data.
	 * @var boolean
	 */
	protected $labelInDisplay;
	/**
	 * The css prefix used to identify column data.
	 * @var string
	 */
	protected $dataClass;
	/**
	 * The time format.
	 * @var string
	 */
	protected $timeFormat = "g:i a";
	/**
	 * The date time format.
	 * @var string
	 */
	protected $dateTimeFormat = "g:i a, m/d/y";
	/**
	 * The date format.
	 * @var string
	 */
	protected $dateFormat = 'm/d/y';

	/**
	 * Construct a component that represents a table in the database.
	 * @param string $tableName
	 */
	function __construct(Component $display, $tableName, $htmlTag = 'div', $class = null, $html = null) {
		parent::__construct($display, $htmlTag, $class, $html);
		$table = Propel::getDatabaseMap()->getTable($tableName);
		//Keep a reference to the table map so when something is linked, we can look up the linked
		//table's information.
		$this->tableMap = $table;
		$this->tableName = $tableName;
		$this->title = static::beautify($tableName);
		foreach ($table->getColumns() as $column) {
			$name = $column->getName();
			$this->columns[$name] = $this->getDefaultOptions();
			$this->addColumn($column, $this->columns[$name]);
		}
	}

	/**
	 * Change the default column options. Note that this does not set the PROPEL_OBJECT property.
	 * This is instead set by addRelatedTable() and link().
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
			self::OPT_LABEL => null
		];
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
	 * Set a CSS class prefix that will be used to identify column data. If null, then no CSS class
	 * will be used.
	 * @param string $dataClass
	 * @return self
	 */
	function setDataClass($dataClass) {
		$this->dataClass = $dataClass;
		return $this;
	}

	/**
	 * Get the CSS prefix.
	 * @return string
	 */
	function getDataClass() {
		return $this->dataClass;
	}

	/**
	 * Set the time format used by the component.
	 * @param string $format
	 * @return self
	 */
	function setTimeFormat($format = 'g:i a') {
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
	 * Set the date time format used by the component.
	 * @param string $format
	 * @return self
	 */
	function setDateTimeFormat($format = 'g:i a, m-d-y') {
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
	 * Set the date format used by the component.
	 * @param string $format
	 * @return self
	 */
	function setDateFormat($format = 'm-d-y') {
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
	 * Set a component to show labels in. Defaults to null. Also, can specify whether the output should
	 * automatically render the label in the display component. If true, then will put the label inside
	 * the component. If false, then will put the label alongside the component. If null, will
	 * not output the label.
	 * @param Component $label
	 * @param boolean $labelInDisplay Can be
	 * @return self;
	 */
	function setLabelComponent(Component $label = null, $labelInDisplay = true) {
		$this->label = $label;
		$this->labelInDisplay = $labelInDisplay;
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
	 * Link a column value with another Data component.
	 * @param string $column
	 * @param Data $component
	 * @return self
	 */
	function link($column, Data $component) {
		if (!isset($this->linked[$column])) {
			$this->linked[$column] = [];
		}
		$this->linked[$column][] = $component;
		//Set the Propel method that needs to be called to get input for the linked component.
		$columnMap = $this->tableMap->getColumn($column);
		$propel = 'get' . $columnMap->getRelatedTable()->getPhpName();
		if (!method_exists($this->tableMap->getPhpName(), $propel)) {
			$propel .= 'By' . $columnMap->getPhpName();
		}
		$this->columns[$column][self::OPT_PROPEL_OBJECT] = $propel;
		return $this;
	}

	/**
	 * Add an extra component that will be rendered within the context of the current component.
	 * This also will check the table name of the given component and compute the method to call
	 * to get the input for the extra component.
	 * @param Component $component
	 * @param string $column The column that goes after the extra component. If null, then will put the
	 * component after the last column.
	 * @param string $inputIdColumn If there are multiple methods to call on the input (i.e.
	 * multiple "RelatedBy" methods), then specify the ID column to use.
	 * @return self
	 */
	function addExtraComponent($component, $column = null, $inputIdColumn = null) {
		$array = &$this->components;
		//Figure out the array the component should be added to: column component or regular.
		if ($column !== null) {
			if (!isset($this->columnComponents[$column])) {
				$this->columnComponents[$column] = [];
			}
			$array = &$this->columnComponents[$column];
		}
		//Figure out the Propel method if applicable.
		$propel = null;
		if ($component instanceof Data) {
			$table = Propel::getDatabaseMap()->getTable($component->getTableName());
			$propel = 'get' . Box::now()->pluralize($table->getPhpName());
			if ($inputIdColumn) {
				$propel .= 'RelatedBy' . $table->getColumn($inputIdColumn)->getPhpName();
			}
		}
		//Add to the end of the array.
		$array[] = [
			self::EXTRA_METHOD => $propel,
			self::EXTRA_COMPONENT => $component
		];
		return $this;
	}

	/**
	 * Make columns visible. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return self
	 */
	function show($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_VISIBLE, true);
		return $this;
	}

	/**
	 * Hides columns. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return self
	 */
	function hide($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_VISIBLE, false);
		return $this;
	}

	/**
	 * Enable the columns to be sent to the client. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return self
	 */
	function allow($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_ACCESS, true);
		return $this;
	}

	/**
	 * Disable the columns from being sent to the client. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return self
	 */
	function deny($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_ACCESS, false);
		return $this;
	}

	/**
	 * Set the default values. Can use a Propel object to set the default values.
	 * @param array|ActiveRecordInterface $columns
	 * @return self
	 */
	function setDefaultValues($columns) {
		if ($columns instanceof ActiveRecordInterface) {
			foreach ($this->columns as $column => $options) {
				$value = $columns->getByName($column, TableMap::TYPE_FIELDNAME);
				$this->columns[$column][self::OPT_DEFAULT] = $value;
			}
		} else {
			$this->merge($columns, self::OPT_DEFAULT);
		}
		return $this;
	}

	/**
	 * Set the labels.
	 * @param array $columns
	 * @return self
	 */
	function setLabels(array $columns) {
		$this->merge($columns, self::OPT_LABEL);
		return $this;
	}

	/**
	 * Output the HTML of the labels.
	 * @return self
	 */
	function printLabels() {
		foreach ($this->columns as $options) {
			if ($options[self::OPT_ACCESS]) {
				if ($options[self::OPT_VISIBLE]) {
					$this->display->useHtml('style="display:none"');
				}
				$this->label->render($options[self::OPT_LABEL], static::class);
			}
		}
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
	 * @return self
	 */
	function order(array $columns = []) {
		$newColumns = [];
		$count = count($this->columns);
		for ($i = 0; $i < $count; $i++) {
			if (isset($columns[$i]) && isset($this->columns[$columns[$i]])) {
				$newColumns[] = $this->columns[$columns[$i]];
				unset($this->columns[$columns[$i]]);
			} else if ($this->columns) {
				$newColumns[] = array_shift($this->columns);
			}
		}
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
	 * @return self
	 */
	function alias(array $oldToNewColumns = [], $changeLabel = true) {
		$newColumns = [];
		foreach ($this->columns as $oldColumn => $option) {
			if (isset($oldToNewColumns[$oldColumn])) {
				$newColumns[$oldToNewColumns[$oldColumn]] = $option;
				if ($changeLabel) {
					$option[self::OPT_LABEL] = static::spaceName($oldToNewColumns[$oldColumn]);
				}
			} else {
				$newColumns[] = $option;
			}
		}
		$this->columns = $newColumns;
		return $this;
	}

	/**
	 * Applies the $options for each $columns.
	 * @param array|string $columns If an array, then column names are values. If a string, then
	 * $columns = [$columns]. If empty, then assumes option applies to all columns.
	 * @param array|string $options If an array, then keys are option names and values are
	 * option values. If a string, then assumes $options is an option name.
	 * @param string $value If $options is a string instead of an array, then $value is
	 * used as the value for the option specified by $options.
	 * @return self
	 */
	function apply($columns, $options, $value = null) {
		//Coerce $columns into an array.
		if (empty($columns)) {
			$columns = array_keys($this->columns);
		} else if (!is_array($columns)) {
			$columns = [$columns];
		}
		if (!is_array($options)) {
			foreach ($columns as $name) {
				$this->columns[$name][$options] = $value;
			}
		} else {
			foreach ($columns as $name) {
				$this->columns[$name] = array_merge($this->columns[$name], $options);
			}
		}
		return $this;
	}

	/**
	 * Merges in the values contained in the $columns array. If you don't want to put the values
	 * in $columns, see apply().
	 * 1) If $options is not null, then $columns is assumed to be an array where each key is a
	 * column name and each value is option value for the option specified by $option.
	 * 2) If $options is null, then $columns is assumed to be an array where key is a column
	 * name and each value is an array with keys of option names and values of option values.
	 * @param array $columns
	 * @param string $option
	 * @return self
	 */
	function merge(array $columns, $option = null) {
		if ($option) {
			foreach ($columns as $name => $value) {
				$this->columns[$name][$option] = $value;
			}
		} else {
			$this->columns = array_merge_recursive($this->columns, $columns);
		}
		return $this;
	}

	/**
	 * Render the child component. Child classes may want to override this method along with
	 * renderColumn().
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 */
	protected function renderMe($input = null, $included = null) {
		foreach ($this->columns as $column => $options) {
			if ($options[self::OPT_ACCESS]) {
				if ($options[self::OPT_VISIBLE]) {
					$this->display->useHtml('style="display:none"');
				}
				if ($this->dataClass) {
					$this->display->useCssClass($this->dataClass . "-$column");
				}
				if ($this->label && $this->labelInDisplay !== null) {
					if ($options[self::OPT_VISIBLE]) {
						$this->label->useHtml('style="display:none"');
					}
					if ($this->labelInDisplay) {
						ob_start();
						$this->label->render($options[self::OPT_LABEL], static::class);
						$this->display->setAfterOpeningTag(ob_get_clean());
					} else {
						$this->label->render($options[self::OPT_LABEL], static::class);
					}
				}
				$value = $this->getValueFromInput($column, $input);
				$linked = $this->renderLinkedComponents($column, $value);
				if (!$linked) {
					$this->renderColumn($column, $value);
				}
				$this->renderColumnComponents($column, $input);
			}
		}
	}

	/**
	 * Render a column.
	 * @param string $column
	 * @param mixed $value
	 */
	protected function renderColumn($column, $value) {
		$this->display->renderMe($value, $this);
	}

	/**
	 * Render any linked components for the given column. Return null if there is no component
	 * for the given column.
	 * @param string $column
	 * @param array|ActiveRecordInterface $input The input to be given to the component.
	 * @return boolean True if something was rendered; false otherwise.
	 */
	protected function renderLinkedComponents($column, $input = null) {
		$result = false;
		if (isset($this->linked[$column])) {
			foreach ($this->linked[$column] as $component) {
				$component->render($input, static::class);
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * Render any extra components for the given column. Return null if there is no component
	 * for the given column.
	 * @param string $column
	 * @param array|ActiveRecordInterface $input The input to be given to the component.
	 * @return boolean True if something was rendered; false otherwise.
	 */
	protected function renderColumnComponents($column, $input = null) {
		$result = false;
		if (isset($this->columnComponents[$column])) {
			foreach ($this->columnComponents[$column] as $component) {
				$component->render($input, static::class);
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * Render the componenents.
	 * @param array|ActiveRecordInterface $input
	 * @param string $included
	 */
	protected function renderComponents($input = null, $included = null) {
		foreach ($this->components as $object) {
			if (!$object) {
				$this->renderMe($input, $included);
			} else {
				$method = $object[self::EXTRA_METHOD];
				$component = $object[self::EXTRA_COMPONENT];
				if ($input instanceof ActiveRecordInterface && $method) {
					$component->render($input->$method(), static::class);
				} else {
					$component->render($input, static::class);
				}
			}
		}
		return null;
	}

	/**
	 * Get a value from input. Note that if the $value is a DateTime object, convert it to a string
	 * in the format specifed by either the setDateTimeFormat(), setTimeFormat(), or setDateFormat()
	 * methods depending on the type of the column in the database.
	 * @param string $key The key to use to try to get the value from the input.
	 * @param mixed $input
	 * @return string
	 */
	protected function getValueFromInput($key, $input) {
		if (is_array($input) && isset($input[$key])) {
			return $input[$key];
		} else if ($input instanceof ActiveRecordInterface) {
			$object = $this->columns[$key][self::OPT_PROPEL_OBJECT];
			if ($object) {
				return $input->$object();
			} else {
				$value = $input->getByName($key, TableMap::TYPE_FIELDNAME);
				if ($value instanceof DateTime) {
					$type = $this->columns[$key][self::OPT_TYPE];
					if ($type === PropelTypes::DATE) {
						return $value->format($this->dateFormat);
					} else if ($type === PropelTypes::TIME) {
						return $value->format($this->timeFormat);
					} else {
						return $value->format($this->dateTimeFormat);
					}
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
