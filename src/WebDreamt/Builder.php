<?php

namespace WebDreamt;

use DOMDocument;
use DOMNode;
use Exception;
use FilesystemIterator;
use PDO;
use Propel\Runtime\Propel;
use RecursiveIteratorIterator;
use ReflectionClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * A class useful for synchronizing the database with Propel and other dependencies.
 */
class Builder {

	/**
	 * The vendor folder
	 * @var string
	 */
	public $Vendor;
	/**
	 * The database folder
	 * @var string
	 */
	public $DB;
	/**
	 * The propel folder
	 * @var string
	 */
	public $PropelProject;
	/**
	 * The propel config file
	 * @var string
	 */
	public $PropelPHP;
	/**
	 * The schema folder
	 * @var string
	 */
	public $Schemas;
	/**
	 * The schema that the user can edit
	 * @var string
	 */
	public $UserSchema;
	/**
	 * The validation schema
	 * @var string
	 */
	public $ValidSchema;
	/**
	 * The schema that Propel uses
	 * @var string
	 */
	public $BuildSchema;
	/**
	 * The file where Propel puts a generated schema
	 * @var string
	 */
	public $GeneratedSchema;
	/**
	 * The Propel classes folder
	 * @var string
	 */
	public $GeneratedClasses;
	/**
	 * The Propel migrations folder
	 * @var string
	 */
	public $GeneratedMigrations;
	/**
	 * A list of schemas to add to the database
	 * @var array
	 */
	protected $registeredSchemas;
	/**
	 * A reference to the box
	 * @var Box
	 */
	protected $a;
	/**
	 * The propel command runner
	 * @var Application
	 */
	protected $propel;
	/**
	 * Output from propel commands
	 * @var ConsoleOutput
	 */
	protected $propelOutput;
	/**
	 * Indicates if the maps have been loaded with either loadAll() or loadMaps().
	 * @var boolean
	 */
	protected static $mapsLoaded = false;

	/**
	 * Construct a Builder object. Note that this will ensure/create a basic Propel setup, but
	 * will not modify the database or create any Propel classes.
	 * @param Box $box The settings object.
	 * @param array|string $schemas Any additional schemas to use for database creation.
	 * @param boolean $files If true, then will automatically make sure the needed folder and config
	 * files are in place.
	 * @param boolean $console If true, then will allow builder to issue commands to the Propel
	 * command line program.
	 */
	public function __construct(Box $box, $schemas = [], $files = true, $console = true) {
		umask(0);

		//Change how this is organized.
		$this->Vendor = $box->VendorDirectory;

		$this->DB = $this->Vendor . "../db/";
		$this->PropelProject = $this->Vendor . "../db/propel/";
		$this->PropelPHP = $this->PropelProject . "propel.php";
		$this->Schemas = $this->Vendor . "../db/schemas/";
		$this->Classes = $this->Vendor . '../db/classes/';
		$this->Old = $this->Vendor . '../db/old/';
		$this->UserSchema = $this->Schemas . "schema.xml";
		$this->ValidSchema = $this->Schemas . "validation.xml";
		$this->BuildSchema = $this->PropelProject . "schema.xml";
		$this->GeneratedSchema = $this->PropelProject . "generated-reversed-database/schema.xml";
		$this->GeneratedDatabase = $this->PropelProject . "generated-reversed-database/";
		$this->GeneratedClasses = $this->PropelProject . "generated-classes/";
		$this->GeneratedMigrations = $this->PropelProject . "generated-migrations/";

		if (!is_array($schemas)) {
			$schemas = [$schemas];
		}
		$this->registeredSchemas = $schemas;
		$this->a = $box;

		if ($console) {
			$finder = new Finder();
			$finder->files()->name('*.php')
					->in($this->Vendor . '/propel/propel/src/Propel/Generator/Command')->depth(0);
			$app = new Application('Propel', Propel::VERSION);
			foreach ($finder as $file) {
				$ns = '\\Propel\\Generator\\Command';
				$r = new ReflectionClass($ns . '\\' . $file->getBasename('.php'));
				if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
					$app->add($r->newInstance());
				}
			}

			$this->propel = $app;
			$this->propelOutput = new ConsoleOutput();
			if (!($app instanceof Application)) {
				throw new Exception("Could not get the propel application.");
			}
		}

		if ($files) {
			$this->setupFiles();
		}
	}

	/**
	 * Sets up the appropiate directory structure.
	 */
	public function setupFiles() {
		$this->makeDir($this->Vendor . "../db/");
		$this->makeDir($this->PropelProject);
		$this->makeDir($this->Schemas);
		$this->makeDir($this->Classes);
		$this->makeDir($this->Old);
		if (!file_exists($this->ValidSchema)) {
			$xml = "<?xml version='1.0' encoding='UTF-8'?>\n<database>\n</database>\n";
			file_put_contents($this->ValidSchema, $xml);
		}
		$propelPHP = array(
			'propel' =>
			array(
				'database' =>
				array(
					'connections' =>
					array(
						'default' =>
						array(
							'adapter' => 'mysql',
							'classname' => 'Propel\\Runtime\\Connection\\ConnectionWrapper',
							'dsn' => 'mysql:host=localhost;dbname=test',
							'user' => 'root',
							'password' => '',
							'attributes' =>
							array(
							),
						),
					),
				),
				'runtime' =>
				array(
					'defaultConnection' => 'default',
					'connections' =>
					array(
						0 => 'default',
					),
				),
				'generator' =>
				array(
					'defaultConnection' => 'default',
					'connections' =>
					array(
						0 => 'default',
					),
				),
			),
		);
		$default = &$propelPHP["propel"]["database"]["connections"]["default"];
		$default["dsn"] = "mysql:host=" . $this->a->DatabaseHost . ";dbname=" . $this->a->DatabaseName;
		$default["user"] = $this->a->DatabaseUsername;
		$default["password"] = $this->a->DatabasePassword;
		\file_put_contents($this->PropelPHP, "<?php\nreturn " . \var_export($propelPHP, true) . ";\n");

		chdir($this->PropelProject);
		$input = new ArrayInput(["command" => "config:convert"]);
		$this->propel->find("config:convert")->run($input, $this->propelOutput);
	}

	/**
	 * Creates the database if it doesn't exist. Does not add any tables.
	 */
	public function createDatabase() {
		$db = $this->a->db();
		$dbName = $this->a->DatabaseName;
		$db->exec("CREATE DATABASE IF NOT EXISTS $dbName; USE $dbName");
	}

	/**
	 * Given an EMPTY database, build() will attempt to 1) Insert the necessary tables for
	 * dependencies, 2) Build the Propel schema.xml file based off the dependency tables, 3) Build the
	 * corresponding Propel classes based off of schema.xml.
	 * @throws Exception If the Sentry schema is not found or if the database is not empty.
	 */
	public function build() {
		/**
		 * Commands of interest:
		 * propel model:build
		 * propel sql:build
		 * propel reverse "mysql:host=localhost;dbname=db;user=root;password=pwd"
		 */
		$schemas = $this->registeredSchemas;
		foreach ($schemas as $schema) {
			if (!file_exists($schema)) {
				throw new Exception("Registered schema does not exist at $schema");
			}
		}

		$this->createDatabase();

		$pdo = $this->a->db();
		$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		if (count($tables) !== 0) {
			throw new Exception("Database is not empty: " . implode(",", $tables));
		}

		foreach ($schemas as $schema) {
			$sql = file_get_contents($schema);
			$pdo->exec($sql);
		}

		$this->updatePropel();
	}

	/**
	 * Attempts to add the schems to the database.
	 */
	public function addSchemas() {
		foreach ($this->registeredSchemas as $schema) {
			$sql = file_get_contents($schema);
			$this->a->db()->exec($sql);
		}
	}

	/**
	 * Registers schemas to be potentially added.
	 * @param array $schemas
	 */
	public function registerSchemas($schemas = []) {
		if (!is_array($schemas)) {
			$schemas = [$schemas];
		}
		$this->registeredSchemas = $schemas;
	}

	/**
	 * updatePropel() attempts to 1) Build the schema.xml file based off of the
	 * current database (and merge that build with valid.xml),
	 * 2) Build the Propel classes corresponding to schema.xml.
	 * @throws Exception If the propel command, the Propel project directory, or the generated schema
	 * are not found.
	 */
	public function updatePropel() {
		$project = $this->PropelProject;
		if (!file_exists($project)) {
			throw new Exception("Project directory does not exist at $project");
		}

		$custom = $this->a;
		$host = $custom->DatabaseHost;
		$name = $custom->DatabaseName;
		$username = $custom->DatabaseUsername;
		$password = $custom->DatabasePassword;

		chdir($project);
		$this->propel->find("reverse")->run(new ArrayInput([
			"command" => "reverse",
			"connection" => "mysql:host=$host;dbname=$name;user=$username;password=$password"
				]), $this->propelOutput);

		$gen = $this->GeneratedSchema;
		if (!file_exists($gen)) {
			throw new Exception("Generated schema does not exist at $gen");
		}
		rename($gen, $this->UserSchema);

		$this->generateSchemaXml();
		$this->generateModels();
	}

	/**
	 * updateDatabase() attempts to 1) Diff the status of the database with Propel/schema.xml
	 * (formed from combining schemas/schema.xml and valid.xml), 2) Push any migrations created to
	 * the database, 3) Generate Propel classes. Note that the Propel migration files are deleted
	 * after they are used.
	 * @throws Exception If propel command or project directory is not found.
	 */
	public function updateDatabase() {
		$this->generateSchemaXml();

		$project = $this->PropelProject;
		if (!file_exists($project)) {
			throw new Exception("Project directory does not exist at $project");
		}

		$this->createDatabase();

		chdir($project);
		$this->propel->find("diff")->run(new ArrayInput(["command" => "diff"]), $this->propelOutput);
		$this->propel->find("migrate")->run(new ArrayInput([
			"command" => "migrate"
				]), $this->propelOutput);
		array_map('unlink', glob($this->GeneratedMigrations . "*") ? : []);

		$this->generateModels();
	}

	/**
	 * Generates the models for Propel.
	 * @throws Exception If propel/schema.xml is not found.
	 */
	private function generateModels() {
		$build = $this->BuildSchema;
		if (!file_exists($build)) {
			throw new Exception("Build schema does not exist at $build");
		}

		$this->removeDirectory($this->GeneratedClasses);
		chdir($this->PropelProject);
		$this->propel->find("build")->run(new ArrayInput([
			"command" => "build"
				]), $this->propelOutput);

		//Get rid of Propel classes that are no longer in the database.
		foreach (scandir($this->Classes) as $file) {
			$path = $this->Classes . $file;
			$check = !file_exists($this->GeneratedClasses . $file);
			if ($file !== '.' && $file !== ".." && is_file($path) && $check) {
				rename($path, $this->Old . $file);
			}
		}

		//Add new Propel classes to the classes folder.
		foreach (scandir($this->GeneratedClasses) as $file) {
			$path = $this->GeneratedClasses . $file;
			$check = substr($file, strlen($file) - 9) !== 'Query.php';
			if ($file !== '.' && $file !== ".." && is_file($path) && $check) {
				if (!file_exists($this->Classes . $file)) {
					rename($path, $this->Classes . $file);
				} else {
					unlink($path);
				}
			}
		}
	}

	/**
	 * Merges valid.xml and schema.xml in Schema directory to create propel/schema.xml
	 * Note this is also where a table is determined if it it should have isCrossRef="true".
	 * For this to occur, there needs to be two foreign keys that are part of the primary key.
	 * @throws Exception If user or valid schema is not found.
	 */
	private function generateSchemaXml() {
		$userSchema = $this->UserSchema;
		$validSchema = $this->ValidSchema;

		if (!file_exists($userSchema)) {
			throw new Exception("User schema does not exist at $userSchema");
		}
		if (!file_exists($validSchema)) {
			throw new Exception("Valid schema does not exist at $validSchema");
		}

		$schemaDom = new DOMDocument();
		$schemaDom->load($this->UserSchema);
		$validDom = new DOMDocument();
		$validDom->load($this->ValidSchema);

		$markers = $validDom->getElementsByTagName('table');
		//Get the validation rules.
		$data = [];
		/* @var $marker DOMNode */
		foreach ($markers as $marker) {
			$tableName = $marker->attributes->getNamedItem("name")->nodeValue;
			$data[$tableName] = $marker->childNodes;
		}

		//Add the validation rules to data schema.
		$markers = $schemaDom->getElementsByTagName('table');
		foreach ($markers as $marker) {
			$tableName = $marker->attributes->getNamedItem("name")->nodeValue;
			if (array_key_exists($tableName, $data)) {
				foreach ($data[$tableName] as $node) {
					$marker->appendChild($node);
				}
			}

			//http://propelorm.org/documentation/04-relationships.html
			//If there is no id column in the table and there are at least two foreign keys,
			//assume the table is a junction table and add isCrossRef="true" to the table.
			//Note that the foreign keys need to also be primary keys for Propel to work correctly.
			$hadId = false;
			$key = 0;
			/* @var $child DOMNode */
			foreach ($marker->childNodes as $child) {
				if ($child->attributes) {
					$name = $child->attributes->getNamedItem("name");
					$foreign = $child->attributes->getNamedItem("foreignTable");
					if ($name && $name->nodeValue === "id") {
						$hadId = true;
					}
					if ($foreign) {
						$key++;
					}
				}
			}
			if ($key >= 2 && !$hadId) {
				$marker->setAttribute("isCrossRef", "true");
			}
		}
		//Need to fix how enum is handled.
		$markers = (new \DOMXpath($schemaDom))->query("//*[starts-with(@sqlType,'enum(')]");
		foreach ($markers as $marker) {
			$values = $marker->getAttribute("sqlType");
			$values = substr($values, 5, strlen($values) - 1 - 5);
			$marker->setAttribute("size", "[" . $values . "]");
		}

		//Fix sentry table Propel names.
		$markers = (new \DOMXpath($schemaDom))->query("//*[@name='users_groups']");
		foreach ($markers as $marker) {
			$marker->setAttribute('phpName', 'UserGroup');
		}
		$markers = (new \DOMXpath($schemaDom))->query("//*[@name='users']");
		foreach ($markers as $marker) {
			$marker->setAttribute('phpName', 'User');
		}
		$markers = (new \DOMXpath($schemaDom))->query("//*[@name='groups']");
		foreach ($markers as $marker) {
			$marker->setAttribute('phpName', 'Group');
		}

		//Save as the build schema.
		echo $schemaDom->save($this->BuildSchema);
	}

	/**
	 * Removes all files and directories in a directory if it exists.
	 * @param string $dirPath
	 */
	public function removeDirectory($dirPath, $removeTop = false) {
		$dirPath = realpath($dirPath);
		if (\file_exists($dirPath)) {
			foreach (new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as
						$path) {
				$path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
			}
			if ($removeTop) {
				rmdir($dirPath);
			}
		}
	}

	/**
	 * Makes a directory if it doesn't exist already.
	 * @param string $dir
	 */
	public function makeDir($dir) {
		if (!file_exists($dir)) {
			mkdir($dir);
		}
	}

	/**
	 * Drops all tables in the database.
	 */
	public function deleteDatabase($dropDatabase = false) {
		$db = $this->a->db();
		$db->exec("SET FOREIGN_KEY_CHECKS=0");
		$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table) {
			$db->exec("DROP TABLE $table");
		}
		$db->exec("SET FOREIGN_KEY_CHECKS=1");
		if ($dropDatabase) {
			$database = $this->a->DatabaseName;
			$db->exec("DROP DATABASE $database");
		}
	}

	/**
	 * Deletes all the data in the database.
	 */
	public function deleteData() {
		$db = $this->a->db();
		$db->exec("SET FOREIGN_KEY_CHECKS=0");
		$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table) {
			$db->exec("TRUNCATE $table");
		}
		$db->exec("SET FOREIGN_KEY_CHECKS=1");
	}

	/**
	 * Load all generated classes. This is useful if you cannot autoload the classes for a
	 * certain script.
	 */
	public function loadAllClasses() {
		$filename = $this->GeneratedClasses . "Base/";
		if (!file_exists($filename)) {
			throw new Exception('There are no classes to load. Does the database have tables?');
		}
		static::loadAll($filename);
		static::loadAll($this->GeneratedClasses);
		static::loadAll($this->GeneratedClasses . "Map/");
		static::loadAll($this->Classes);
		static::$mapsLoaded = true;
	}

	/**
	 * Load all maps.
	 * @throws Exception Thrown if the map folder doesn't exist.
	 */
	static public function loadMaps() {
		if (static::$mapsLoaded) {
			return;
		}
		$filename = Box::get()->VendorDirectory . "../db/propel/generated-classes/Map/";
		if (!file_exists($filename)) {
			throw new Exception('There are no classes to load. Does the database have tables?');
		}
		static::loadAll($filename);
		static::$mapsLoaded = true;
	}

	/**
	 * Load all PHP files in a directory. Assumes directory has a trailing slash.
	 * @param string $directory
	 */
	protected static function loadAll($directory) {
		foreach (scandir($directory) as $file) {
			$require = $directory . $file;
			if ($file !== '.' && $file !== ".." && is_file($require)) {
				require_once $require;
			}
		}
	}

	/**
	 * Guarantees that the database has the latest schema changes and, by extension,
	 * guarantees the consistency of Propel with the database.
	 *
	 * Specifically, this compares the generated schema's file's timestamp with the normal schema's
	 * timestamp. If the generated schema's timestamp is older, then this means that the normal
	 * schema has been modified without synchronizing those modifications with the database
	 * (such as the case after a git pull). So, the function will call updateDatabase(), which will
	 * generate a new schema and update the database from that, and return true.
	 * If not, then returns false.
	 * @return boolean Indicates if the synchronization was carried out.
	 */
	public static function automate() {
		$box = Box::get();
		$generatedSchema = $box->VendorDirectory . "../db/schemas/schema.xml";
		if (!file_exists($generatedSchema)) {
			ob_start();
			$box->builder()->updatePropel();
			ob_get_clean();
			return true;
		} else
		if (filemtime($generatedSchema) >
				filemtime($box->VendorDirectory . "../db/propel/schema.xml")) {
			ob_start();
			$box->builder()->updateDatabase();
			ob_get_clean();
			return true;
		}
		return false;
	}

}
