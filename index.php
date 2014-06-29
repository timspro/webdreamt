<?php

$pdo = new \PDO("mysql:host=localhost;dbname=webdreamt;charset=utf8", "root", "");
$pdo->prepare("CREATE TABLE test (test VARCHAR(20))")->execute();
