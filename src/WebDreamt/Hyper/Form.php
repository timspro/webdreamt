<?php

namespace WebDreamt\Hyper;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\ColumnMap;

/**
 * A class to easily render a form for a table in the database.
 */
class Form extends Component {

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
	 * Indicates if the form can handle multiple items.
	 * @var boolean
	 */
	protected $multiple = false;
	/**
	 * A count of the number of forms rendered.
	 * @var int
	 */
	protected static $count = 0;
	/**
	 * A function to give control to change form inputs.
	 * @var callable
	 */
	protected $inputHook = null;

	protected function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options[self::OPT_DISABLE] = false;
		$options[self::OPT_REQUIRE] = false;
		$options[self::OPT_HTML_TYPE] = '';
		$options[self::OPT_HTML_CLASS] = '';
		$options[self::OPT_HTML_EXTRA] = '';
		return $options;
	}

	protected function addColumn(ColumnMap $column, array &$options) {
		parent::addColumn($column, $options);
		if ($column->getName() === 'id') {
			$options[self::OPT_VISIBLE] = false;
		}
		if ($column->getName() === 'created_at' || $column->getName() === 'updated_at') {
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
	 * Sets the HTML class string of the columns.
	 * @param array $columns
	 * @return this
	 */
	function setHtmlClass($columns) {
		$this->merge($columns, self::OPT_HTML_CLASS);
		return $this;
	}

	/**
	 * Sets the HTML type of the columns.
	 * @param array $columns
	 * @return this
	 */
	function setHtmlType($columns) {
		$this->merge($columns, self::OPT_HTML_TYPE);
		return $this;
	}

	/**
	 * Sets the extra HTML attributes of the columns.
	 * @param array $columns
	 * @return this
	 */
	function setHtmlExtra($columns) {
		$this->merge($columns, self::OPT_HTML_EXTRA);
		return $this;
	}

	/**
	 * Makes all columns required. Defaults to all columns.
	 * @param array $columns
	 * @return self
	 */
	function required(array $columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_REQUIRE, true);
		return $this;
	}

	/**
	 * Makes columns not required. Defaults to all columns.
	 * @param array $columns
	 * @return self
	 */
	function optional(array $columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_REQUIRE, false);
		return $this;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function disable(array $columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_DISABLE, false);
		return $this;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return self
	 */
	function enable(array $columns = null) {
		$this->apply(is_array($columns) ? $columns : func_get_args(), self::OPT_DISABLE, true);
		return $this;
	}

	/**
	 * Sets HTML attributes for the form. You will likely want to use "role='form'" in $html.
	 * @param string $html Example: "class='test' method='get' role='form'"
	 */
	function setHtml($html = "role='form'") {
		$this->html = $html;
	}

	/**
	 * Sets if the form should submit multiple items.
	 * @param boolean $multiple
	 * @return self
	 */
	function setMultiple($multiple = false) {
		$this->multiple = $multiple;
		return $this;
	}

	/**
	 * Sets a function that provides fine-grain control over the name, value, and possible values
	 * (for applicable inputs) of the HTML form input. The values passed to function are: (1) column name,
	 * (2) the options for the column, (3) the form input name, (4) the form value,
	 * (5) an array of possible values, the last three are passed by reference and so are modifiable.
	 * @param callable $function
	 * @return self
	 */
	function setInputHook($function = null) {
		$this->inputHook = $function;
		return $this;
	}

	/**
	 * Renders the component.
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 * @return string
	 */
	function renderChild($input = null, $included = null) {
		//Get an ID for the form.
		static::$count++;
		$count = static::$count;
		if ($included === null) {
			//Output the setup for the form.
			?>
			<form <?= $this->html ?> class="wd-form <?= implode(" ", $this->classes) ?>">
				<?php
			} else {
				?>
				<div <?= $this->html ?> class="wd-subform <?= implode(" ", $this->classes) ?>">
					<?php
				}
				?>
				<input type='hidden' name='<?= $count ?>' value='<?= $this->tableName ?>'/>
				<?php
				foreach ($this->columns as $column => $options) {
					if ($options[self::OPT_ACCESS]) {
						//Get the value for the given column.
						$value = $this->getValueFromInput($column, $input);
						//Get the output for any linked components. Note that we do something special for selects.
						$components = null;
						$selectComponent = null;
						if (isset($this->linked[$column])) {
							$components = '';
							foreach ($this->linked[$column] as $component) {
								if ($component instanceof Select) {
									$selectComponent = $component;
								} else {
									$components .= $component->render($value, static::class);
								}
							}
						}
						//If there are no linked components or if there is a Select component...
						if ($components === null || $selectComponent !== null) {
							$name = $count . "-" . $column;
							$label = ($selectComponent ? $selectComponent->getHeader() : $options[self::OPT_LABEL]);
							$hidden = $options[self::OPT_VISIBLE] ? '' : 'style="display:none"';
							$disabled = $options[self::OPT_DISABLE] ? 'disabled=""' : '';
							$required .= $options[self::OPT_REQUIRE] && $options[self::OPT_VISIBLE] ? 'required=""' : '';
							$type = $options[self::OPT_HTML_TYPE];
							$class = $options[self::OPT_HTML_CLASS];
							$extra = $options[self::OPT_HTML_EXTRA];
							$attributes = "name='$name' class='form-control $class' $disabled $required $extra";
							$possibleValues = '';
							if ($this->inputHook) {
								$function = $this->inputHook;
								$function($column, $options, &$name, &$value, &$possibleValues);
							}
							?>
							<div class='form-group' <?= $hidden ?>>
								<label for='<?= $name ?>'><?= $label ?></label>
								<?php
								if (isset($selectComponent)) {
									$selectComponent->appendHtml($attributes);
									echo $selectComponent->render($value);
								} else {
									switch ($type) {
										case self::HTML_NUMBER:
											break;
										case self::HTML_TEXT:
											echo "<input type='text' value='$value' $attributes />";
											break;
										case self::HTML_TEXTAREA:
											echo "<textarea $attributes>$value</textarea>";
											break;
										case self::HTML_BOOLEAN:
											$value = $value ? 'Yes' : 'No';
											$possibleValues = ['No', 'Yes'];
										case self::HTML_SELECT:
											?>
											<select class="form-control" <?= $attributes ?>>
												<?php
												$possibleValues = $possibleValues ? : $options[self::OPT_EXTRA];
												foreach ($possibleValues as $option) {
													$selected = $value === $option ? 'selected=""' : '';
													?>
													<option <?= $selected ?>><?= $option ?></option>
													<?php
												}
												?>
											</select>
											<?php
											break;
									}
								}
								?>
							</div>
							<?php
						}
						//Output the components
						if ($components !== null) {
							echo $components;
							continue;
						}
					}
				}
				if ($this->multiple) {
					?>
					<button type='button' class="btn btn-default">Add Another</button>
					<?php
				}
				echo $this->renderExtra($input);
				if ($included) {
					?>
				</div>
				<?php
			} else {
				if ($this->wrapper !== self::WRAP_MODAL) {
					?>
					<button type="submit" class="btn btn-default">Submit</button>
					<?php
				} else {
					$this->setButtons(["btn-primary wd-submit" => 'Submit']);
				}
				?>
				<input type='hidden' class='next-form-id' value='<?= static::$count + 1 ?>' />
			</form>
			<?php
		}
	}

}
