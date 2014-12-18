<?php

namespace WebDreamt\Hyper;

/**
 * A class used to display a Propel object or an array with key names of columns and values to display.
 */
class Display extends Group {

	/**
	 * This function is disabled.
	 * @param type $display
	 */
	function setDisplay($display = null) {
		throw new Exception("Cannot set a child component for a display.");
	}

	/**
	 * Set the child class prefix
	 * @param string $childPrefix
	 * @return self
	 */
	function setChildPrefix($childPrefix = null) {
		return parent::setChildPrefix($childPrefix);
	}

	/**
	 * Renders the Display component.
	 * @param array $input
	 * @param string $included
	 * @return string
	 */
	function renderChild($input = null, $included = null) {
		if ($this->htmlTag) {
			echo '<' . $this->htmlTag . ' ' . $this->html . " class='" . implode(" ", $this->classes) . "'>";
		}
		foreach ($this->columns as $column => $options) {
			if ($options[self::OPT_ACCESS]) {
				$value = $this->getValueFromInput($column, $input);
				$visible = ($options[self::OPT_VISIBLE] ? 'style="display:none"' : '');
				$class = ($this->childPrefix ? "class='" . $this->childPrefix . "-$column'" : '');
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
		echo $this->renderExtra($input);
		if ($this->htmlTag) {
			echo '</' . $this->htmlTag . '>';
		}
	}

}
