<?php

namespace WebDreamt;

use DOMDocument;
use DOMNode;
use Exception;

/**
 * A class useful for synchronizing the database with Propel and other dependencies.
 */
class Build {

	private $propelProjectDirectory;
	private $propelCommandPath;
	private $userSchema;
	private $buildSchema;
	private $validSchema;
	private $generatedSchema;
	private $generatedClasses;
	private $generatedMigrations;
	private $registeredSchemas;
	private $a;

	/**
	 * Construct a Build object.
	 * @param Box $custom The settings object.
	 * @param array|string $schemas Any additional schemas to use for database creation.
	 */
	public function __construct(Box $custom, $schemas) {
		$this->propelCommandPath = __DIR__ . "/../../vendor/bin/propel";
		$this->propelProjectDirectory = __DIR__ . "/Propel";
		$this->userSchema = __DIR__ . "/Schemas/schema.xml";
		$this->validSchema = __DIR__ . "/Schemas/valid.xml";
		$this->buildSchema = __DIR__ . "/Propel/schema.xml";
		$this->generatedSchema = __DIR__ . "/Propel/generated-reversed-database/schema.xml";
		$this->generatedDatabase = __DIR__ . "/Propel/generated-reversed-database/";
		$this->generatedClasses = __DIR__ . "/Propel/generated-classes/";
		$this->generatedMigrations = __DIR__ . "/Propel/generated-migrations/";

		if (!is_array($schemas)) {
			$schemas = [$schemas];
		}
		$this->registeredSchemas = $schemas;
		$this->a = $custom;
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
		$tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
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
	 * updatePropel() attempts to 1) Build the schema.xml file based off of the
	 * current database (and merge that build with valid.xml),
	 * 2) Build the Propel classes corresponding to schema.xml.
	 * @throws Exception If the propel command, the Propel project directory, or the generated schema
	 * are not found.
	 */
	public function updatePropel() {
		$project = $this->propelProjectDirectory;
		$cd = "cd " . $project;
		$propel = $this->propelCommandPath;

		if (!file_exists($propel)) {
			throw new Exception("Propel command does not exist at $propel");
		}
		if (!file_exists($project)) {
			throw new Exception("Project directory does not exist at $project");
		}

		$custom = $this->a;
		$host = $custom->get("dbHost");
		$name = $custom->get("dbName");
		$username = $custom->get("dbUsername");
		$password = $custom->get("dbPassword");
		$command = "$cd; $propel reverse \"mysql:host=$host;dbname=$name;" .
				"user=$username;password=$password\"";
		shell_exec($command);
		$gen = $this->generatedSchema;
		if (!file_exists($gen)) {
			throw new Exception("Generated schema does not exist at $gen");
		}
		rename($gen, $this->userSchema);

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

		$propel = $this->propelCommandPath;
		if (!file_exists($propel)) {
			throw new Exception("Propel command does not exist at $propel");
		}
		$project = $this->propelProjectDirectory;
		if (!file_exists($project)) {
			throw new Exception("Project directory does not exist at $project");
		}

		$cd = "cd " . $project;
		shell_exec("$cd; $propel diff; $propel migrate");

		$this->generateModels();
	}

	/**
	 * Generates the models for Propel.
	 * @throws Exception If Propel/schema.xml is not found.
	 */
	private function generateModels() {
		$build = $this->buildSchema;
		if (!file_exists($build)) {
			throw new Exception("Build schema does not exist at $build");
		}

		$propel = $this->propelCommandPath;
		$cd = "cd " . $this->propelProjectDirectory;

		shell_exec("rm -rf " . $this->generatedClasses);
		shell_exec("$cd; $propel model:build");
	}

	/**
	 * Merges valid.xml and schema.xml in Schema directory to create Propel/schema.xml
	 * @throws Exception If user or valid schema is not found.
	 */
	private function generateSchemaXml() {
		$userSchema = $this->userSchema;
		$validSchema = $this->validSchema;

		if (!file_exists($userSchema)) {
			throw new Exception("User schema does not exist at $userSchema");
		}
		if (!file_exists($validSchema)) {
			throw new Exception("Valid schema does not exist at $validSchema");
		}

		$schemaDom = new DOMDocument();
		$schemaDom->load($this->userSchema);
		$validDom = new DOMDocument();
		$validDom->load($this->validSchema);

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
		echo $schemaDom->save($this->buildSchema);
	}

	/**
	 * Gets the value of the specified property.
	 * @param string $name The property name
	 * @return mixed
	 */
	public function get($name) {
		return $this->$name;
	}

}
