<?php

namespace WebDreamt;

use Propel\Runtime\Map\TableMap;
use WebDreamt\Automatic\Component;
use WebDreamt\Automatic\Form;

class Cache {

	private $vendor;

	function __construct(Box $box) {
		$this->vendor = $box->VendorDirectory;
	}

	/**
	 * Gets a new form.
	 * @param type $filename
	 * @param type $key
	 * @param type $className
	 * @param type $values
	 * @return Form
	 */
	function form($filename, $key, $className, $values = null) {
		return $this->find($filename, $key, __FUNCTION__, $values) ? :
				new Form($this->getTableMap($classname), $values);
	}

	function table() {

	}

	/**
	 *
	 * @param type $className
	 * @return TableMap
	 */
	protected function getTableMap($className) {
		$map = "\\Map\\" . $className . "TableMap";
		$map::buildTableMap();
		return $map::getTableMap();
	}

	/**
	 * Using the parameters provided, tries to find the component in the cache.
	 * If successful
	 * @param type $function
	 * @param type $filename
	 * @param type $key
	 */
	protected function find($filename, $key, $function) {

	}

	/**
	 * Caches the component and returns the rendered template as a string.
	 * @param Component $component
	 * @return string
	 */
	static function add(Component $component) {
		$component->getTemplate()
	}

}
