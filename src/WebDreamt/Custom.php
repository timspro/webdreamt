<?php

namespace WebDreamt;

class Custom extends \WebDreamt\Common\Store {

	private $dbHost = "localhost";
	private $dbName = "webdreamt";
	private $dbUsername = "root";
	private $dbPassword = "";

	/**
	 * @return \PDO A PDO instance to the database.
	 */
	function db() {
		return $this->factory(__FUNCTION__, function () {
					$database = $this->dbName;
					$server = $this->dbHost;
					$username = $this->dbUsername;
					$password = $this->dbPassword;

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
	 * @return \Cartalyst\Sentry\Sentry A sentry instance.
	 */
	function sentry() {
		return $this->factory(__FUNCTION__, function () {
					$capsule = new Capsule;
					$capsule->addConnection([
						'driver' => 'mysql',
						'host' => $this->dbHost,
						'database' => $this->dbName,
						'username' => $this->dbUsername,
						'password' => $this->dbPassword,
						'charset' => 'utf8',
						'collation' => 'utf8_unicode_ci',
					]);

					$capsule->bootEloquent();

					return Sentry::instance();
				});
	}

	/**
	 * Get an instance of Custom.
	 * @return Custom
	 */
	function a() {
		return parent::a();
	}

}
