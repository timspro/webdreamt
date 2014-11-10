<?php

namespace WebDreamt\Automatic;

/**
 * A class to easily render a form for a table in the database.
 */
class Form extends Component {

	const OPT_DISABLE = 'disable';

	protected $formHtml;

	protected function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options[self::OPT_DISABLE] = false;
		return $options;
	}

	protected function getDefaultRelation() {
		$relation = parent::getDefaultRelation();
		$relation[self::REL_SEARCH] = true;
		$relation[self::REL_SEARCH_TABLE] = null;
		return $relation;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return Component Chainable
	 */
	function disable(array $columns = null) {
		$this->apply($columns ? : $this->columns, [self::OPT_DISABLE => true]);
		return $this;
	}

	/**
	 * Makes columns disable. Defaults to all columns.
	 * @param array $columns Column names should be the keys of the array.
	 * @return Component Chainable
	 */
	function enable(array $columns = null) {
		$this->apply($columns ? : $this->columns, [self::OPT_DISABLE => false]);
		return $this;
	}

	/**
	 *
	 * @param string $html
	 */
	function setFormHtml($html) {
		$this->formHtml = $html;
	}

	/**
	 * Gets the form template.
	 */
	function getTemplate() {
		ob_start();
		?>
		<form role='form' <?= $this->formHTML ?>>
			<?php
			$this->outputColumns($this->columns);
			?>
			<button type="submit" class="btn btn-default">Submit</button>
		</form>
		<?php
		return ob_end_flush();
	}

	/**
	 * Outputs part of the form for the given columns.
	 * @param array $columns
	 */
	protected function outputColumns($columns) {
		foreach ($columns as $column => $options) {
			if (self::isRelation($options)) {
				?>
				<div class='col-md-10'>
					<?php
					$this->outputColumns($options[self::REL_COLUMNS]);
					?>
				</div>
				<?php
			}
			if ($options[self::OPT_ACCESS]) {
				$spacedName = preg_replace('/(?<! )(?<!^)[A-Z]/', ' $0', $column);
				$disabled = ($options[self::OPT_DISABLE] ? 'disabled' : '');
				$hidden = ($options[self::OPT_ACCESS] ? 'style="display:none"' : '');
				?>
				<div class='form-group' <?= $hidden ?>>
					<label for='<?= $column ?>'><?= $spacedName ?></label>
					<input class='form-control' type='text' name='<?= $column ?>' <?= $disabled ?>/>
				</div>
				<?php
			}
		}
	}

}
