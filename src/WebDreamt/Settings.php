<?php

namespace WebDreamt;

require_once __DIR__ . "/../../vendor/autoload.php";

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Cartalyst\Sentry\Facades\Native\Sentry as Sentry;

class Settings {

	static private $db_name = "webdreamt";
	static private $db_host = "localhost";
	static private $db_username = "root";
	static private $db_password = "";
	static private $sentry;
	static private $pdo;

	static public function getDbName() {
		return self::$db_name;
	}

	static public function getDbHost() {
		return self::$db_host;
	}

	static public function getDbUsername() {
		return self::$db_username;
	}

	static public function getDbPassword() {
		return self::$db_password;
	}

	/**
	 *
	 * @return \Cartalyst\Sentry\Sentry
	 */
	static public function sentry() {
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
		if (!isset(self::$pdo)) {
			$host = self::$db_host;
			$name = self::$db_name;
			$username = self::$db_username;
			$password = self::$db_password;
			$pdo = new \PDO("mysql:host=$host;dbname=$name;charset=utf8", $username, $password);
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			self::$pdo = $pdo;
		}
		return self::$pdo;
	}

	static public function init() {
		self::$pdo = null;
		self::$sentry = null;
	}

}

Settings::init();
