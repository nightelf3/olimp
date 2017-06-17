<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 11.04.2017
 * Time: 23:42
 */

define('BASE_PATH', dirname(dirname(__FILE__)));

require_once(BASE_PATH . '/vendor/autoload.php');
spl_autoload_register(function ($class) {
    $classPath = str_replace("\\", "/", $class);

    if (file_exists(BASE_PATH . "/{$classPath}.php")) {
        require_once BASE_PATH . "/{$classPath}.php";

        // initialize helpers
        if (preg_match('/^helpers/', $class) && method_exists($class, 'initialize')) {
            $class::initialize();
        }
    }

    return false;
});

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection(helpers\ConfigHelper::getDatabaseSettings(), 'default');
$capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// turn on debug mode
if (helpers\ConfigHelper::isDev()) {
    Symfony\Component\Debug\Debug::enable(E_ERROR | E_WARNING);
    Kint_Renderer_Rich::$theme = 'solarized-dark.css';
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    Kint::$enabled_mode = false;
}

$sessionId = session_id();
if (empty($sessionId)) {
    session_start(helpers\ConfigHelper::getSessionSettings());
}

$klein = new \Klein\Klein();
$routes = include(BASE_PATH . '/includes/routes.php');

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
    $namespaces = explode("\\", $callbacks[0]);
    $controller = ucfirst(array_pop($namespaces)) . 'Controller';
    if ($namespaces) {
        $class = "controllers\\" . implode("\\", $namespaces) . "\\{$controller}";
    } else {
        $class = "controllers\\{$controller}";
    }

    $action = $callbacks[1] ?: 'index';
    $controller = new $class($controller, $action, $request);

    if (method_exists($controller, $action)) {
        $obj = $controller->$action($request, $response, $service, $app);
        return $obj;
    } else {
        helpers\ErrorHelper::assert("Can't find {$controller}->{$action}");
    }

    return $response->code(404);
}

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
    helpers\ErrorHelper::assert("Oh no, a bad error happened that caused a {$code} code.");
});

$klein->dispatch();
