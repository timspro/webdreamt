<?php

namespace WebDreamt\Hyper;

use DateTime;
use Propel\Common\Pluralizer\StandardEnglishPluralizer;
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;

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
	 * The label for the column displayed in an easily human-readable form
	 */
	const OPT_LABEL = 'label';
	/**
	 * The method to call on the propel object to access a different object. Only applies if input is
	 * given as a Propel object.
	 */
	const OPT_PROPEL_OBJECT = 'object';

	/**
	 * An instance of the pluralizer Propel uses for pluralization. Should be accessed through
	 * getPluralizer().
	 * @var StandardEnglishPluralizer
	 */
	static private $pluralizer;
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
	protected $tableName = '';
	/**
	 * The table map
	 * @var TableMap
	 */
	protected $tableMap = null;
	/**
	 * An array of extra components to render.
	 * @var array
	 */
	protected $extraComponents = [];
	/**
	 * The input to be passed to the render method.
	 * @var array
	 */
	protected $input = null;
	/**
	 * Indicates whether the labels should be shown.
	 * @var boolean
	 */
	protected $showLabels = true;

	/**
	 * Constructs a component. Note that the provided table name can be null, but such a setting
	 * might not make sense for the child component.
	 * @param string $tableName
	 */
	function __construct($tableName = null) {
		if ($tableName) {
			$table = Propel::getDatabaseMap()->getTable($tableName);
			//Keep a reference to the table map so when something is linked, we can look up the linked table's
			//information.
			$this->tableMap = $table;
			$this->tableName = $tableName;
			foreach ($table->getColumns() as $column) {
				$name = $column->getName();
				$this->columns[$name] = $this->getDefaultOptions();
				$this->addColumn($column, $this->columns[$name]);
			}
		}
	}

	/**
	 * Changes the default column options. Note that this does not set the PROPEL_OBJECT property.
	 * This is instead set by addRelatedTable() and link().
	 * @param ColumnMap $column
	 * @param array $options
	 */
	protected function addColumn(ColumnMap $column, array &$options) {
		$options[self::OPT_LABEL] = static::spaceColumnName($column->getName());
		$options[self::OPT_DEFAULT] = $column->getDefaultValue();
		$options[self::OPT_TYPE] = $column->getType();
		if ($options[self::OPT_TYPE] === PropelTypes::ENUM) {
			$options[self::OPT_EXTRA] = $column->getValueSet();
		} else {
			$options[self::OPT_EXTRA] = $column->getSize();
		}

		if (substr($column->getName(), -2) === 'id') {
			$remainder = substr($options[self::OPT_LABEL], 0, strlen($options[self::OPT_LABEL]) - 2);
			$options[self::OPT_LABEL] = $remainder . 'ID';
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
			self::OPT_EXTRA => null,
			self::OPT_PROPEL_OBJECT => null,
			self::OPT_LABEL => null
		];
	}

	/**
	 * Adds an extra component that will be rendered within the context of the current component.
	 * This also checks the table name of the given component and computes the method to call to get the
	 * input for the extra component.
	 * @param Component $component
	 * @param string $inputIdColumn If there are multiple methods to call on the input (i.e.
	 * multiple "RelatedBy" methods), then specify the ID column to use.
	 * @return self
	 */
	function addExtraComponent($component, $inputIdColumn = null) {
		$table = Propel::getDatabaseMap()->getTable($component->getTableName());
		$propel = 'get' . self::pluralize($table->getPhpName());
		if ($inputIdColumn) {
			$propel .= 'RelatedBy' . $table->getColumn($inputIdColumn)->getPhpName();
		}
		$this->extraComponents[$propel] = $component;
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
	 * Set the input of the component. Note that input set this way will override input passed to the
	 * render method.
	 * @param array $input
	 * @return self
	 */
	function setInput($input = null) {
		$this->input = $input;
		return $this;
	}

	/**
	 * Get the input of the component.
	 * @return array
	 */
	function getInput() {
		return $this->input;
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
	 * Shows or hide labels. Defaults to true.
	 * @param boolean $labels
	 * @return self;
	 */
	function showLabels($labels = true) {
		$this->showLabels = $labels;
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

		//Set the propel method that needs to be called to get input for the linked component.
		$columnMap = $this->tableMap->getColumn($column);
		$propel = 'get' . $columnMap->getRelatedTable()->getPhpName();
		if (!method_exists($this->tableMap->getPhpName(), $propel)) {
			$propel .= 'By' . $columnMap->getPhpName();
		}
		$this->columns[$column][self::OPT_PROPEL_OBJECT] = $propel;
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
	 * Sets the labels.
	 * @param array $columns
	 * @return this
	 */
	function setLabels($columns) {
		$this->merge($columns, self::OPT_LABEL);
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
				$this->columns[$name][$options] = $value;
			}
		} else {
			foreach ($columns as $name) {
				$this->columns[$name] = array_merge($this->columns[$name], $options);
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
	 * Renders any linked components for the given column. Returns null if there is no component
	 * for the given column.
	 * @param string $column
	 * @param mixed $input The input to be given to the component.
	 * @return string
	 */
	function renderLinked($column, $input = null) {
		if (isset($this->linked[$column])) {
			$result = '';
			foreach ($this->linked[$column] as $component) {
				$result .= $component->render($input, static::class);
			}
			return $result;
		}
		return null;
	}

	/**
	 * Renders the extra componenents.
	 * @param mixed $input
	 * @return string
	 */
	function renderExtra($input = null) {
		$result = '';
		foreach ($this->extraComponents as $method => $component) {
			if ($input instanceof ActiveRecordInterface) {
				$result .= $component->render($input->$method(), static::class);
			} else {
				$result .= $component->render($input, static::class);
			}
		}
		return $result;
	}

	/**
	 * Gets a value from input.
	 * @param string $column
	 * @param mixed $input
	 * @return string
	 */
	protected function getValueFromInput($column, $input) {
		if (is_array($input) && isset($input[$column])) {
			return $input[$column];
		} else if ($input instanceof ActiveRecordInterface) {
			$object = $this->columns[$column][self::OPT_PROPEL_OBJECT];
			if ($object) {
				return $input->$object();
			} else {
				$value = $input->getByName($column, TableMap::TYPE_FIELDNAME);
				if ($value instanceof DateTime) {
					return $value->format('Y-m-d H:i:s');
				}
				return $value;
			}
		}
		if (isset($this->columns[$column][self::OPT_DEFAULT])) {
			return $this->columns[$column][self::OPT_DEFAULT];
		}
		return '';
	}

	/**
	 * Spaces out a column name.
	 * @param string $name
	 * @return string
	 */
	static protected function spaceColumnName($name) {
		return ucwords(str_replace('_', ' ', $name));
	}

	/**
	 * Pluralizes a string using Propel's pluralizer.
	 * @return string
	 */
	static protected function pluralize($string) {
		if (!isset(self::$pluralizer)) {
			self::$pluralizer = new StandardEnglishPluralizer();
		}
		return self::$pluralizer->getPluralForm($string);
	}

}
