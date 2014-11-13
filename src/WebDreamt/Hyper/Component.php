<?php

namespace WebDreamt\Hyper;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;

/**
 * A class to be used as a base to render other objects from the database.
 */
abstract class Component {

	/**
	 * The accessibility of the column. A value of false means that the client does not receive any
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
	 * An array of the $options as keys. This is lazily initiated and includes options declared
	 * in child and parent classes.
	 * @var array
	 */
	static protected $options = [];
	/**
	 * An array where the keys are column names and the values are the options for each column.
	 * @var array
	 */
	protected $columns = [];
	/**
	 * An array where the keys are column names and the values are arrays with values
	 * that are Components.
	 * @var array
	 */
	protected $linked = [];
	/**
	 * HTML attributes for the top level element.
	 * @var string
	 */
	protected $html = '';
	/**
	 * CSS classes for the top level element.
	 * @var array
	 */
	protected $classes = [];
	/**
	 * The name of the table
	 * @var string
	 */
	protected $tableName;

	/**
	 * Constructs a component.
	 * @param string $table
	 */
	function __construct($table) {
		$table = Propel::getDatabaseMap()->getTable($table);
		$this->tableName = $table->getName();
		foreach ($table->getColumns() as $column) {
			$name = $column->getName();
			$this->columns[$name] = $this->getDefaultOptions();
			$this->addColumn($column, $this->columns[$name]);
		}
	}

	/**
	 * Changes the default column options.
	 * @param ColumnMap $column
	 * @param array $options
	 */
	protected function addColumn(ColumnMap $column, array &$options) {
		$options[self::OPT_DEFAULT] = $column->getDefaultValue();
		$options[self::OPT_TYPE] = $column->getType();
		if ($options[self::OPT_TYPE] === PropelTypes::ENUM) {
			$options[self::OPT_EXTRA] = $column->getValueSet();
		} else {
			$options[self::OPT_EXTRA] = $column->getSize();
		}
	}

	/**
	 * Gets the default options.
	 * @return array
	 */
	protected function getDefaultOptions() {
		return [
			self::OPT_ACCESS => true,
			self::OPT_VISIBLE => true,
			self::OPT_DEFAULT => null,
			self::OPT_TYPE => null,
			self::OPT_EXTRA => null
		];
	}

	/**
	 * Sets the HTML of the top level element. Note use addCssClass to add classes.
	 * @param string $html
	 * @return self
	 */
	function setHtml($html = '') {
		$this->html = $html;
		return $this;
	}

	/**
	 * Gets the HTML of the top level element.
	 * @return string
	 */
	function getHtml() {
		return $this->html;
	}

	/**
	 * Appends on to the HTML of the top level element.
	 * @param string $html
	 * @return self
	 */
	function appendHtml($html = '') {
		$this->html .= $html;
		return $this;
	}

	/**
	 * Adds a CSS class to the top level element. Note use addCssClass to add classes.
	 * @param array|string|... $className
	 * @return self
	 */
	function addCssClass($className) {
		if (is_array($className)) {
			$array = $className;
		} else {
			$array = func_get_args();
		}
		$this->classes = array_merge($this->classes, $array);
		return $this;
	}

	/**
	 * Link a column value with another Component.
	 * @param string $column
	 * @param Component $component
	 * @return self
	 */
	function link($column, $component) {
		if (!isset($this->linked[$column])) {
			$this->linked[$column] = [];
		}
		$this->linked[$column][] = $component;
		return $this;
	}

	/**
	 * Makes columns visible. Defaults to all columns.
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
	 * Disables the columns from being sent to the client. Defaults to all columns.
	 * @param array|... $columns Column names should be the values of the array.
	 * @return self
	 */
	function deny($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_ACCESS, false);
		return $this;
	}

	/**
	 * Sets the default values. Can use a Propel object to set the default values.
	 * @param array|ActiveRecordInterface $columns
	 * @return self
	 */
	function setDefaultValues($columns) {
		if ($columns instanceof ActiveRecordInterface) {
			$columns = $columns->toArray(TableMap::TYPE_COLNAME);
		}
		$this->merge($columns, self::OPT_DEFAULT);
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
				$columns[$name][$options] = $value;
			}
		} else {
			foreach ($columns as $name) {
				$columns[$name] = $options;
			}
		}
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
	 */
	function merge(array $columns, $option = null) {
		if ($option) {
			foreach ($columns as $name => $value) {
				$this->columns[$name][$option] = $value;
			}
		} else {
			$this->columns = array_merge_recursive($this->columns, $columns);
		}
	}

	/**
	 * Syntactic sugar for the render(...) method.
	 * @return string
	 */
	function __toString() {
		return $this->render();
	}

	/**
	 * Renders the component.
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 * @return string
	 */
	abstract function render($input = null, $included = null);
	/**
	 * Parses input into an array.
	 * @param array|ActiveRecordInterface|ActiveRecordInterface[] $input
	 * @return array
	 */
	protected function parseInput($input = null) {
		if ($input instanceof ActiveRecordInterface) {
			return $input->toArray(TableMap::TYPE_COLNAME);
		} else if (!empty($input) && $input[0] instanceof ActiveRecordInterface) {
			return $input->toArray(TableMap::TYPE_COLNAME);
		} else if (is_array($input)) {
			return $input;
		}
		return null;
	}

	/**
	 * Spaces out a column name.
	 * @param string $name
	 * @return string
	 */
	static protected function spaceColumnName($name) {
		return ucwords(str_replace('_', ' ', $name));
	}

}
