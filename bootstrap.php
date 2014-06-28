<?php

require_once "vendor/autoload.php";

new WebDreamt\Settings();

if(!isset($_GET['url'])) {
	return;
}

$action = basename($_GET['url']);
echo "URL: " . $_GET['url'];

if($action === "query" && isset($_GET['query'])) {
	return (new WebDreamt\Query($_GET['query']))->execute();
}