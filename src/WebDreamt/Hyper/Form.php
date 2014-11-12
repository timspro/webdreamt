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
	 * Gets the form template.
	 */
	function getTemplate($included = false) {
		ob_start();
		echo $this->getProtection();
		if (!$included) {
			?>
			<form <?= $this->html ?>>
				<?php
			} else {
				?>
				<div class="wd-subform">
					<?php
				}
				foreach ($this->columns as $column => $options) {
					if ($options[self::OPT_ACCESS]) {
						if (isset($this->linked[$options[$column]])) {
							foreach ($this->linked as $component) {
								echo '<% $new = isset($input["' . $column . '"] ? $input["' . $column . '"] : []  %>';
								echo '<% $code = ' . print_r($component->getTemplate(true), true) . ' %>';
								echo '<%= protect($new, $code) %>';
							}
							continue;
						}

						$label = $options[self::OPT_LABEL];
						$disabled = ($options[self::OPT_DISABLE] ? 'disabled' : '');
						$hidden = ($options[self::OPT_ACCESS] ? 'style="display:none"' : '');
						$value = 'value="<% isset($input["' . $column . '"] ? $input["' . $column . '"] : ';
						if ($options[self::OPT_DEFAULT]) {
							$value = '"' . $options[self::OPT_DEFAULT] . '" %>"';
						} else {
							$value = '"" %>"';
						}
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
							<label for='<?= $column ?>'><?= $spacedName ?></label>
							<?php
							if (!isset($select)) {
								$attributes = "name='$label' $disabled $extra $value";
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
