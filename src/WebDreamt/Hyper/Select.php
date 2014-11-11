<?php

namespace WebDreamt\Hyper;

class Select extends Component {

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
		<select <?= $this->html ?>>
			<?php
			if ($this->displayColumn) {
				echo '<% $column = "' . $this->displayColumn . '" %>';
				?>
				<%
				foreach ($input as $index => $input) {
					%>
	<option><%= $input[$index][$column] %><option>
						<%
					}
					?>
					<?php
				} else {
					?>
					<%
					foreach ($input as $index => $input) {
						%>
	<option><%= $input[$index] %></option>
					<%
				}
				?>
				<?php
			}
			?>
		</select>
		<?php
		return ob_get_clean();
	}

}
