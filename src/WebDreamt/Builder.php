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
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * A class useful for synchronizing the database with Propel and other dependencies.
 */
class Builder {

	public $PropelPHP;
	public $PropelProject;
	public $UserSchema;
	public $BuildSchema;
	public $ValidSchema;
	public $GeneratedSchema;
	public $GeneratedClasses;
	public $GeneratedMigrations;
	public $Vendor;
	/**
	 * A list of schemas to add to the database
	 * @var array
	 */
	private $registeredSchemas;
	/**
	 * A reference to the box
	 * @var type
	 */
	private $a;
	/**
	 * The propel command runner
	 * @var Application
	 */
	private $propel;
	private $propelOutput;

	/**
	 * Construct a Builder object.
	 * @param Box $box The settings object.
	 * @param array|string $schemas Any additional schemas to use for database creation.
	 */
	public function __construct(Box $box, $schemas = [], $baseDir = null) {
		umask(0);

		//Change how this is organized.
		$this->Vendor = $box->VendorDirectory;

		if (!$baseDir) {
			$this->PropelProject = $this->Vendor . "../db/Propel/";
		} else {
			if (!is_dir($baseDir)) {
				throw new Exception("$baseDir is not a directory.");
			}
			$this->PropelProject = $baseDir;
		}
		$this->PropelPHP = $this->PropelProject . "propel.php";
		$schemaDir = $this->Vendor . "../db/Schemas/";
		$this->UserSchema = $schemaDir . "schema.xml";
		$this->ValidSchema = $schemaDir . "validation.xml";
		$this->BuildSchema = $this->PropelProject . "schema.xml";
		$this->GeneratedSchema = $this->PropelProject . "generated-reversed-database/schema.xml";
		$this->GeneratedDatabase = $this->PropelProject . "generated-reversed-database/";
		$this->GeneratedClasses = $this->PropelProject . "generated-classes/";
		$this->GeneratedMigrations = $this->PropelProject . "generated-migrations/";

		$this->makeDir($this->Vendor . "../db/");
		$this->makeDir($this->PropelProject);
		$this->makeDir($schemaDir);
		if (!file_exists($this->ValidSchema)) {
			$xml = "<?xml version='1.0' encoding='UTF-8'?>\n<database>\n</database>\n";
			file_put_contents($this->ValidSchema, $xml);
		}

		if (!is_array($schemas)) {
			$schemas = [$schemas];
		}
		$this->registeredSchemas = $schemas;
		$this->a = $box;

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
		$this->propelOutput = new NullOutput();
		if (!($app instanceof Application)) {
			throw new Exception("Could not get the propel application.");
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
	 * updateDatabase() attempts to 1) Diff the status of the database with build.xml (formed from
	 * combining Schemas/build.xml and valid.xml), 2) Push any migrations created to the database,
	 * 3) Generate Propel classes.
	 * @throws Exception If propel command or project directory is not found.
	 */
	public function updateDatabase() {
		$this->generateSchemaXml();

		$project = $this->PropelProject;
		if (!file_exists($project)) {
			throw new Exception("Project directory does not exist at $project");
		}

		chdir($project);
		$this->propel->find("diff")->run(new ArrayInput(["command" => "diff"]), $this->propelOutput);
		$this->propel->find("migrate")->run(new ArrayInput([
			"command" => "migrate"
				]), $this->propelOutput);

		$this->generateModels();
	}

	/**
	 * Generates the models for Propel.
	 * @throws Exception If Propel/schema.xml is not found.
	 */
	private function generateModels() {
		$build = $this->BuildSchema;
		if (!file_exists($build)) {
			throw new Exception("Build schema does not exist at $build");
		}

		$this->removeDirectory($this->GeneratedClasses);
		chdir($this->PropelProject);
		$this->propel->find("model:build")->run(new ArrayInput([
			"command" => "model:build"
				]), $this->propelOutput);
	}

	/**
	 * Merges valid.xml and schema.xml in Schema directory to create Propel/schema.xml
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
			if (isset($data[$tableName])) {
				foreach ($data[$tableName] as $node) {
					$marker->appendChild($node);
				}
			}
		}

		//Save as the build schema.
		echo $schemaDom->save($this->BuildSchema);
	}

	/**
	 * Removes all files and directories in a directory if it exists.
	 * @param string $dirPath
	 */
	public function removeDirectory($dirPath) {
		if (\file_exists($dirPath)) {
			foreach (new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as
						$path) {
				$path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
			}
			//rmdir($dirPath);
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

}
