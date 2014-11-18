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
	 * The label for the input
	 */
	const OPT_LABEL = 'label';

	/**
	 * See setFormHtml()
	 * @var string
	 */
	protected $formHtml = "role='form'";
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
		$options[self::OPT_LABEL] = static::spaceColumnName($column->getPhpName());
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
	 * Sets the labels.
	 * @param array $columns
	 * @return this
	 */
	function setLabels($columns) {
		$this->merge($columns, self::OPT_LABEL);
		return $this;
	}

	/**
	 * Renders the component.
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 */
	function render($input = null, $included = null) {
		ob_start();
		static::$count++;
		if ($included === null) {
			?>
			<form <?= $this->html ?> class="<?= implode(" ", $this->classes) ?>">
				<?php
			} else {
				?>
				<div class="wd-subform">
					<?php
				}
				foreach ($this->columns as $column => $options) {
					if ($options[self::OPT_ACCESS]) {
						$value = isset($input[$column]) ? $input[$column] : $options[self::OPT_DEFAULT];
						$components = $this->renderLinked($column, $value);
						if ($components !== null) {
							echo $components;
							continue;
						}

						$name = static::$count . "." . $this->tableName . "." . $column;
						$label = $options[self::OPT_LABEL];
						$disabled = $options[self::OPT_DISABLE] ? 'disabled' : '';
						$hidden = $options[self::OPT_ACCESS] ? 'style="display:none"' : '';
						switch ($options[self::OPT_TYPE]) {
							case PropelTypes::LONGVARCHAR:
							case PropelTypes::VARCHAR:
								$type = 'text';
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
							case PropelTypes::BOOLEAN;
								$select = ['Yes', 'No'];
								break;
							case PropelTypes::ENUM:
								$select = $options[self::OPT_EXTRA];
								break;
						}
						?>
						<div class='form-group' <?= $hidden ?>>
							<label for='<?= $name ?>'><?= $label ?></label>
							<?php
							if (!isset($select)) {
								$attributes = "name='$name' $disabled $extra value='$value'";
								?>
								<input class='form-control' type='<?= $type ?>' <?= $attributes ?>/>
								<?php
							} else {
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
							}
							?>
						</div>
						<?php
					}
				}
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
		return ob_end_flush();
	}

}
