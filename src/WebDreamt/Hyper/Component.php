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
	 * Indicates the component should go in a modal.
	 */
	const WRAP_MODAL = 'modal';
	/**
	 * Indicates the component should go in a panel.
	 */
	const WRAP_PANEL = 'panel';

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
	 * Indicates if the component should be put in a wrapper and what kind of wrapper.
	 * @var string
	 */
	protected $wrapper = self::WRAP_PANEL;
	/**
	 * The header to use for the wrapper
	 * @var string
	 */
	protected $header = '';
	/**
	 * Any buttons to add to the modal.
	 * @var array
	 */
	protected $buttons = [];
	/**
	 * The time format
	 * @var string
	 */
	protected $timeFormat = "g:i a";
	/**
	 * The date time format
	 * @var string
	 */
	protected $dateTimeFormat = "g:i a, m/d/y";
	/**
	 * The date format
	 * @var string
	 */
	protected $dateFormat = 'm/d/y';

	/**
	 * Constructs a component. Note that the provided table name can be null, in which case the
	 * this constructor will not set any members (so they will be a variation of empty), but such
	 * a setting might not make sense for the child component.
	 * @param string $tableName
	 */
	function __construct($tableName = null) {
		if ($tableName) {
			$table = Propel::getDatabaseMap()->getTable($tableName);
			//Keep a reference to the table map so when something is linked, we can look up the linked table's
			//information.
			$this->tableMap = $table;
			$this->tableName = $tableName;
			$this->header = static::convertName($tableName);
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
		$options[self::OPT_DEFAULT] = $column->getDefaultValue();
		$options[self::OPT_TYPE] = $column->getType();
		//Set up enum.
		if ($options[self::OPT_TYPE] === PropelTypes::ENUM) {
			$options[self::OPT_EXTRA] = $column->getValueSet();
		} else {
			$options[self::OPT_EXTRA] = $column->getSize();
		}
		//Set label.
		$options[self::OPT_LABEL] = static::convertName($column->getName());
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
	 * Set if a wrapper HTML should be used for the component.
	 * @param boolean $wrapper
	 * @return self
	 */
	function setWrapper($wrapper = true) {
		$this->wrapper = $wrapper;
		return $this;
	}

	/**
	 * Add buttons that will be rendered with the modal, if a modal is set as the wrapper HTML element.
	 * @param array $buttons The keys should be a strings of class names and the value should be the
	 * text for the button.
	 */
	function addButtons($buttons) {
		$this->buttons = array_merge($this->buttons, $buttons);
		return $this;
	}

	/**
	 * Set the header for the modal/panel. Note that has no effect if setWrapper is called with false/null
	 * when the Component is rendered. Default depends on the component but is generally based on the
	 * table's name.
	 * @param string $text
	 * @return self
	 */
	function setHeader($text) {
		$this->header = $text;
		return $this;
	}

	/**
	 * Get the header for the component.
	 * @return string
	 */
	function getHeader() {
		return $this->header;
	}

	/**
	 * Sets the time format used by the component.
	 * @param string $format
	 * @return self
	 */
	function setTimeFormat($format = 'g:i a') {
		$this->timeFormat = $format;
		return $this;
	}

	/**
	 * Sets the date time format used by the component.
	 * @param string $format
	 * @return self
	 */
	function setDateTimeFormat($format = 'g:i a, m-d-y') {
		$this->dateTimeFormat = $format;
		return $this;
	}

	/**
	 * Sets the date format used by the component.
	 * @param string $format
	 * @return self
	 */
	function setDateFormat($format = 'm-d-y') {
		$this->dateFormat = $format;
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
	 * Reorders the columns of the Component. The numerical indexes of the passed array are used as
	 * the guaranteed positions of the columns in the new ordering. Any gaps in the new ordering are
	 * filled in with unspecified columns in the old ordering. Thus, if we had ['a', 'b', 'c', 'd', 'e']
	 * as columns and passed [2 => 'a', 0 => 'e', 4 => 'd'] we would have ['e', 'b', 'a', 'c', 'd'].
	 * @param array $columns The key is the index of the column in the new list of columns. The value
	 * is the column name.
	 */
	function order($columns = []) {
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
	}

	/**
	 * Allows one to specify a different name to use for a column.
	 * Note that this will also change the label for the column unless $changeLabel is set to false.
	 * @param array $oldToNewColumns The key should be the old column name and the value should be the
	 * new column name.
	 */
	function alias($oldToNewColumns = [], $changeLabel = true) {
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
	function render($input = null, $included = null) {
		if ($this->input) {
			$input = $this->input;
		}
		ob_start();
		switch ($this->wrapper) {
			case self::WRAP_PANEL:
				?>
				<div class="panel panel-default">
					<div class="panel-heading"><?= $this->header ?></div>
					<div class="panel-body">
						<?php $this->renderChild() ?>
					</div>
				</div>
				<?php
				break;
			case self::WRAP_MODAL:
				?>
				<div class="modal fade">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
								</button>
								<h4 class="modal-title"><?= $this->header ?></h4>
							</div>
							<div class="modal-body">
								<?php $this->renderChild() ?>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<?php
								foreach ($this->buttons as $class => $text) {
									?>
									<button type="button" class="btn <?= $class ?>"><?= $text ?></button>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<?php
				break;
			default:
				$this->renderChild();
		}
		return ob_get_clean();
	}

	/**
	 * Renders the Child component. Child classes should implement this method. Note that the child
	 * class can
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 */
	abstract protected function renderChild($input = null, $included = null);
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
	 * Gets a value from input. Note that if the $value is a DateTime object, converts it to a string
	 * in the format specifed by either the $dateTimeFormat, $timeFormat, or $dateFormat members.
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
					$type = $this->columns[$column][self::OPT_TYPE];
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
		if (isset($this->columns[$column][self::OPT_DEFAULT])) {
			return $this->columns[$column][self::OPT_DEFAULT];
		}
		return '';
	}

	/**
	 * Changes underscores into spaces in a column or table name and capitalizes it.
	 * Also, changes ' Id' to ' ID' if the string is the last part of the resulting name.
	 * @param string $name
	 * @return string
	 */
	static protected function convertName($name) {
		$return = ucwords(str_replace('_', ' ', $name));
		if (substr($return, -3) === ' Id') {
			$return = substr($return, 0, strlen($return) - 2) . 'ID';
		}
		return $return;
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
