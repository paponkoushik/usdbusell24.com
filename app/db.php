<?php
/**
 * Database connection create
 */

use Illuminate\Events\Dispatcher;

$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => getenv("DB_HOST"),
    'database' => getenv("DB_NAME"),
    'username' => getenv("DB_USERNAME"),
    'password' => getenv("DB_PASSWORD"),
    'charset' => 'Utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => '',
], 'default');
$events = new Dispatcher(new \Illuminate\Container\Container());

/*$events->listen('Illuminate\Database\Events\QueryExecuted', function ($query) use ($container) {
    $logger = $container->get('logger');
    echo sprintf("[mysql_query] %s executed in %f seconds", $query->sql, $query->time);
});*/

$capsule->setEventDispatcher($events);
$capsule->setAsGlobal();
$capsule->bootEloquent();
// End of Database connection