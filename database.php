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

// /* Создаем таблицу tasks */
$tableIsset = Capsule::schema()->hasTable('tasks');

if (!$tableIsset) {
    Capsule::schema()->create('tasks', function ($table) {
        $table->id();
        $table->string('status')->nullable();
        $table->string('result')->nullable();
        $table->string('photo_name');
        $table->timestamps();
    });
}
