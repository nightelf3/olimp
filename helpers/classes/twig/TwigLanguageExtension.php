<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:12
 */
namespace helpers\classes\twig;

use helpers\TemplateHelper;

class TwigLanguageExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('text', [ $this, 'text' ])
        ];
    }

    public function text($id)
    {
        return TemplateHelper::text($id);
    }
}
