<?php
use WebDreamt\Box;
use WebDreamt\Builder;
require_once __DIR__ . '/../../bootstrap.php';

$box = new Box(false);
$box->DatabaseName = 'test';
Builder::automate($box);
$box->BuilderFiles = false;
$box->builder()->loadAllClasses();
