<?php

namespace WebDreamt\Hyper;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\TableMap;

/**
 * A class that wraps a propel object and allows it to be accessed with array techniques.
 */
class PropelWrapper {

	protected $record;

	/**
	 * Constructs a PropelWrapper for either a propel object or a container of propel objects.
	 * Note that __get and __isset are implemented. __get will return a PropelWrapper object if
	 * applicable.
	 * @param ActiveRecordInterface|ActiveRecordInterface[] $record
	 */
	public function __construct($record) {
		$this->record = $record;
	}

	public function __get($name) {
		if (is_int($name)) {
			$return = $this->record[$name];
		} else {
			$return = $this->record->getByName($name, TableMap::TYPE_COLNAME);
		}
		if ($return instanceof ActiveRecordInterface ||
				(is_array($return) && !empty($return) && $return[0] instanceof ActiveRecordInterface)) {
			return new PropelWrapper($return);
		}
		return $return;
	}

	public function __isset($name) {
		if (is_int($name)) {
			return $this->record[$name];
		}
		return isset($this->record->getByName($name, TableMap::TYPE_COLNAME));
	}

}
