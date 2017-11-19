<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:38
 */
namespace helpers;

use Klein\Klein;

class ControllerHelper extends BaseHelper
{
    /**
     * @var Klein
     */
    protected static $klein = null;

    public static function getKlein()
    {
        if (null == self::$klein) {
            self::$klein = new Klein();
        }

        return self::$klein;
    }

    public static function getControllerClass($name, $sufix = 'Controller')
    {
        $namespaces = explode("\\", $name);
        $controller = ucfirst(array_pop($namespaces)) . $sufix;
        if ($namespaces) {
            return "controllers\\" . implode("\\", $namespaces) . "\\{$controller}";
        }

        return "controllers\\{$controller}";
    }

    public static function getComponent($name, $action = 'index', $data = [])
    {
        $class = \helpers\ControllerHelper::getControllerClass($name, 'Component');
        $action = $action ?: 'index';
        $controller = new $class($data);
        $klein = self::getKlein();

        if (method_exists($controller, $action)) {
            return $controller->$action($klein->request(), $klein->response(), $klein->service(), $klein->app());
        }

        ErrorHelper::assert(false, "Can't find {$controller}->{$action}");
        return $klein->response()->code(404);
    }
}