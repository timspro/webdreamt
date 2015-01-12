<?php

namespace WebDreamt;

class Script {

	/**
	 * Constructs an instance of the Script class.
	 * @param Box $box All methods except push() need a $box to be declared.
	 */
	function __construct($box) {

	}

	/**
	 * Runs the manager browser-based helper script.
	 */
	function manager() {
		require __DIR__ . '/../../script/manager.php';
	}

	/**
	 * Runs the authorization browser-based helper script.
	 */
	function authorization() {
		require __DIR__ . '/../../script/authorization.php';
	}

	/**
	 * Runs a git push script that also synchronizes Propel with the DB in the process.
	 * Note that this probably should not be run from browser as it is likely the web server
	 * won't have the right permissions to use command line git.
	 */
	function pushWithDB() {
		require __DIR__ . '/../../script/push-with-db.php';
	}

	/**
	 * Runs a git push script. Note that this probably should not be run from brower as it
	 * is likely the web server won't have the right permissions to use command line git.
	 */
	function push() {
		require __DIR__ . '/../../script/push.php';
	}

}
