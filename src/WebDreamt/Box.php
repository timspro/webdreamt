<?php

namespace WebDreamt;

use PDO;
use Cartalyst\Sentry\Facades\Native\Sentry as Sentry;
use Illuminate\Database\Capsule\Manager as Capsule;
use WebDreamt\Common\Store as Store;

/**
 * A class to store objects that are configured via constant values.
 */
class Box extends Store {

	public $DatabaseHost = "localhost";
	public $DatabaseName = "";
	public $DatabaseUsername = "root";
	public $DatabasePassword = "";

	/**
	 * @return PDO A PDO instance to the database.
	 */
	function db() {
		return $this->factory(__FUNCTION__, function () {
					$database = $this->DatabaseName;
					$server = $this->DatabaseHost;
					$username = $this->DatabaseUsername;
					$password = $this->DatabasePassword;

					if (empty($database)) {
						$configure = "mysql:host=$server";
					} else {
						$configure = "mysql:host=$server;dbname=$database";
					}
					$pdo = new PDO($configure, $username, $password);
					$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					return $pdo;
				});
	}

	/**
	 *
	 * @return Build
	 */
	function build() {
		return $this->factory(__FUNCTION__, function () {
					return new Build($this, __DIR__ . "/../../vendor/cartalyst/sentry/schema/mysql.sql");
				});
	}

	/**
	 * @return Sentry A sentry instance.
	 */
	function sentry() {
		return $this->factory(__FUNCTION__, function () {
					$capsule = new Capsule;
					$capsule->addConnection([
						'driver' => 'mysql',
						'host' => $this->DatabaseHost,
						'database' => $this->DatabaseName,
						'username' => $this->DatabaseUsername,
						'password' => $this->DatabasePassword,
						'charset' => 'utf8',
						'collation' => 'utf8_unicode_ci',
					]);

					$capsule->bootEloquent();

					return Sentry::instance();
				});
	}

	/**
	 * Get an instance of Box.
	 * @return Box
	 */
	public static function a() {
		return parent::a();
	}

}
