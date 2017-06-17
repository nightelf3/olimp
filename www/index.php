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
    $class = str_replace("\\", "/", $class);

    if (file_exists(BASE_PATH . "/{$class}.php")) {
        require_once BASE_PATH . "/{$class}.php";
    }

    return false;
});

$config = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(BASE_PATH . "/config/app.yml"));

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $config['database']['host'],
    'database'  => $config['database']['database'],
    'username'  => $config['database']['username'],
    'password'  => $config['database']['password'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => $config['database']['prefix'],
], 'default');
$capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// turn on debug mode
if ($config['env'] == "dev") {
    Symfony\Component\Debug\Debug::enable(E_ERROR | E_WARNING);
    Kint::$theme = 'solarized-dark';
} else {
    error_reporting(E_ERROR | E_WARNING);
    ini_set('display_errors', 0);
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
function callback($path, array $callbacks, $request, $response, $service, $app)
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
        $response->body("Not found");
        return $response->code(404);
    }
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
    if ($code >= 400 && $code < 500) {
        $ec = new \ExceptionController();
        echo $ec->index($router->request(), $router->response(), $router->service(), $router->app());
    } elseif ($code >= 500 && $code <= 599) {
        error_log('Something bad happened');
    }
});

$klein->dispatch();
