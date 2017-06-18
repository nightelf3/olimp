<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 18.06.2017
 * Time: 3:51
 */

define('BASE_PATH', dirname(dirname(__FILE__)));

/* Autoload */
require_once(BASE_PATH . '/vendor/autoload.php');
spl_autoload_register(function ($class) {
    $classPath = str_replace("\\", "/", $class);
    $classPath = BASE_PATH . "/{$classPath}.php";

    if (file_exists($classPath)) {
        require_once ($classPath);

        // initialize helpers
        if (preg_match('/^helpers/', $class) && method_exists($class, 'initialize')) {
            $class::initialize();
        }
    }

    return false;
});