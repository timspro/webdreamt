<?php
use WebDreamt\Box;
require_once __DIR__ . "/../../vendor/autoload.php";

$box = new Box();
$box->DatabaseName = "septic360";
umask(0);
chdir(__DIR__ . "/../../");
$box->builder()->updatePropel();
echo shell_exec("git add -A :/");
echo shell_exec("git commit -am  'Automatically updated.' -- 2>&1");
echo shell_exec("git pull 2>&1");
echo shell_exec("git push 2>&1");
chdir(__DIR__ . "/../../vendor/timspro/webdreamt");
echo shell_exec("git pull 2>&1");
$box->builder()->updateDatabase();
chdir(__DIR__ . "/../../");
echo shell_exec("composer dumpautoload 2>&1");
echo "Deleting data\n";
$box->builder()->deleteData();
$data = require_once __DIR__ . '/data/amount.php';
$custom = require_once __DIR__ . '/data/custom.php';
echo "Adding data\n";
$box->filler()->addData($data, false, $custom);
