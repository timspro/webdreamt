<?php
use WebDreamt\Box;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

namespace WebDreamt\Component;

/**
 * A class used to display iterable input.
 */
class Group extends Wrapper {

	protected $childId;

	/**
	 * Construct a Group. This sets the title to the plural form of the display component's title.
	 * This takes as input something that is iterable.
	 * @param Component $display
	 */
	function __construct(Component $display) {
		parent::__construct($display);
		$this->title = Box::now()->pluralize($this->getTitle());
	}

	/**
	 * Set the child id prefix.
	 * @param string $childId
	 * @return self
	 */
	function setChildId($childId = null) {
		$this->childId = $childId;
		return $this;
	}

	/**
	 * Get the child ID prefix.
	 * @return string
	 */
	function getChildId() {
		return $this->childId;
	}

	protected function renderMe($input = null, $included = null) {
		if (!$input) {
			return;
		}
		$display = $this->display;
		$oldHtml = $display->getHtml();
		foreach ($input as $key => $value) {
			$id = $this->getValueFromInput('id', $value);
			if ($this->childId && $id) {
				$display->setHtml($oldHtml . " id='" . $this->childId . "-$id'");
			}
			$display->render($value, static::class);
		}
		$display->setHtml($oldHtml);
	}

}
