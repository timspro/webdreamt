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
	 * Renders the Display component.
	 * @param array $input
	 * @param string $included
	 * @return string
	 */
	function renderChildComponent($input = null, $included = null) {
		if ($this->htmlTag) {
			echo '<' . $this->htmlTag . ' ' . $this->html . " class='" . implode(" ", $this->class) . "'>";
		}
		echo $this->afterOpening;
		foreach ($this->columns as $column => $options) {
			echo $this->renderExtraComponents($column, $input);

			if ($options[self::OPT_ACCESS]) {
				$childTag = $this->childHtmlTag;
				if ($this->showLabels) {
					echo "<$childTag class='wd-display-label'>" . $options[self::OPT_LABEL] . "</$childTag>";
				}
				$value = $this->getValueFromInput($column, $input);
				$visible = ($options[self::OPT_VISIBLE] ? 'style="display:none"' : '');
				$class = ($this->cssPrefix ? "class='" . $this->cssPrefix . "-$column'" : '');
				echo "<$childTag " . $this->childHtml . " $class $visible>";
				$components = $this->renderLinkedComponents($column, $value);
				if ($components !== null) {
					echo $components;
				} else {
					echo $value;
				}
				echo "</$childTag>";
				if ($this->breaks) {
					echo '<br />';
				}
			}
		}
		echo $this->renderExtraComponents('', $input);
		echo $this->beforeClosing;
		if ($this->htmlTag) {
			echo '</' . $this->htmlTag . '>';
		}
	}

}
