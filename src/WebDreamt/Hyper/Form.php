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
	 * Indicates if the form can handle multiple items.
	 * @var boolean
	 */
	protected $multiple = false;
	/**
	 * A count of the number of forms rendered.
	 * @var int
	 */
	protected static $count = 0;

	protected function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options[self::OPT_DISABLE] = false;
		return $options;
	}

	protected function addColumn(ColumnMap $column, array &$options) {
		parent::addColumn($column, $options);
		$options[self::OPT_LABEL] = static::spaceColumnName($column->getName());
		if ($column->getName() === 'id') {
			$options[self::OPT_VISIBLE] = false;
		}
		if ($column->getName() === 'created_at' || $column->getName() === 'updated_at') {
			$options[self::OPT_ACCESS] = false;
		}
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
	 * Renders the component.
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 * @return string
	 */
	function render($input = null, $included = null) {
		ob_start();
		if ($this->input) {
			$input = $this->input;
		}
		static::$count++;
		$count = static::$count;
		if ($included === null) {
			?>
			<form <?= $this->html ?> class="<?= implode(" ", $this->classes) ?>">
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
						$value = $this->getValueFromInput($column, $input);

						$components = null;
						$selectComponent = null;
						if (isset($this->linked[$column])) {
							$components = '';
							foreach ($this->linked[$column] as $component) {
								if ($component instanceof Select) {
									$selectComponent = $component;
								} else {
									$components .= $component->render($input, static::class);
								}
							}
						}

						if ($components === null || $selectComponent !== null) {
							$name = $count . "-" . $this->tableName . "-" . $column;
							$label = ($selectComponent ?
											'Select ' . static::spaceColumnName($selectComponent->getTableName()) :
											$options[self::OPT_LABEL]);
							$disabled = $options[self::OPT_DISABLE] ? 'disabled=""' : '';
							$hidden = $options[self::OPT_VISIBLE] ? '' : 'style="display:none"';
							$extra = '';
							$classes = '';
							$select = false;
							$textarea = false;
							switch ($options[self::OPT_TYPE]) {
								case PropelTypes::LONGVARCHAR:
									$extra = 'size="' . $options[self::OPT_EXTRA] . '"';
									$textarea = true;
									break;
								case PropelTypes::VARCHAR:
									if (intval($options[self::OPT_EXTRA]) >= 255) {
										$textarea = true;
									} else {
										$type = 'text';
									}
									$extra = 'size="' . $options[self::OPT_EXTRA] . '"';
									break;
								case PropelTypes::INTEGER:
									$type = 'number';
									$extra = 'size="' . $options[self::OPT_EXTRA] . '"';
									break;
								case PropelTypes::FLOAT:
								case PropelTypes::DOUBLE:
								case PropelTypes::DECIMAL:
									$type = 'number';
									$extra = 'step="0.01"';
									break;
								case PropelTypes::BOOLEAN:
									$select = ['Yes', 'No'];
									break;
								case PropelTypes::ENUM:
									$select = $options[self::OPT_EXTRA];
									break;
								case PropelTypes::DATE:
									$type = 'text';
									if ($value) {
										$value = date("m-d-Y", strtotime($value));
									}
									$classes = 'date-control';
									break;
								case PropelTypes::TIMESTAMP:
									$type = 'text';
									if ($value) {
										$value = date("m-d-Y g:i a", strtotime($value));
									}
									$classes = 'datetime-control';
									break;
							}
							$attributes = "name='$name' $disabled $extra value='$value'";
							?>
							<div class='form-group' <?= $hidden ?>>
								<label for='<?= $name ?>'><?= $label ?></label>
								<?php
								if (isset($selectComponent)) {
									$selectComponent->appendHtml($attributes);
									echo $selectComponent->render($value);
								} else if ($select) {
									?>
									<select <?= $attributes ?>>
										<?php
										foreach ($select as $option) {
											?>
											<option><?= $option ?></option>
											<?php
										}
										?>
									</select>
									<?php
								} else if ($textarea) {
									?>
									<textarea class='form-control <?= $classes ?>' <?= $attributes ?>><?= $value ?></textarea>
									<?php
								} else {
									?>
									<input class='form-control <?= $classes ?>' type='<?= $type ?>' <?= $attributes ?>/>
									<?php
								}
								?>
							</div>
							<?php
						}

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
				?>
				<button type="submit" class="btn btn-default">Submit</button>
			</form>
			<?php
		}
		return ob_get_clean();
	}

}
