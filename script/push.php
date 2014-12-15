<?php
//This may be included from somewhere else.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
}
use WebDreamt\Box;
//Note that there is no reason we need a custom Box here.
$box = new Box(false);
umask(0);
$root = $box->VendorDirectory . "../";
chdir($root);
echo shell_exec("git add -A :/");
echo shell_exec("git commit -am  'Automatically updated.' -- 2>&1");
echo shell_exec("git pull 2>&1");
echo shell_exec("git push 2>&1");
echo shell_exec("composer dumpautoload 2>&1");
