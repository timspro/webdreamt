<?php

namespace WebDreamt\Hyper;

use Propel\Runtime\Map\ColumnMap;

/**
 * A class to easily render a table based on a database table.
 */
class Table extends Component {

	protected $showRowNumbers = false;
	protected $rowNumberHeader = '#';
	protected $classes = ['table'];

	protected function addColumn(ColumnMap $column, array &$options) {
		parent::addColumn($column, $options);
		if ($column->getName() === 'id') {
			$options[self::OPT_VISIBLE] = false;
		}
	}

	/**
	 * If the parameters $show is true, then the table won't have row numbers.
	 * @param boolean $show
	 */
	function showRowNumbers($show = false) {
		$this->showRowNumbers = $show;
		return $this;
	}

	/**
	 * Sets the row number header. Default is '#'.
	 * @param string $rowNumberHeader Set the row number header.
	 */
	function setRowNumberHeader($rowNumberHeader = '#') {
		$this->rowNumberHeader = $rowNumberHeader;
	}

	/**
	 * Renders the component.
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 */
	function render($input = null, $included = null) {
		ob_start();
		if ($this->input) {
			$input = $this->input;
		}
		?>
		<table <?= $this->html ?> class="<?= implode(" ", $this->classes) ?>">
			<?php
			if ($this->showLabels) {
				?>
				<thead>
					<?php
					if ($this->showRowNumbers) {
						?>
					<th><?= $this->rowNumberHeader ?></th>
					<?php
				}
				?>
				<?php
				foreach ($this->columns as $column => $options) {
					if ($options[self::OPT_ACCESS]) {
						$visible = ($options[self::OPT_VISIBLE] ? '' : 'style="display:none"');
						?>
						<th <?= $visible ?>><?= $options[self::OPT_LABEL] ?></th>
						<?php
					}
				}
				?>
			</thead>
			<?php
		}
		?>
		<tbody>
			<?php
			foreach ($input as $index => $row) {
				?>
				<tr>
					<?php
					if ($this->showRowNumbers) {
						?>
						<td><?= ($index + 1) ?></td>
						<?php
					}
					foreach ($this->columns as $column => $options) {
						$value = $this->getValueFromInput($column, $row);
						$components = $this->renderLinked($column, $value);
						if ($components !== null) {
							$value = $components;
						}
						if ($options[self::OPT_ACCESS]) {
							$visible = ($options[self::OPT_VISIBLE] ? '' : 'style="display:none"');
							?>
							<td <?= $visible ?>><?= $value ?></td>
							<?php
						}
					}
					$extra = $this->renderExtra($row);
					if (!empty($extra)) {
						?>
						<td><?= $extra ?></td>
						<?php
					}
					?>
				</tr>
				<?php
			}
			?>
		</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

}
