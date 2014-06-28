<?php

namespace WebDreamt\Common;

use \WebDreamt\Common\Object;

/**
 * Settings contains configuration details.
 */
class Settings extends Object {
	/* @var $global Settings */

	private static $global;
	/* @var $parameters array */
	private $parameters;
	/* @var $db PDO */
	private $db;
	// Configuration file.
	private static $configuration;

	/**
	 * Reads settings.ini and starts a database connection.
	 * @throws \Exception If cannot find settings.ini
	 */
	public function __construct() {
		if (file_exists(static::$configuration)) {
			$this->parameters = parse_ini_file(static::$configuration, true);
			$parameters = $this->parameters['Database'];
			//We shouldn't need to keep track of database information, so delete it.
			unset($this->parameters['Database']);

			$this->db = new \PDO('mysql:host=' . $parameters['db_server'] . ';dbname=' . $parameters['db_'
					. 'database'] . ';charset=utf8', $parameters['db_username'], $parameters['db_password']);
			$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			if (!isset(static::$global)) {
				static::$global = $this;
			}
		} else {
			throw new \Exception("Settings: Could not find Settings.ini.");
		}
	}

	/**
	 * Gets an instance of the PDO object.
	 * @return \PDO
	 */
	public function PDO() {
		return $this->db;
	}

	/**
	 * Gets the configuration parameters.
	 * @param string Section for parameters.
	 * @return array
	 */
	public function getSetup($section) {
		return $this->parameters[$section];
	}

	/**
	 * Gets a static reference to the first declared Settings class.
	 * @return Settings
	 */
	public static function get() {
		return parent::get();
	}

	/**
	 * Declares class constants. Does not need to be called.
	 */
	public static function init() {
		static::$configuration = __DIR__ . "/Settings.ini";
	}

}

Settings::init();
