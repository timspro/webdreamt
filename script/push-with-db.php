<?php
//This may be included from somewhere else.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
}
use WebDreamt\Box;
$box = Box::get();
if (!$box) {
	echo "Could not find a Box!";
	return;
}
umask(0);
$root = $box->VendorDirectory . "../";
chdir($root);
$box->builder()->updatePropel();
echo shell_exec("git add -A :/");
echo shell_exec("git commit -am  'Automatically updated.' -- 2>&1");
echo shell_exec("git pull 2>&1");
echo shell_exec("git push 2>&1");
chdir($root . "vendor/timspro/webdreamt");
echo shell_exec("git pull 2>&1");
$box->builder()->updateDatabase();
chdir($root);
echo shell_exec("composer dumpautoload 2>&1");
echo "Deleting data\n";
$box->builder()->deleteData();
echo "Adding data\n";
$box->filler()->addData();
