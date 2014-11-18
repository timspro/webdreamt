<?php

namespace WebDreamt\Hyper;

class Display extends Group {

	/**
	 * This function is disabled.
	 * @param type $display
	 */
	function setChildComponent($display = null) {
		throw new Exception("Cannot set a child component for a display.");
	}

	function render($input = null, $included = null) {
		ob_start();
		if ($this->htmlTag) {
			echo '<' . $this->htmlTag . ' ' . $this->html . " class='" . implode(" ", $this->classes) . "'>";
		}
		foreach ($this->columns as $column => $options) {
			if ($options[self::OPT_ACCESS]) {
				$value = (isset($input[$column]) ? $input[$column] : $options[self::OPT_DEFAULT]);
				$visible = ($options[self::OPT_VISIBLE] ? 'style="display:none"' : '');
				$class = ($this->childPrefix ? "class='" . $this->childPrefix . "_$column'" : '');
				echo '<' . $this->childHtmlTag . ' ' . $this->childHtml . " $class $visible>";
				$components = $this->renderLinked($column, $value);
				if ($components !== null) {
					echo $components;
				} else {
					echo $value;
				}
				echo '</' . $this->childHtmlTag . '>';
			}
		}
		if ($this->htmlTag) {
			echo '</' . $this->htmlTag . '>';
		}
		return ob_get_clean();
	}

}
