<?php

namespace WebDreamt;

class Build {

	private static $sentrySchemaDirectory;
	private static $propelProjectDirectory;
	private static $propelCommandPath;
	private static $userSchema;
	private static $buildSchema;
	private static $validSchema;
	private static $generatedSchema;

	/**
	 * Given an EMPTY database, build() will attempt to 1) Insert the necessary tables for Sentry
	 * to function, 2) Build the Propel schema.xml file based off the Sentry tables, 3) Build the
	 * corresponding Propel classes based off of schema.xml.
	 * @throws Exception If the Sentry schema is not found or if the database is not empty.
	 */
	static public function build() {
		/**
		 * Commands of interest:
		 * propel model:build
		 * propel sql:build
		 * propel reverse "mysql:host=localhost;dbname=db;user=root;password=pwd"
		 */
		$schema = self::$sentrySchemaDirectory;
		if (!file_exists($schema)) {
			throw new Exception("Sentry schema does not exist at $schema");
		}

		$pdo = Settings::pdo();
		if (count($pdo->query("SHOW TABLES")) === 0) {
			throw new Exception("Database is not empty.");
		}

		$sentrySchema = file_get_contents($schema);
		$pdo->exec($sentrySchema);

		self::updatePropel();
	}

	/**
	 * updatePropel() attempts to 1) Build the schema.xml file based off of the
	 * current database (and merge that build with valid.xml),
	 * 2) Build the Propel classes corresponding to schema.xml.
	 * @throws Exception If the propel command, the Propel project directory, or the generated schema
	 * are not found.
	 */
	static public function updatePropel() {
		$project = self::$propelProjectDirectory;
		$cd = "cd " . $project;
		$propel = self::$propelCommandPath;

		if (!file_exists($propel)) {
			throw new Exception("Propel command does not exist at $propel");
		}
		if (!file_exists($project)) {
			throw new Exception("Project directory does not exist at $project");
		}

		$host = Settings::getDbHost();
		$name = Settings::getDbName();
		$username = Settings::getDbUsername();
		$password = Settings::getDbPassword();
		shell_exec("$cd; $propel reverse \"mysql:host=$host;dbname=$name;user=$username;" .
				"password=$password\"");
		$gen = self::$generatedSchema;
		if (!file_exists($gen)) {
			throw new Exception("Generated schema does not exist at $gen");
		}
		rename($gen, self::$userSchema);
		self::generateSchemaXml();

		self::generateModels();
	}

	/**
	 * Generates the models for Propel.
	 * @throws Exception If Propel/schema.xml is not found.
	 */
	static public function generateModels() {
		$build = self::$buildSchema;
		if (!file_exists($build)) {
			throw new Exception("Build schema does not exist at $build");
		}

		$propel = self::$propelCommandPath;
		$cd = "cd " . self::$propelProjectDirectory;
		shell_exec("$cd; $propel model:build");
	}

	/**
	 * updateDatabase() attempts to 1) Diff the status of the database with build.xml (formed from
	 * combining Schemas/build.xml and valid.xml), 2) Push any migrations created to the database,
	 * 3) Generate Propel classes.
	 * @throws Exception If propel command or project directory is not found.
	 */
	static public function updateDatabase() {
		self::generateSchemaXml();

		$propel = self::$propelCommandPath;
		if (!file_exists($propel)) {
			throw new Exception("Propel command does not exist at $propel");
		}
		$project = self::$propelProjectDirectory;
		if (!file_exists($project)) {
			throw new Exception("Project directory does not exist at $project");
		}

		$cd = "cd " . $project;
		shell_exec("$cd; $propel . diff migrate");

		self::generateModels();
	}

	/**
	 * Merges valid.xml and schema.xml in Schema directory to create Propel/schema.xml
	 * @throws Exception If user or valid schema is not found.
	 */
	static public function generateSchemaXml() {
		$userSchema = self::$userSchema;
		$validSchema = self::$validSchema;

		if (!file_exists($userSchema)) {
			throw new Exception("User schema does not exist at $userSchema");
		}
		if (!file_exists($validSchema)) {
			throw new Exception("Valid schema does not exist at $validSchema");
		}

		$schemaDom = new \DOMDocument();
		$schemaDom->load(self::$userSchema);
		$validDom = new \DOMDocument();
		$validDom->load(self::$validSchema);

		$markers = $validDom->getElementsByTagName('table');
		//Get the validation rules.
		$data = [];
		/* @var $marker \DOMNode */
		foreach ($markers as $marker) {
			$tableName = $marker->attributes["name"];
			$data[$tableName] = $marker->childNodes;
		}

		//Add the validation rules to data schema.
		$markers = $schemaDom->getElementsByTagName('table');
		foreach ($markers as $marker) {
			$tableName = $marker->attributes["name"];
			foreach ($data[$tableName] as $node) {
				$marker->appendChild($node);
			}
		}

		//Save as the build schema.
		echo $schemaDom->saveXML(self::$buildSchema);
	}

	static public function init() {
		self::$propelCommandPath = __DIR__ . "/../vendor/bin/propel";
		self::$propelProjectDirectory = __DIR__ . "/Propel";
		self::$sentrySchemaDirectory = __DIR__ . "/../../vendor/cartalyst/sentry/schema/mysql.sql";
		self::$userSchema = __DIR__ . "/Schemas/schema.xml";
		self::$buildSchema = __DIR__ . "/Propel/schema.xml";
		self::$generatedSchema = __DIR__ . "/Propel/generated-schema/schema.xml";
		self::$validSchema = __DIR__ . "/Schemas/valid.xml";
	}

}

Build::init();
