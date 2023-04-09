<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 11.04.2017
 * Time: 23:42
 */

require_once(dirname(dirname(__FILE__)) . '/includes/autoload.php');
date_default_timezone_set(helpers\ConfigHelper::get('datetime', 'timezone'));

/* Turn on debug mode */
if (helpers\ConfigHelper::isDebug()) {
    error_reporting(E_ERROR | E_WARNING);
    Symfony\Component\ErrorHandler\DebugClassLoader::enable();
    Symfony\Component\ErrorHandler\ErrorHandler::register(new Symfony\Component\ErrorHandler\ErrorHandler(new Symfony\Component\ErrorHandler\BufferingLogger(), true));
    Kint\Renderer\RichRenderer::$theme = 'solarized-dark.css';
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
function callback($path, array $callbacks, Klein\Request $request, Klein\Response $response, \Klein\ServiceProvider $service, \Klein\App $app)
{
    $class = \helpers\ControllerHelper::getControllerClass($callbacks[0]);
    $action = $callbacks[1] ?: 'index';
    $controller = new $class($request);

    if (method_exists($controller, $action)) {
        return $controller->$action($request, $response, $service, $app);
    }

    helpers\ErrorHelper::assert(false, "Can't find {$class}->{$action}");
    throw \Klein\Exceptions\HttpException::createFromCode(404);
}

$routes = include(helpers\UrlHelper::path('includes/routes.php'));
foreach ($routes as $route) {
    $methods = $route[0];
    $path = $route[1];
    $callbacks = explode("#", $route[2]);
    $conditions = $route[3] ?: [];

    $klein->respond($methods, $path, function (\Klein\Request $request, \Klein\Response $response, \Klein\ServiceProvider $service, \Klein\App $app) use ($path, $callbacks, $conditions) {
        if ($conditions['login'] && !\helpers\UserHelper::isAuthenticated()) {
            throw \Klein\Exceptions\HttpException::createFromCode(401);
        }
        if ($conditions['admin']) {
            if (!\helpers\UserHelper::isAdmin()) {
                throw \Klein\Exceptions\HttpException::createFromCode(401);
            }
            $callbacks[0] = 'admin\\' . $callbacks[0];
        }

        $app->register('optional', function() use ($conditions) {
            return $conditions['optional'];
        });
        return callback($path, $callbacks, $request, $response, $service, $app);
    });
}

$klein->onHttpError(function ($code, \Klein\Klein $router) {
    //TODO: add 404, 403, 401 catching
    helpers\ErrorHelper::assert(false, "Oh no, a bad error happened that caused a {$code} code.");
    return $router->response()->redirect(\helpers\UrlHelper::href());
});

$klein->dispatch();
