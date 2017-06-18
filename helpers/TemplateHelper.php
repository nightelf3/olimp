<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 18.06.2017
 * Time: 3:23
 */
namespace helpers;

class TemplateHelper extends BaseHelper
{
    /** @var \Twig_Environment $twig */
    protected static $twig = null;

    public static function initialize()
    {
        $loader = new \Twig_Loader_Filesystem(UrlHelper::pathTo(ConfigHelper::get('template', 'path')));
        self::$twig = new \Twig_Environment($loader);
    }

    /**
     * Render template
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    public static function render($template, array $data)
    {
        return self::$twig->render($template, $data);
    }
}