<?php

namespace WebDreamt;

use Cartalyst\Sentry\Facades\Native\Sentry as SentryNative;
use Cartalyst\Sentry\Sentry as Sentry;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;
use Propel\Common\Pluralizer\StandardEnglishPluralizer;

/**
 * A class to store objects that are configured via constant values or expression.
 */
class Box {

	/**
	 * @var Box
	 */
	static protected $box;
	public $DatabaseHost = "localhost";
	public $DatabaseName = "";
	public $DatabaseUsername = "root";
	public $DatabasePassword = "";
	/**
	 * The vendor directory in the project.
	 * @var string
	 */
	public $VendorDirectory;

	/**
	 * Constructs a Box.
	 * @param boolean $guarantee If true, then calls Builder::guarantee which helps to maintain
	 * consistency between the Propel and the database. Defaults to true. Use false when its unnecessary
	 * to peform this check, such as when not using the database.
	 */
	function __construct($guarantee = true) {
		$this->VendorDirectory = (\file_exists(__DIR__ . '/../../vendor/') ?
						__DIR__ . '/../../vendor/' : __DIR__ . '/../../../../');
		if ($guarantee) {
			if (!self::$box) {
				self::$box = $this;
			}
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
	 * Pluralizes a string using Propel's pluralizer.
	 * @param string $string
	 * @return string
	 */
	function pluralize($string) {
		if ($string === null || $string === '') {
			return '';
		}
		$pluralizer = $this->factory(__FUNCTION__, function() {
			return new StandardEnglishPluralizer();
		});
		return $pluralizer->getPluralForm($string);
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
	 *
	 * @return Script A script instance
	 */
	function script() {
		return $this->factory(__FUNCTION__, function() {
					return new Script($this);
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
		if (!property_exists($this, $name)) {
			$this->$name = $initializer();
		}
		return $this->$name;
	}

	/**
	 * Get an instance of Box.
	 * @return Box
	 */
	static function get() {
		return static::$box;
	}

	/**
	 * Get an instance of Box or create a new one that does not guarantee consistency between the
	 * database and Propel (and so has lower overhead).
	 * @return Box
	 */
	static function now() {
		return static::$box ? : new Box(false);
	}

	/**
	 * Returns an HTML header. Defaults to opening html and body tags and a complete head.
	 * @return string
	 */
	public function header() {
		return '
			<html lang="en">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>WebDreamt</title> ' .
				$this->css() . '
			</head>
			<body style="background-color: #e3e3e3; padding: 50px;">';
	}

	/**
	 * Returns the link tags representing the CSS.
	 * @return string
	 */
	public function css() {
		return '<link href="' . $this->root() . '/dist/build.min.css" rel="stylesheet">';
	}

	/**
	 * Returns an HTML footer. Defaults to script tags and closing html and closing body tags.
	 * @return string
	 */
	public function footer() {
		return '
		</body>
		</html>';
	}

	/**
	 * Returns the script tags representing the javascript.
	 * @return string
	 */
	public function javascript() {
		return '<script src="' . $this->root() . '/dist/build.js"></script>';
	}

}
