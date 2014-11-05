<?php
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use WebDreamt\Automatic\Component;
use WebDreamt\Cache;

namespace WebDreamt\Automatic;

abstract class Component {

	const OPT_ACCESS = 'access';
	const OPT_VISIBLE = 'visible';
	/**
	 *
	 */
	const OPT_VALUE = 'value';
	/**
	 * The Propel type of the column.
	 */
	const OPT_TYPE = 'type';
	/**
	 * Extra information about the type. Set of possible values for ENUM and length for others.
	 */
	const OPT_EXTRA = 'extra';
	/**
	 * If the column is a foreign key, then will be an array of columns => options. Null otherwise.
	 */
	const OPT_RELATED = 'related';

	/**
	 * An array (*) where the column names of the tables are the keys of the array and
	 * the values are either 1) an array as defined by (*) or 2) an options map which is by default
	 * defined by the result of $this->getDefaultOptions();
	 * @var array
	 */
	protected $columns;
	/**
	 * An array of inputs to be used to rendering the component.
	 * @var array
	 */
	private $values;

	/**
	 * Parses the columns.
	 * @param TableMap $table
	 * @param array $values Any inputs to be used in the component that won't be cached.
	 */
	function __construct(TableMap $table, array $values = null) {
		$this->columns = [];
		$this->addColumns($table->getColumns(), $this->columns);
		$this->values = $values;
	}

	/**
	 * Adds columns to the object.
	 * @param ColumnMap[] $columns
	 * @param array $base
	 */
	protected function addColumns(array $columns, array &$base) {
		foreach ($columns as $column) {
			$phpName = $column->getPhpName();
			$base[$phpName] = $this->getDefaultOptions();
			$this->addColumn($column, $base[$phpName]);
		}
	}

	/**
	 * Changes the default column options.
	 * @param ColumnMap $column
	 * @param array $options
	 */
	protected function addColumn(ColumnMap $column, array &$options) {
		$options[self::OPT_VALUE] = $column->getDefaultValue();
		$options[self::OPT_TYPE] = $column->getType();
		if ($options[self::OPT_TYPE] === PropelTypes::ENUM) {
			$options[self::OPT_EXTRA] = $column->getValueSet();
		} else {
			$options[self::OPT_EXTRA] = $column->getSize();
		}
		if ($column->isForeignKey()) {
			$options[self::OPT_RELATED] = [];
			$this->addColumns($column->getRelatedTable()->getColumns(), $options[self::OPT_RELATED]);
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
			self::OPT_VALUE => null,
			self::OPT_TYPE => null,
			self::OPT_EXTRA => null,
			self::OPT_RELATED => null
		];
	}

	/**
	 * Makes columns visible. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return Component Chainable
	 */
	function show(array $columns = null) {
		$this->apply($columns ? : $this->columns, [self::OPT_VISIBLE => true]);
		return $this;
	}

	/**
	 * Hides columns. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return Component Chainable
	 */
	function hide(array $columns = null) {
		$this->apply($columns ? : $this->columns, [self::OPT_VISIBLE => false]);
		return $this;
	}

	/**
	 * Enable the columns to be sent to the client. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return Component
	 */
	function allow(array $columns = null) {
		$this->apply($columns ? : $this->columns, [self::OPT_ACCESS => true]);
		return $this;
	}

	/**
	 * Disables the columns from being sent to the client. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return Component
	 */
	function deny(array $columns = null) {
		$this->apply($columns ? : $this->columns, [self::OPT_ACCESS => false]);
		return $this;
	}

	/**
	 * Sets the default values.
	 * @param array|ActiveRecordInterface $columns
	 * @return Component
	 */
	function setDefaultValues($columns) {
		if ($columns instanceof ActiveRecordInterface) {
			$columns = $columns->toArray();
		}
		$this->applyTo($columns, self::OPT_VALUE);
		return $this;
	}

	/**
	 * Applies an options object to each of the selected columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @param array $options
	 * @return Component
	 */
	function apply(array $columns, array $options) {
		array_walk_recursive($columns, function(&$value) use ($options) {
			$value = $options;
		});

		$this->mergeIn($this->columns, $columns);
		return $this;
	}

	/**
	 * Sets the value for the $option key in the option object of the selected columns to the values
	 * given in the $columns array.
	 * @param array $columns Column names should be the keys of the array.
	 * @param string $option
	 * @return Component
	 */
	function applyTo(array $columns, $option) {
		array_walk_recursive($columns, function(&$value) use ($option) {
			$given = $value;
			$value = [];
			$value[$option] = $given;
		});

		$this->mergeIn($this->columns, $columns);
		return $this;
	}

	/**
	 * Essentially does a recursive merge taking into account the OPT_RELATED option.
	 * @param array $original The array to merge into.
	 * @param array $added The array to merge in.
	 */
	protected function mergeIn(array &$original, array $added) {
		foreach ($added as $key => $value) {
			if (is_array(current($value))) {
				$this->mergeIn($original[$key][self::OPT_RELATED], $value);
			} else {
				$original[$key] = array_merge($original[$key], $value);
			}
		}
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
		return Cache::add($this);
	}

	/**
	 * Gets the template as a string to be used to render the component.
	 */
	abstract function getTemplate();
}
