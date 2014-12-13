<?php

namespace WebDreamt;

use Cartalyst\Sentry\Facades\Native\Sentry as SentryNative;
use Cartalyst\Sentry\Sentry as Sentry;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;

/**
 * A class to store objects that are configured via constant values or expression.
 */
class Box {

	/**
	 * @var Box
	 */
	static private $box;
	public $DatabaseHost = "localhost";
	public $DatabaseName = "";
	public $DatabaseUsername = "root";
	public $DatabasePassword = "";
	public $VendorDirectory;

	/**
	 * Constructs a Box.
	 * @param boolean $guarantee If true, then calls Builder::guarantee which helps to maintain
	 * consistency between the Propel and the database. Defaults to true.
	 */
	function __construct($guarantee = true) {
		$this->VendorDirectory = (\file_exists(__DIR__ . '/../../vendor/') ?
						__DIR__ . '/../../vendor/' : __DIR__ . '/../../../../');
		if (!self::$box) {
			self::$box = $this;
		}
		if ($guarantee) {
			Builder::guarantee($this);
		}
	}

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
	 * @return Builder A builder instance
	 */
	function builder() {
		return $this->factory(__FUNCTION__, function () {
					$schema = $this->VendorDirectory . "cartalyst/sentry/schema/mysql.sql";
					$fk = __DIR__ . '/Builder/sentry.sql';
					$build = new Builder($this, [$schema, $fk]);
					return $build;
				});
	}

	/**
	 *
	 * @return Filler A filler instance
	 */
	function filler() {
		return $this->factory(__FUNCTION__, function() {
					return new Filler($this);
				});
	}

	/**
	 * @return Sentry A sentry instance
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

					return SentryNative::instance();
				});
	}

	/**
	 * @return Server A server instance
	 */
	function server() {
		return $this->factory(__FUNCTION__, function() {
					return new Server($this);
				});
	}

	/**
	 *
	 * @return Router A router instance
	 */
	function router() {
		return $this->factory(__FUNCTION__, function() {
					return new Router($this);
				});
	}

	function javascript() {

	}

	/**
	 * Gets the web root URL.
	 * @return string
	 */
	function root() {
		return $this->factory(__FUNCTION__, function() {
					return substr(realpath($this->VendorDirectory . "../"), strlen($_SERVER['DOCUMENT_ROOT']));
				});
	}

	/**
	 * Checks to see if the property is defined. If not, then will use the initializer to construct
	 * one.
	 * @param string $name The property name
	 * @param callable $initializer The function to use for initialization
	 * @return mixed
	 */
	protected function factory($name, callable $initializer) {
		if (!isset($this->$name)) {
			$this->$name = $initializer();
		}
		return $this->$name;
	}

	/**
	 * Get an instance of Box.
	 * @return Box
	 */
	public static function a() {
		return self::$box;
	}

}
