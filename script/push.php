<?php
//This may be included from somewhere else or may be run directly.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
}
use WebDreamt\Box;
//Disallow most command line usage.
if (php_sapi_name() !== 'cli' && !(isset($wdAllowWebGit) && $wdAllowWebGit === true)) {
	echo 'This script cannot be run via the web unless the variable $wdAllowWebPush is '
	. 'set and is equal to true since it is difficult to specify git credentials for the server.';
	return;
}

//Note that there is no reason we need a custom Box here.
$box = Box::get();
umask(0);
$root = $box->VendorDirectory . "../";
chdir($root);
echo shell_exec("git add -A :/");
echo shell_exec("git commit -am  'Automatically updated.' -- 2>&1");
echo shell_exec("git pull 2>&1");
echo shell_exec("git push 2>&1");
echo shell_exec("composer dumpautoload 2>&1");
