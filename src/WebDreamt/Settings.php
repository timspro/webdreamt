<?php

namespace WebDreamt;

require_once __DIR__ . "/../../vendor/autoload.php";

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Cartalyst\Sentry\Facades\Native\Sentry as Sentry;

class Settings {

	static private $self;
	static private $db_name = "webdreamt";
	static private $db_host = "localhost";
	static private $db_username = "root";
	static private $db_password = "";
	static private $sentry;
	static private $pdo;

	/**
	 * Initialization code.
	 */
	private function __construct() {
		self::$self = $this;
	}

	static public function getDBName() {
		return self::$db_name;
	}

	static public function getDBHost() {
		return self::$db_host;
	}

	static public function getDBUsername() {
		return self::$db_username;
	}

	static public function getDBPassword() {
		return self::$db_password;
	}

	/**
	 *
	 * @return \Cartalyst\Sentry\Sentry
	 */
	static public function sentry() {
		if (!isset(self::$self)) {
			new Settings;
		}
		if (!isset(self::$sentry)) {
			$capsule = new Capsule;
			$capsule->addConnection([
				'driver' => 'mysql',
				'host' => self::$db_host,
				'database' => self::$db_name,
				'username' => self::$db_username,
				'password' => self::$db_password,
				'charset' => 'utf8',
				'collation' => 'utf8_unicode_ci',
			]);

			$capsule->bootEloquent();

			self::$sentry = Sentry::instance();
		}
		return self::$sentry;
	}

	/**
	 *
	 * @return \PDO
	 */
	static public function pdo() {
		if (!isset(self::$self)) {
			new Settings;
		}
		if (!isset(self::$pdo)) {
			self::$pdo = new \PDO('mysql:host=' . self::$db_host . ';dbname=' . self::$db_name .
					';charset=utf8', self::$db_username, self::$db_password);
			self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		return self::$pdo;
	}

}
