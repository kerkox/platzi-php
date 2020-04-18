<?php

require_once '../vendor/autoload.php';

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();
if(getenv('DEBUG') === 'true'){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

use App\Middlewares\AuthenticationMiddleware;
use Illuminate\Database\Capsule\Manager as Capsule;
use Aura\Router\RouterContainer;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use WoohooLabs\Harmony\Harmony;
use WoohooLabs\Harmony\Middleware\DispatcherMiddleware;
use WoohooLabs\Harmony\Middleware\LaminasEmitterMiddleware;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/../logs/app.log', Logger::WARNING));

$container = new DI\Container();
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

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();
$map->get('index', '/', [
    'App\Controllers\IndexController',
    'indexAction',
]);
$map->get('indexJobs', '/jobs', [
    'App\Controllers\JobsController',
    'indexAction',
    'auth' => true
]);
$map->get('deleteJobs', '/jobs/delete', [
    'App\Controllers\JobsController',
    'deleteAction',
    'auth' => true
]);
$map->get('addJobs', '/jobs/add', [
    'App\Controllers\JobsController',
    'getAddJobAction',
    'auth' => true
]);
$map->post('saveJobs', '/jobs/add', [
    'App\Controllers\JobsController',
    'getAddJobAction',
    'auth' => true
    ]);
$map->get('addUsers', '/users/add', [
    'App\Controllers\UserController',
    'getAddUserAction',
    'auth' => true
]);
$map->get('editUser', '/users/edit', [
    'App\Controllers\UserController',
    'getEditUserAction',
    'auth' => true
]);
$map->post('updateUser', '/users/edit', [
    'App\Controllers\UserController',
    'postUpdateUserAction',
    'auth' => true
]);

$map->post('saveUsers', '/users/add', [
    'App\Controllers\UserController',
    'postSaveUserAction',
    'auth' => true
    ]);

$map->get('loginForm', '/login', [
    'App\Controllers\AuthController',
    'getLogin'
]);

$map->post('auth', '/auth', [
    'App\Controllers\AuthController',
    'postLogin'
]);


$map->get('admin', '/admin', [
    'App\Controllers\AdminController',
    'getIndex',
    'auth' => true
]);

$map->get('logout', '/logout', [
    'App\Controllers\AuthController',
    'getLogout',
    'auth' => true
]);



$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);


if (!$route) {
    echo 'No route';
} else {
    $needsAuth = $handlerData['auth'] ?? false;

    if($needsAuth && !isset($_SESSION['userId'])){
        header('Location: /login');
        exit;
    }
    $harmony = new Harmony($request, new Response());
    $harmony
        ->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()));
        if(getenv('DEBUG') === 'true'){
            $harmony->addMiddleware(new \Middlewares\Whoops());
        }
    $harmony
        ->addMiddleware(new AuthenticationMiddleware())
        ->addMiddleware(new Middlewares\AuraRouter($routerContainer))
        ->addMiddleware(new DispatcherMiddleware($container, 'request-handler'))
        ->run();

}
