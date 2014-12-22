<?php

namespace WebDreamt\Hyper;

use Propel\Runtime\Map\ColumnMap;

/**
 * A class to easily render a table based on a database table.
 */
class Table extends Columned {

	protected $showRowNumbers = false;
	protected $rowNumberHeader = '#';
	protected $classes = ['table'];
	/**
	 * Defaults to WRAP_PANEL.
	 * @var string
	 */
	protected $wrapper = self::WRAP_PANEL;

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
	 * Renders the Table component.
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 */
	protected function renderChildComponent($input = [], $included = null) {
		?>
		<table <?= $this->html ?> class="<?= implode(" ", $this->class) ?>">
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
						$cssPrefix = ($this->cssPrefix ? "class='" . $this->cssPrefix . "-$column'" : '');
						$visible = ($options[self::OPT_VISIBLE] ? '' : 'style="display:none"');
						?>
						<th <?= $visible . ' ' . $cssPrefix ?>><?= $options[self::OPT_LABEL] ?></th>
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
			echo $this->beforeOpening;
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
						echo $this->renderExtraComponents($column, $row);
						$value = $this->getValueFromInput($column, $row);
						$components = $this->renderLinkedComponents($column, $value);
						if ($components !== null) {
							$value = $components;
						}
						if ($options[self::OPT_ACCESS]) {
							$cssPrefix = ($this->cssPrefix ? "class='" . $this->cssPrefix . "-$column'" : '');
							$visible = ($options[self::OPT_VISIBLE] ? '' : 'style="display:none"');
							?>
							<td <?= $visible . ' ' . $cssPrefix ?>><?= $value ?></td>
							<?php
						}
					}
					$extra = $this->renderExtraComponents('', $row);
					if (!empty($extra)) {
						?>
						<td><?= $extra ?></td>
						<?php
					}
					?>
				</tr>
				<?php
			}
			echo $this->beforeClosing;
			?>
		</tbody>
		</table>
		<?php
	}

}
