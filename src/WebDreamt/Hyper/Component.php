<?php

namespace WebDreamt\Hyper;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use ReflectionClass;
use WebDreamt\Cache;

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
	protected $columns;
	/**
	 * An array where the keys are column names and the values are arrays with values
	 * that are Components.
	 * @var array
	 */
	protected $linked;
	/**
	 * An array which is just the $values parameter of the constructor. This represents values
	 * that are not known ahead of time and are accessed by the renderer.
	 * @var array
	 */
	protected $values;
	/**
	 * HTML attributes for the top level element.
	 * @var string
	 */
	protected $html = '';
	/**
	 * The filename for the cached object
	 * @var string
	 */
	public $cachedFilename;

	/**
	 * Constructs a component.
	 * @param TableMap $table
	 * @param array|ActiveRecordInterface|ActiveRecordInterface[] $values
	 */
	function __construct(TableMap $table, $values = []) {
		$this->columns = [];
		$this->linked = [];
		if ($values instanceof ActiveRecordInterface) {
			$this->values = $values->toArray(TableMap::TYPE_COLNAME);
		} else if (!empty($values) && $values[0] instanceof ActiveRecordInterface) {
			$this->values = $values->toArray(TableMap::TYPE_COLNAME);
		} else {
			$this->values = $values;
		}
		foreach ($table->getColumns() as $column) {
			$name = $column->getName();
			$this->columns[$name] = $this->getDefaultOptions();
			$this->addColumn($column, $this->columns[$name]);
		}
	}

	/**
	 * Link a column value with another Component.
	 * @param string $column Can be an empty string, which means that the component will
	 *
	 * @param Component|Resource $component
	 */
	function link($column, $component) {
		if (!isset($this->linked[$column])) {
			$this->linked[$column] = [];
		}
		$this->linked[$column][] = $component;
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
	 * Returns the options available for the current class.
	 */
	protected function getAllOptions() {
		if (!static::$options) {
			$class = new ReflectionClass(static::class);
			foreach ($class->getConstants() as $constant) {
				$option = substr($constant, 0, 3);
				if ($option === 'OPT') {
					static::$options[$option] = true;
				}
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
	 * Gets the values given to the component.
	 * @return array
	 */
	function getValues() {
		return $this->values;
	}

	/**
	 * Sets the HTML of the top level element.
	 * @param string $html
	 * @return this
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
	 * Makes columns visible. Defaults to all columns.
	 * @param array $columns Column names should be the values of the array.
	 * @return self
	 */
	function show($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_VISIBLE, true);
		return $this;
	}

	/**
	 * Hides columns. Defaults to all columns.
	 * @param array $columns Column names should be the values of the array.
	 * @return self
	 */
	function hide($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_VISIBLE, false);
		return $this;
	}

	/**
	 * Enable the columns to be sent to the client. Defaults to all columns.
	 * @param array $columns Column names should be the values of the array.
	 * @return self
	 */
	function allow($columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_ACCESS, true);
		return $this;
	}

	/**
	 * Disables the columns from being sent to the client. Defaults to all columns.
	 * @param array $columns Column names should be the values of the array.
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
	 * Renders the form with the given values and options.
	 * @return string
	 */
	function __toString() {
		//The cache will take care of the rendering.
		return Cache::add($this);
	}

	/**
	 * Returns a function within which child components can run other components.
	 * @return string
	 */
	protected function getProtection() {
		return '<% function protect($input, $code) {	eval("?>" . $code . "<?php"; } %>';
	}

	/**
	 * Gets the template as a string to be used to render the component.
	 * @param boolean $included Indicates if the template is included from another template.
	 * Defaults to false.
	 * @return string
	 */
	abstract function getTemplate($included = false);
	/**
	 * Spaces out a column name
	 * @param string $name
	 * @return string
	 */
	static protected function spaceColumnName($name) {
		return ucwords(str_replace('_', ' ', $name));
	}

}
