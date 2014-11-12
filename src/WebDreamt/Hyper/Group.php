<?php

namespace WebDreamt\Hyper;

class Group extends Component {

	protected $displayColumn = null;

	/**
	 * Set the display column. Defaults to just assuming that the data is indexed.
	 * @param array $displayColumn
	 */
	function setDisplayColumn($displayColumn = null) {
		$this->displayColumn = $displayColumn;
	}

	/**
	 * A disabled function
	 * @param string $column
	 * @param Component|Resource $component
	 * @throws Exception
	 */
	function link($column, $component) {
		throw new Exception("Cannot link Select.");
	}

	function getTemplate() {
		ob_start();
		?>
		<ul <?= $this->html ?>>
			<?php
			if ($this->displayColumn) {
				echo '<% $column = "' . $displayColumn . '" %>';
				?>
				<%
				foreach ($input as $index => $row) {
					%>
	<li><%= $row[$column] %><li>
						<%
					}
					?>
					<?php
				} else {
					?>
					<%
					foreach ($input as $value) {
						%>
	<li><%= $value %></li>
					<%
				}
				?>
				<?php
			}
			?>
		</ul>
		<?php
		return ob_get_clean();
	}

}
