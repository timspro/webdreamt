<?php

namespace WebDreamt\Hyper;

class Group extends Component {

	protected $display = null;
	protected $htmlTag;
	protected $afterOpening = '';
	protected $childHtmlTag;
	protected $childHtml = '';
	protected $childPrefix;
	protected $breaks = false;

	public function __construct($table, $htmlTag = 'div', $childHtmlTag = 'div') {
		parent::__construct($table);
		$this->htmlTag = $htmlTag;
		$this->childHtmlTag = $childHtmlTag;
	}

	/**
	 * Set the component to use to display children. Defaults to just assuming that the data
	 * is indexed. Can also provide a string, in which case that column of the input will be used.
	 * @param Component|string $display
	 * @return self
	 */
	function setDisplay($display = null) {
		$this->display = $display;
		return $this;
	}

	/**
	 * Set the HTML tag for the topmost element.
	 * @param string $htmlTag
	 * @return self
	 */
	function setHtmlTag($htmlTag = 'div') {
		$this->htmlTag = $htmlTag;
		return $this;
	}

	/**
	 * Set the child HTML tag.
	 * @param string $childHtmlTag
	 * @return self
	 */
	function setChildHtmlTag($childHtmlTag = 'div') {
		$this->childHtmlTag = $childHtmlTag;
		return $this;
	}

	/**
	 * Set the HTML for all children. Note that this can be function, in which case it will be passed
	 * the value of the current input, the index of the current input, and the current input.
	 * @param string|callable $childHtml
	 * @return self
	 */
	function setChildHtml($childHtml = '') {
		$this->childHtml = $childHtml;
		return $this;
	}

	/**
	 * Set the child id prefix.
	 * @param string $childPrefix
	 * @return self
	 */
	function setChildPrefix($childPrefix = null) {
		$this->childPrefix = $childPrefix;
		return $this;
	}

	/**
	 * Set whether there should be breaks (<br />) between child elements.
	 * @param boolean $breaks
	 * @return self
	 */
	function setBreaks($breaks = false) {
		$this->breaks = $breaks;
		return $this;
	}

	/**
	 * Set a string to go after the opening tag.
	 * @param string $after
	 * @return self
	 */
	function setAfterOpeningTag($after = '') {
		$this->afterOpening = $after;
		return $this;
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
		if ($this->htmlTag) {
			$visible = ($this->display && !$this->columns[$this->display][self::OPT_VISIBLE] ?
							'style="display:none"' : '');
			echo '<' . $this->htmlTag . ' ' . $this->html . " class='" . implode(" ", $this->classes) .
			"' $visible>\n";
		}
		echo $this->afterOpening;
		foreach ($input as $index => $value) {
			$id = ($this->childPrefix ? 'id="' . $this->childPrefix . '-' . $index . '"' : '');
			$startTag = '';
			$endTag = '';
			$show = true;
			if ($this->childHtmlTag) {
				if (is_callable($this->childHtml)) {
					$function = $this->childHtml;
					$child = $function($value, $index, $input);
				} else {
					$child = $this->childHtml;
				}
				$startTag = '<' . $this->childHtmlTag . ' ' . $child . " " . $id . ">";
			}

			$middle = '';
			if (is_string($this->display)) {
				$components = $this->renderLinked($this->display, $value);
				if ($components !== null) {
					$middle = $components;
				} else {
					$show = $this->columns[$this->display][self::OPT_ACCESS];
					$middle = $this->getValueFromInput($this->display, $value);
				}
			} else if ($this->display instanceof Component) {
				$middle = $this->display->render($value, static::class);
			} else {
				$middle = $value;
			}
			if ($this->childHtmlTag) {
				$endTag = '</' . $this->childHtmlTag . '>';
			}
			if ($this->breaks) {
				$middle .= '<br />';
			}
			if ($show) {
				echo $startTag . $middle . $endTag . "\n";
			}

			echo $this->renderExtra($value);
		}
		if ($this->htmlTag) {
			echo '</' . $this->htmlTag . ">\n";
		}
		return ob_get_clean();
	}

}
