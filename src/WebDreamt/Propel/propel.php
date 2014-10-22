<?php
return array (
  'propel' => 
  array (
    'database' => 
    array (
      'connections' => 
      array (
        'default' => 
        array (
          'adapter' => 'mysql',
          'classname' => 'Propel\\Runtime\\Connection\\ConnectionWrapper',
          'dsn' => 'mysql:host=localhost;dbname=test',
          'user' => 'root',
          'password' => '',
          'attributes' => 
          array (
          ),
        ),
      ),
    ),
    'runtime' => 
    array (
      'defaultConnection' => 'default',
      'connections' => 
      array (
        0 => 'default',
      ),
    ),
    'generator' => 
    array (
      'defaultConnection' => 'default',
      'connections' => 
      array (
        0 => 'default',
      ),
    ),
  ),
);
