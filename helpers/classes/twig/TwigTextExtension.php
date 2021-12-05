<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:22
 */
namespace helpers\classes\twig;

class TwigTextExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('decode', [ $this, 'decode' ]),
        ];
    }

    public function decode($string)
    {
        return htmlspecialchars_decode($string);
    }
}
