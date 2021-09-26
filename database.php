<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'max-test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'prefix' => '',
]);

$capsule->setAsGlobal();
