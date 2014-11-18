<?php

namespace WebDreamt\Hyper;

class Group extends Component {

	protected $display = null;
	protected $htmlTag;
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
	 * is indexed.
	 * @param Component|string $display
	 * @return self
	 */
	function setChildComponent($display = null) {
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
	 * Set the HTML for all children.
	 * @param string $childHtml
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
	 * Renders the component.
	 * @param array $input
	 * @param string $included The class name of the component that is calling render. Null
	 * if not being called from a component.
	 */
	function render($input = null, $included = null) {
		ob_start();
		if ($this->htmlTag) {
			echo '<' . $this->htmlTag . ' ' . $this->html . " class='" . implode(" ", $this->classes) . "'>";
		}
		foreach ($input as $index => $value) {
			$id = ($this->childPrefix ? 'id="' . $this->childPrefix . '-' . $index . '"' : '');
			$startTag = '';
			$endTag = '';
			$show = true;
			if ($this->childHtmlTag) {
				$visible = ($this->columns[$this->display][self::OPT_VISIBLE] ? 'style="display:none"' : '');
				$startTag = '<' . $this->childHtmlTag . ' ' . $this->childHtml . " " . $id . " $visible>";
			}

			$middle = '';
			if (is_string($this->display)) {
				$components = $this->renderLinked($this->display, $value);
				if ($components !== null) {
					$middle = $components;
				} else {
					$show = $this->columns[$this->display][self::OPT_ACCESS];
					$middle = (isset($value[$this->display]) ? $value[$this->display] :
									$this->columns[$this->display][self::OPT_DEFAULT]);
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
				echo $startTag . $middle . $endTag;
			}
		}
		if ($this->htmlTag) {
			echo '</' . $this->htmlTag . '>';
		}
		return ob_get_clean();
	}

}
