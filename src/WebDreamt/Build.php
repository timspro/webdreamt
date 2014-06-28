<?php

namespace WebDreamt;

class Build {

	/**
	 * Given an EMPTY database, build() will attempt to 1) Insert the necessary tables for Sentry
	 * to function, 2) Build the Propel schema.xml file based off the Sentry tables, 3) Build the
	 * corresponding Propel classes based off of schema.xml.
	 */
	static public function build() {
		/**
		 * Commands of interest:
		 * propel model:build
		 * propel sql:build
		 * propel reverse "mysql:host=localhost;dbname=db;user=root;password=pwd"
		 */
		$schema = __DIR__ . "/../../vendor/cartalyst/sentry/schema/mysql.sql";
		if (!file_exists($schema)) {
			throw new Exception("Sentry schema does not exist at " . $schema);
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
	 * updatePropel() attempts to 1) Build the schema.xml file based off of the current database,
	 * 2) Build the Propel classes corresponding to schema.xml. Like
	 */
	static public function updatePropel() {
		$cd = "cd " . __DIR__ . "/../";
		$propel = __DIR__ . "/../vendor/bin/propel";

		if (!file_exists($propel)) {
			throw new Exception("Propel command does not exist at " . $propel);
		}

		shell_exec($cd . ";" . $propel . ' reverse "mysql:host=' . self::$db_host . ';dbname=' .
				Settings::$db_name . ';user=' . self::$root . ';password=' . self::$db_password . '"');
		shell_exec($cd . ";" . $propel . " model:build");
	}

	/**
	 * updateDatabase() attempts to 1) Diff the status of the database with build.xml,
	 * 2) Push any migrations created to the database.
	 */
	static public function updateDatabase() {

	}

}
