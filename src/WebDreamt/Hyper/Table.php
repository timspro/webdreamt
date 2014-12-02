<?php

namespace WebDreamt\Hyper;

/**
 * A class to easily render a table based on a database table.
 */
class Table extends Component {

	protected $hideHeaders = false;
	protected $showRowNumbers = false;
	protected $tableHtml = 'class="table"';
	protected $rowNumberHeader = '#';

	protected function addColumn(ColumnMap $column, array &$options) {
		parent::addColumn($column, $options);
		$options[self::OPT_LABEL] = static::spaceColumnName($column->getPhpName());
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
	 * Sets HTML attributes for the table. You will likely want to include 'class="table"'.
	 * @param string $html Example: "class='table table-bordered'"
	 */
	function setHtml($html = 'class="table"') {
		$this->html = $html;
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
			if (!$this->hideHeaders) {
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
						$visible = ($options[self::OPT_VISIBLE] ? 'style="display:none"' : '');
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
						$value = $this->columns[$this->display][self::OPT_DEFAULT];
						$components = $this->renderLinked($column, $value);
						if ($components !== null) {
							$value = $components;
						}
						if ($options[self::OPT_ACCESS]) {
							$visible = ($options[self::OPT_VISIBLE] ? 'style="display:none"' : '');
							?>
							<td <?= $visible ?>>
								<?= $value ?>
							</td>
							<?php
						}
					}
					$extra = $this->renderExtra($input);
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
