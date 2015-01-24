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
	 * An instance of the Box that can be accessed statically
	 * @var Box
	 */
	static protected $box;
	/**
	 * The database server's host name
	 * @var string
	 */
	public $DatabaseHost = "localhost";
	/**
	 * The database's name
	 * @var string
	 */
	public $DatabaseName = "";
	/**
	 * The username used to log into the database
	 * @var string
	 */
	public $DatabaseUsername = "root";
	/**
	 * The password used to log into the database
	 * @var string
	 */
	public $DatabasePassword = "";
	/**
	 * The vendor directory in the project.
	 * @var string
	 */
	public $VendorDirectory;
	/**
	 * If false, then builder won't setup the Propel directories and config files.
	 * @var boolean
	 */
	public $BuilderFiles = true;
	/**
	 * If false, then builder won't be able to issue Propel commands.
	 * @var boolean
	 */
	public $BuilderConsole = true;

	/**
	 * Construct a Box.
	 */
	function __construct() {
		$this->VendorDirectory = (\file_exists(__DIR__ . '/../../vendor/') ?
						__DIR__ . '/../../vendor/' : __DIR__ . '/../../../../');
		if (!static::$box) {
			static::$box = $this;
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
					$build = new Builder($this, [$schema, $fk], $this->BuilderFiles, $this->BuilderConsole);
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
	 * Require the propel configuration file.
	 */
	function enable() {
		Builder::automate($this);
		require_once $this->VendorDirectory . "../db/propel/generated-conf/config.php";
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
	 * @return static
	 */
	static function get() {
		if (static::$box === null) {
			static::$box = new static();
		}
		return static::$box;
	}

	/**
	 * Returns an HTML header. Defaults to opening html and body tags and a complete head.
	 * @param boolean $css Add results from css()
	 * @param string $title The page title
	 * @param function $custom A function to use to output custom CSS
	 * @return string
	 */
	public function header($css = true, $title = '', $custom = null) {
		if ($custom) {
			ob_start();
			$custom();
			$custom = ob_get_clean();
		}
		return '
			<html lang="en">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>' . $title . '</title> ' .
				($css ? $this->css() : '') . "\n" .
				$custom . '
			</head>
			<body style="background-color: #e3e3e3; padding: 50px;">';
	}

	/**
	 * Returns the link tags representing the CSS.
	 * @return string
	 */
	public function css() {
		return '<link href="' . $this->root() . '/dist/client/webdreamt-build.min.css" rel="stylesheet">';
	}

	/**
	 * Returns an HTML footer. Defaults to script tags and closing html and closing body tags.
	 * @param boolean $javascript Add results from javascript().
	 * @param function $custom A function to use to output custom HTML.
	 * @return string
	 */
	public function footer($javascript = true, $custom = null) {
		if ($custom) {
			ob_start();
			$custom();
			$custom = ob_get_clean();
		}
		return ($javascript ? $this->javascript() : '') . "\n" .
				$custom . '
		</body>
		</html>';
	}

	/**
	 * Returns the script tags representing the javascript.
	 * @return string
	 */
	public function javascript() {
		return '<script src="' . $this->root() . '/dist/client/webdreamt-build.js"></script>';
	}

}
