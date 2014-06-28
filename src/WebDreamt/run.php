<?php

namespace WebDreamt;

require_once __DIR__ . '/Settings.php';

Settings::sentry()->createUser(['email' => "schmuck", 'password' => "Slimeser23"]);
