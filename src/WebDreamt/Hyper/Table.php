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

	function getTemplate() {
		ob_start();
		echo $this->getProtection();
		?>
		<table <?= $this->html ?>>
			<?php
			if (!$this->hideHeaders) {
				?>
				<thead>
					<?= '<th>' . $this->rowNumberHeader . '</th>' ?>
					<?php
					foreach ($this->columns as $column) {
						?>
					<th>
						<?=
						($column[self::OPT_ACCESS] && $column[self::OPT_VISIBLE]) ?
								$column[self::OPT_HEADER] : ''
						?>
					</th>
					<?php
				}
				?>
			</thead>
			<?php
		}
		?>
		<tbody>
			<?php
			$columns = [];
			foreach ($this->columns as $name => $column) {
				if ($column[self::OPT_ACCESS] && $column[self::OPT_VISIBLE]) {
					$columns[$name] = $column;
				}
			}
			echo '<% $columns = ' . print_r($columns, true) . ' %>';
			$linked = [];
			foreach ($this->linked as $name => $component) {
				$linked[$name] = $component->getTemplate(true);
			}
			echo '<% $linked = ' . print_r($linked, true) . ' %>';
			?>
			<%
			foreach ($input as $index => $input) {
				%>
	<tr>
					<?php
					echo $this->showRowNumbers ? '<%= $index %>' : '';
					?>
					<%
					foreach ($columns as $column) {
						%>
		<td>
							<%
							if (isset($input[$index][$column])) {
								if (isset($linked[$column])) {
									echo protect($input[$index][$column], $linked[$column]);
								} else {
									echo $input[$index][$column];
								}
							}
							%>
		</td>
						<%
					}
					%>
	</tr>
				<%
			}
			%>
</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

}
