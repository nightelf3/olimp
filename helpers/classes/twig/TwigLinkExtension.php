<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:22
 */
namespace helpers\classes\twig;

use helpers\UrlHelper;

class TwigLinkExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('href', [ $this, 'href' ])
        ];
    }

    public function href($controller = '')
    {
        return UrlHelper::href($controller);
    }
}
