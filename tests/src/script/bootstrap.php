<?php
use WebDreamt\Box;
use WebDreamt\Builder;
require_once __DIR__ . '/../../bootstrap.php';

$box = new Box(false);
$box->DatabaseName = 'test';
Builder::automate($box);
$box->DummyBuilder = true;
$box->builder()->loadAllClasses();
