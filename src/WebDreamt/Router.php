<?php

namespace WebDreamt;

//Check permissions.
class Router {

	private static $webRoot;
	private static $cache;

	public static function init() {
		self::$webRoot = __DIR__ . "/../../web/";
		self::$cache = __DIR__ . "/cache/web.dat";
	}

	private $web;
	private $retryOnNotFound;

	/**
	 * Constructs the router by parsing the web root. Note that file extensions are ignored and there
	 * should not be a folder named "index".
	 */
	public function __construct($retryOnNotFound = true) {
		$this->retryOnNotFound = $retryOnNotFound;
		//Check to see if the cache exists.
		if (!file_exists(self::$cache)) {
			//If it doesn't
			$this->web = $this->buildWeb(self::$webRoot);
			file_put_contents(self::$cache, json_encode($this->web));
		} else {
			$this->web = json_decode(self::$cache);
		}
	}

	protected function buildWeb($originalDir) {
		$contents = array();
		foreach (scandir($originalDir) as $fileName) {
			if ($fileName == '.' || $fileName == '..') {
				continue;
			}
			if (is_dir($originalDir . '/' . $fileName)) {
				$contents[$fileName] = buildWeb($originalDir . '/' . $fileName);
			} else {
				$contents[$fileName] = $originalDir . "/" . $fileName;
			}
		}
		return $contents;
	}

	/**
	 * Actually runs a script
	 * @param script $_SCRIPT The full script's pathname
	 * @param array $_URL An array of the URL parameters
	 */
	protected function go($_SCRIPT, $_URL) {
		require_once $_SCRIPT;
	}

	/**
	 * Executes the page associated with the URL, passing along any page parameters in the process.
	 * @param string $url The URL to execute. Can start with "/", which has no effect.
	 */
	public function run($url) {
		//Get rid of starting slash if it exists.
		if ($url[0] === "/") {
			$url = substr($url, 1);
		}

		$parts = explode("/", $url);
		$current = $this->web;
		$script = null;
		$params = [];
		//Parse through the parts of the URL.
		foreach ($parts as $part) {
			//While we don't know what script to use.
			if (!$script) {
				//Check to see if there is match for the current part.
				if (isset($current[$part])) {
					$current = $current[$part];
					//If $current is a string, then we have found the script's filename.
					//If it is an array, then we need to look further down the URL path.
					if (is_string($current)) {
						$script = $current;
					}
					//Check to see if there is an index.
				} else if (isset($current["index"])) {
					//If there is an "index" entry in web, then we know it is a filename by definition.
					$script = $current["index"];
					//Need to add the current part in this case.
					$params[] = $part;
				} else {
					throw new Exception("Could not find page for " + $url);
				}
				//Otherwise, just add the part to the list of parameters.
			} else {
				$params[] = $part;
			}
		}

		//If we ran out of parts, and haven't found a script...
		if (!$script) {
			//Check to see if there is an index for the currently given URL path.
			if (isset($current["index"])) {
				$script = $current["index"];
			} else {
				throw new Exception("Cound not find page for " + $url);
			}
		}

		//Run the resolved file.
		$this->go($script, $url);
	}

}
