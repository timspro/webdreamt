<?php
use WebDreamt\Box;
use WebDreamt\Builder;
require_once __DIR__ . '/../../bootstrap.php';

$box = new Box(false);
$box->DatabaseName = 'test';
Builder::automate($box);
//Since we are not using autoloader to get the Propel classes, we need the next two lines.
//We can set this setting to false because we already ensured we did the necessary setup on line 8.
$box->BuilderFiles = false;
//Now, load the classes.
$box->builder()->loadAllClasses();
