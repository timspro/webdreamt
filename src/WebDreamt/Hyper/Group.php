<?php

namespace WebDreamt\Hyper;

class Group extends Component {

	protected $display = null;
	protected $htmlTag;
	protected $childHtmlTag;
	protected $childHtml = '';
	protected $childIdPrefix;

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
	 * Set the HTML for all children.
	 * @param string $childHtml
	 * @return self
	 */
	function setChildHtml($childHtml = '') {
		$this->childHtml = $childHtml;
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
	 * Set the child id prefix.
	 * @param string $childIdPrefix
	 * @return self
	 */
	function setChildIdPrefix($childIdPrefix = null) {
		$this->childIdPrefix = $childIdPrefix;
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
			echo '<' . $this->htmlTag . ' ' . $this->html . '>';
		}

		if ($this->htmlTag) {
			echo '</' . $this->htmlTag . '>';
		}
		return ob_get_clean();
	}

}
