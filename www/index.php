<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 11.04.2017
 * Time: 23:42
 */

require_once(dirname(dirname(__FILE__)) . '/includes/autoload.php');

/* Turn on debug mode */
if (helpers\ConfigHelper::isDebug()) {
    Symfony\Component\Debug\Debug::enable(E_ERROR | E_WARNING);
    Kint_Renderer_Rich::$theme = 'solarized-dark.css';
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    Kint::$enabled_mode = false;
}

/* Eloqument ORM */
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection(helpers\ConfigHelper::getDatabaseSettings(), 'default');
$capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

/* Session */
$sessionId = session_id();
if (empty($sessionId)) {
    session_start(helpers\ConfigHelper::getSessionSettings());
}

/* Klein */
$klein = \helpers\ControllerHelper::getKlein();

/**
 * Main callback function for routes
 *
 * @param string $path
 * @param array $callbacks
 * @param \Klein\Request $request
 * @param \Klein\Response $response
 * @param $service
 * @param $app
 *
 * @return mixed
 */
function callback($path, array $callbacks, Klein\Request $request, Klein\Response $response, $service, $app)
{
    $class = \helpers\ControllerHelper::getControllerClass($callbacks[0]);
    $action = $callbacks[1] ?: 'index';
    $controller = new $class();

    if (method_exists($controller, $action)) {
        return $controller->$action($request, $response, $service, $app);
    }

    helpers\ErrorHelper::assert(false, "Can't find {$controller}->{$action}");
    return $response->code(404);
}

$routes = include(helpers\UrlHelper::path('includes/routes.php'));
foreach ($routes as $route) {
    $method = $route[0];
    $path = $route[1];
    $callbacks = explode("#", $route[2]);

    $klein->respond($method, $path, function ($request, $response, $service, $app) use ($path, $callbacks) {
        return callback($path, $callbacks, $request, $response, $service, $app);
    });
}

$klein->onHttpError(function ($code, $router) {
    //TODO: add 404 catching
    helpers\ErrorHelper::assert(false, "Oh no, a bad error happened that caused a {$code} code.");
});

$klein->dispatch();
