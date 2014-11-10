<?php
use Propel\Runtime\Map\TableMap;
use WebDreamt\Automatic\Component;
use WebDreamt\Automatic\Form;
use WebDreamt\Box;
use WebDreamt\Cache\Resource;

namespace WebDreamt;

class Cache {

	private $vendor;

	/**
	 * Constructs the Cache.
	 * @param Box $box
	 */
	function __construct(Box $box) {
		$this->vendor = $box->VendorDirectory;
	}

	/**
	 * Gets a new form.
	 * @param string $filename
	 * @param string $key
	 * @param string $className
	 * @param array $values
	 * @return Form
	 */
	function form($filename, $key, $className, $values = null) {
		return $this->find($filename, $key, __FUNCTION__, $values) ? :
				new Form($this->getTableMap($classname), $values);
	}

	/**
	 *
	 * @param string $filename
	 * @param string $key
	 * @param string $className
	 * @param string $values
	 */
	function table($filename, $key, $className, $values) {

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
	 * @param string $filename
	 * @param string $key
	 * @param string $function
	 */
	protected function find($filename, $key, $function) {

	}

	/**
	 * Caches the component and returns the rendered template as a string.
	 * @param Component $component
	 * @return string
	 */
	static function add(Component $component) {
		$component->getTemplate();
		new Cache\Resource();
	}

}
