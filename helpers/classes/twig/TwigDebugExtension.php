<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:22
 */
namespace helpers\classes\twig;

use helpers\ConfigHelper;
use helpers\UrlHelper;

class TwigDebugExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('d', [ $this, 'd' ])
        ];
    }

    public function d($data)
    {
        if (ConfigHelper::isDebug()) {
            return d($data);
        }
        return null;
    }
}
