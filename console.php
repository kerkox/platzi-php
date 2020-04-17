#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use App\Commands\HelloWorldCommand;
use Symfony\Component\Console\Application;
use Illuminate\Database\Capsule\Manager as Capsule;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => getenv('DB_DRIVER','mysql'),
    'host'      => getenv('DB_HOST','localhost'),
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USER'),
    'password'  => getenv('DB_PASS'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'port'      => getenv('DB_PORT',3306)
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

$application = new Application();

// ... register commands
$application->add(new HelloWorldCommand());
$application->add(new \App\Commands\CreateUserCommand());
$application->run();
