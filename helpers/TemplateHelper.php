<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 18.06.2017
 * Time: 3:23
 */
namespace helpers;

use helpers\classes\twig\TwigLanguageExtension;

class TemplateHelper extends BaseHelper
{
    /** @var \Twig_Environment $twig */
    protected static $twig = null;

    public static function initialize()
    {
        $loader = new \Twig_Loader_Filesystem(UrlHelper::path(ConfigHelper::get('template', 'path')));
        self::$twig = new \Twig_Environment($loader, [
            'debug' => ConfigHelper::isDebug()
        ]);

        $pathToTwigExtensions = ConfigHelper::get('template', 'extensions') . '/';
        foreach (glob(UrlHelper::path("{$pathToTwigExtensions}*.php"), GLOB_BRACE) as $extension) {
            $class = str_replace('/', '\\', $pathToTwigExtensions . basename($extension, '.php'));
            self::$twig->addExtension(new $class());
        }
    }

    /**
     * Render template
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    public static function render($template, array $data = [])
    {
        return self::$twig->render("{$template}.twig", $data);
    }

    public static function text($id)
    {
        //TODO: implement multi-language support
        return "_{$id}Text";
    }
}