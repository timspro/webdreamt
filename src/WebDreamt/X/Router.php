<?php

namespace WebDreamt\X;

//Check permissions.
class Router {

	/**
	 * The directory to use to sore the web files
	 * @var string
	 */
	protected $webDirectory;
	/**
	 * The directory to use to store the cache
	 * @var string
	 */
	protected $cacheDirectory;
	/**
	 * The structure of the web directory.
	 * @var array
	 */
	protected $web;
	/**
	 * Whether to rebuild $web if the script is not found.
	 * @var boolean
	 */
	protected $retryOnNotFound;

	/**
	 * Constructs the router by parsing the web root. Note that file extensions are ignored and there
	 * should not be a folder named "index".
	 * @param Box $box
	 * @param boolean $retryOnNotFound
	 */
	function __construct(Box $box, $retryOnNotFound = true) {
		$this->cacheDirectory = $box->VendorDirectory . "../cache/";
		$this->cacheFile = $this->cacheDirectory . "web.php";
		$this->webDirectory = $box->VendorDirectory . '../web/';
		$this->retryOnNotFound = $retryOnNotFound;
		//Check to see if the cache exists.
		if (!file_exists($this->cacheFile)) {
			if (!file_exists($this->cacheDirectory)) {
				mkdir($this->cacheDirectory);
			}
			$this->buildCache();
		} else {
			$this->web = require $this->cacheFile;
		}
	}

	/**
	 * Builds the Router's cache.
	 */
	function buildCache() {
		$this->web = $this->buildWeb($this->webDirectory);
		file_put_contents($this->cacheFile, print_r($this->web, true));
	}

	/**
	 * Build the cached directory structure.
	 * @param string $originalDir
	 * @return string
	 */
	protected function buildWeb($originalDir) {
		$contents = array();
		foreach (scandir($originalDir) as $fileName) {
			if ($fileName == '.' || $fileName == '..') {
				continue;
			}
			if (is_dir($originalDir . '/' . $fileName)) {
				$contents[$fileName] = $this->buildWeb($originalDir . '/' . $fileName);
			} else {
				$removed = preg_replace('/\\.[^.\\s]{3,4}$/', '', $fileName);
				$contents[$removed] = $originalDir . "/" . $fileName;
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
	 * @param boolena $retry
	 */
	public function run($url, $retry = true) {
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
					$this->notFound($url, $retry);
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
				$this->notFound($url, $retry);
			}
		}

		//Run the resolved file.
		$this->go($script, $url);
	}

	/**
	 * Called when the url is not found.
	 * @param string $url
	 * @param boolean $retry If $retry is true and retryOnNotFound is true, then will retry
	 * to find the page.
	 * @throws Exception
	 */
	protected function notFound($url, $retry) {
		if ($this->retryOnNotFound && $retry) {
			$this->buildCache();
			$this->run($url, false);
		} else {
			throw new Exception("Could not find page for " + $url);
		}
	}

}
