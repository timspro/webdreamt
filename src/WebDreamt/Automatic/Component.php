<?php
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use WebDreamt\Automatic\Component;
use WebDreamt\Cache;

namespace WebDreamt\Automatic;

abstract class Component {

	/**
	 * The accessibility of the column. A value of false means that the client does not receive any
	 * indication that the column exists. A value of true means normal behavior.
	 */
	const OPT_ACCESS = 'access';
	/**
	 * The visibility of the column. A value of true means "show" and a value of false means
	 * "hidden". Note that this is different from OPT_ACCESS because this still allows for some
	 * information to be sent to the client, which can be used by the browser or JavaScript to
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
	 * The columns of the related tabel in the foreign key.
	 */
	const REL_COLUMNS = 'columns';

	/**
	 * An array (*) where the column names of the tables are the keys of the array and
	 * the values are either 1) an array as defined by (*) or 2) an options map which is by default
	 * defined by the result of $this->getDefaultOptions();
	 * @var array
	 */
	protected $columns;
	/**
	 * An array of inputs to be used to rendering the component. Note that this is essentially
	 * useless for the Component or any of its child classes due to caching.
	 * It exists solely to be passed on to the cache, which will render the template with the values.
	 * @var array
	 */
	protected $values;

	/**
	 * Parses the columns.
	 * @param TableMap $table
	 * @param array $values Any inputs to be used in the component that won't be cached.
	 */
	function __construct(TableMap $table, array $values = null) {
		$this->columns = [];
		$this->addColumns($table->getColumns());
		$this->values = $values;
	}

	/**
	 * Adds columns to the object.
	 * @param ColumnMap[] $columns
	 * @param array $base
	 */
	protected function addColumns(array $columns, array &$base = null) {
		if (!$base) {
			$base = &$this->columns;
		}
		/* @var $column ColumnMap */
		foreach ($columns as $column) {
			$phpName = $column->getPhpName();
			if ($column->isForeignKey()) {
				$base[$phpName] = $this->getDefaultRelation();
				$this->addRelation($column, $base[$phpName]);
			} else {
				$base[$phpName] = $this->getDefaultOptions();
				$this->addColumn($column, $base[$phpName]);
			}
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
	 * Changes the default foreign key options.
	 * @param ColumnMap $column
	 * @param array $options
	 */
	protected function addRelation(ColumnMap $column, array &$options) {

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
	 * Gets the default options for the foreign key.
	 * @return array
	 */
	protected function getDefaultRelation() {
		return [
			self::REL_COLUMNS => null
		];
	}

	/**
	 * Makes columns visible. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function show(array $columns = null) {
		$this->apply($columns, [self::OPT_VISIBLE => true]);
		return $this;
	}

	/**
	 * Hides columns. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function hide(array $columns = null) {
		$this->apply($columns, [self::OPT_VISIBLE => false]);
		return $this;
	}

	/**
	 * Enable the columns to be sent to the client. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function allow(array $columns = null) {
		$this->apply($columns, [self::OPT_ACCESS => true]);
		return $this;
	}

	/**
	 * Disables the columns from being sent to the client. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function deny(array $columns = null) {
		$this->apply($columns, [self::OPT_ACCESS => false]);
		return $this;
	}

	/**
	 * Sets the default values.
	 * @param array|ActiveRecordInterface $columns
	 * @return self
	 */
	function setDefaultValues($columns) {
		if ($columns instanceof ActiveRecordInterface) {
			$columns = $columns->toArray();
		}
		$this->applyTo($columns, self::OPT_DEFAULT);
		return $this;
	}

	/**
	 * Applies an options object to each of the selected columns.
	 * @param array $columns Column names should be the keys of the array. The values are unimportant
	 * but should not be arrays unless the column is a foreign key and you are trying to set the
	 * options for a related column. $columns can also be null, in which case $options will be
	 * applied to all columns.
	 * @param array $options
	 * @return this
	 */
	function apply(array $columns, array $options, array &$modify = null) {
		if (!$modify) {
			$modify = $this->columns;
		}
		if (!$columns) {
			$columns = $this->columns;
			$check = self::isColumns;
		} else {
			$check = is_array;
		}
		foreach ($columns as $key => $value) {
			$settingOption = !$check($value);
			$gettingOption = !self::isRelation($modify[$key]);
			if ($settingOption && $gettingOption) {
				$modify[$key] = array_merge($modify[$key], $options);
			} else if ($settingOption && !$gettingOption) {
				//In this case, apply the option to all subarrays.
				$this->applyToAll($options, $modify[$key][self::REL_COLUMNS]);
			} else if (!$settingOption && $gettingOption) {
				throw new Exception("$key is not a foreign key.");
			} else {
				$this->apply($value, $options, $modify[$key][self::REL_COLUMNS]);
			}
		}
		return $this;
	}

	function applyRelation(array $relations, array $options) {

	}

	/**
	 * Sets the value for the $option key in the option object of the selected columns to the values
	 * given in the $columns array.
	 * @param array $columns Column names should be the keys of the array. The values for the option
	 * should be the values of the array.
	 * @param string $option
	 * @return this
	 */
	function applyTo(array $columns, $option, array &$modify = null) {
		if (!$modify) {
			$modify = $this->columns;
		}
		foreach ($columns as $key => $value) {
			$settingOption = !is_array($value);
			$gettingOption = !self::isRelation($modify[$key]);
			if ($settingOption && $gettingOption) {
				$modify[$option] = $value;
			} else if ($settingOption && !$gettingOption) {
				//In this case, apply the option to all subarrays.
				$this->applyToAll([$option => $value], $modify[$key][self::REL_COLUMNS]);
			} else if (!$settingOption && $gettingOption) {
				throw new Exception("$key is not a foreign key.");
			} else {
				$this->applyTo($value, $option, $modify[$key][self::REL_COLUMNS]);
			}
		}
		return $this;
	}

	/**
	 * Applies the options to all columns in $modify. Note that the public version of this method
	 * is basically apply(...).
	 * @param array $options
	 * @param array $modify
	 */
	protected function applyToAll(array $options, array &$modify) {
		foreach ($modify as $key => $value) {
			if (self::isRelation($modify[$key])) {
				$this->applyToAll($options, $modify[$key][self::REL_COLUMNS]);
			} else {
				$modify[$key] = array_merge($modify[$key], $value);
			}
		}
	}

	/**
	 * Checks if the given array represents the columns of a table in the database.
	 * Note that this only works for arrays in the $columns variable since it actually checks
	 * a property of the $columns variable's structure.
	 * @param array $array
	 * @return boolean
	 */
	protected static function isRelation($array) {
		return isset($array[self::REL_COLUMNS]);
	}

	/**
	 * Gets the values to be used as inputs.
	 * @return array
	 */
	function getValues() {
		return $this->values;
	}

	/**
	 * Renders the form with the given values and options.
	 * @return string
	 */
	function __toString() {
		//The cache will take care of the rendering.
		return Cache::add($this);
	}

	/**
	 * Gets the template as a string to be used to render the component.
	 */
	abstract function getTemplate();
}
