<?php

namespace WebDreamt\Hyper;

/**
 * A class used to display an array/collection. You will likely want to call setDisplay() providing
 * either a (1) Component or a (2) column name. If you give setDisplay() a column name, you may want to
 * link the column to a component by using link(). Note that the former way will pass as input the
 * "row" of the array/collection and the latter way will get the "value" for the column from the
 * current input.
 */
class Group extends Component {

	protected $display = null;
	protected $htmlTag;
	protected $afterOpening = '';
	protected $childHtmlTag;
	protected $childHtml = '';
	protected $childPrefix;
	protected $breaks = false;

	/**
	 * Construct a Group.
	 * @param string $tableName the name of the table to use.
	 * @param string $htmlTag The HTML tag used to represent the collection.
	 * @param string $childHtmlTag The HTML tag used to represent the children collection.
	 */
	public function __construct($tableName = null, $htmlTag = 'div', $childHtmlTag = 'div') {
		parent::__construct($tableName);
		$this->htmlTag = $htmlTag;
		$this->childHtmlTag = $childHtmlTag;
	}

	/**
	 * Set the component to use to display children. Defaults to just assuming that the data
	 * is an array of strings. Can also provide a string, in which case that column of the input
	 * will be used. This is especially useful with link();
	 * @param Component|string $display
	 * @return self
	 */
	function setDisplay($display = null) {
		$this->display = $display;
		return $this;
	}

	/**
	 * Set the HTML tag for the topmost element. Can be null, in which case no tag is displayed.
	 * @param string $htmlTag
	 * @return self
	 */
	function setHtmlTag($htmlTag = 'div') {
		$this->htmlTag = $htmlTag;
		return $this;
	}

	/**
	 * Set the child HTML tag. Can be null, in which case no tag is displayed.
	 * @param string $childHtmlTag
	 * @return self
	 */
	function setChildHtmlTag($childHtmlTag = 'div') {
		$this->childHtmlTag = $childHtmlTag;
		return $this;
	}

	/**
	 * Set the HTML for all children. Note that this can be function, in which case it will be passed
	 * the value of the current input, the array index of the current input, and the current input.
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
	function renderChild($input = [], $included = null) {
		if ($this->htmlTag) {
			$visible = '';
			if ($this->display && !$this->columns[$this->display][self::OPT_VISIBLE]) {
				$visible = 'style="display:none"';
			}
			$classes = implode(" ", $this->classes);
			echo '<' . $this->htmlTag . ' ' . $this->html . " class='$classes' $visible>\n";
		}
		echo $this->afterOpening;
		foreach ($input as $index => $row) {
			$id = $this->childPrefix ? "id='" . $this->childPrefix . "-$index'" : '';
			$startTag = '';
			$endTag = '';
			$show = true;
			$value = is_string($this->display) ? $this->getValueFromInput($this->display, $row) : $row;
			if ($this->childHtmlTag) {
				if (is_callable($this->childHtml)) {
					$function = $this->childHtml;
					$child = $function($value, $index, $input);
				} else {
					$child = $this->childHtml;
				}
				$startTag = '<' . $this->childHtmlTag . " $child $id>";
			}

			$middle = '';
			if (is_string($this->display)) {
				$components = $this->renderLinked($this->display, $value);
				if ($components !== null) {
					$middle = $components;
				} else {
					$show = $this->columns[$this->display][self::OPT_ACCESS];
					$middle = $value;
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

			echo $this->renderExtra($row);
		}
		if ($this->htmlTag) {
			echo '</' . $this->htmlTag . ">\n";
		}
	}

}
