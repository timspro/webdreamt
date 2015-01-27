<?php

namespace WebDreamt\Component;

use WebDreamt\Component;

class Icon extends Component {

	/**
	 * An edit icon.
	 */
	const TYPE_EDIT = 'edit';
	/**
	 * A delete icon.
	 */
	const TYPE_DELETE = 'delete';

	/**
	 * The URL for the icon.
	 * @var string
	 */
	protected $url;
	/**
	 * Indicates if the URL is to use AJAX.
	 * @var boolean
	 */
	protected $ajax;
	/**
	 * Indicates the type of the icon.
	 * @var string
	 */
	protected $type;

	function __construct($type, $htmlTag = 'span', $class = null, $html = null, $input = null) {
		switch ($type) {
			case static::TYPE_DELETE:
				$class .= ' glyphicon-remove';
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

	function renderSpecial($input = null, Component $included = null) {
		return '';
	}

}
