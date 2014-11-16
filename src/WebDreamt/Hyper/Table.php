<?php

namespace WebDreamt\Hyper;

/**
 * A class to easily render a table based on a database table.
 */
class Table extends Component {

	/**
	 * The header for the column.
	 */
	const OPT_HEADER = 'header';

	protected $hideHeaders = false;
	protected $showRowNumbers = false;
	protected $tableHtml = 'class="table"';
	protected $rowNumberHeader = '#';

	protected function addColumn(ColumnMap $column, array &$options) {
		parent::addColumn($column, $options);
		$options[self::OPT_HEADER] = static::spaceColumnName($column->getPhpName());
	}

	/**
	 * Sets the headers for the column.
	 * @param type $columns
	 * @return this
	 */
	function setHeaders($columns) {
		$this->merge($columns, self::OPT_HEADER);
		return $this;
	}

	/**
	 * If the parameter $hide is true, then the table won't have headers.
	 * @param boolean $hide
	 * @return this
	 */
	function hideHeaders($hide = false) {
		$this->hideHeaders = $hide;
		return $this;
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
					if ($options[self::OPT_ACCESS] && $options[self::OPT_VISIBLE]) {
						?>
						<th><?= $options[self::OPT_HEADER] ?></th>
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
						$value = isset($input[$column]) ? $input[$column] : $options[self::OPT_DEFAULT];
						$components = $this->renderLinked($column, $value);
						if ($components !== null) {
							echo $components;
							continue;
						}
						if ($options[self::OPT_ACCESS] && $options[self::OPT_VISIBLE]) {
							?>
							<td>
								<?= $value ?>
							</td>
							<?php
						}
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
