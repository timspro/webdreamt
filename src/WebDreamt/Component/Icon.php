<?php

namespace WebDreamt\Component;

use WebDreamt\Component;

/**
 * A class that represents icons.
 */
class Icon extends Component {

	/**
	 * An edit icon.
	 */
	const TYPE_EDIT = 'update';
	/**
	 * A delete icon.
	 */
	const TYPE_DELETE = 'delete';

	/**
	 * Indicates the type of the icon.
	 * @var string
	 */
	protected $type;

	/**
	 * Construct a new icon.
	 * @param string $type
	 * @param string $htmlTag
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct($type, $htmlTag = 'span', $class = null, $html = null, $input = null) {
		switch ($type) {
			case static::TYPE_DELETE:
				$class .= ' glyphicon-remove wd-remove-icon';
				break;
			case static::TYPE_EDIT:
				$class .= ' glyphicon-edit';
				break;
		}
		$this->type = $type;
		$class .= ' glyphicon';
		$html .= ' aria-hidden="true"';
		parent::__construct($htmlTag, $class, $html, $input);
	}

	/**
	 * Get the type of the icon.
	 * @return string
	 */
	function getType() {
		return $this->type;
	}

	/**
	 * This simply returns '' as the icon doesn't/shouldn't need to use input.
	 * @param mixed $input
	 * @param Component $included
	 * @return string
	 */
	function renderInput($input = null, Component $included = null) {
		return '';
	}

}
