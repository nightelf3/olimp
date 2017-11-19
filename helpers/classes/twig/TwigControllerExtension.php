<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:35
 */
namespace helpers\classes\twig;

use helpers\ConfigHelper;
use helpers\ControllerHelper;

class TwigControllerExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('load', [ $this, 'load' ])
        ];
    }

    public function load($name, $action = 'index', $data = [])
    {
        return ControllerHelper::getComponent($name, $action, $data);
    }
}
