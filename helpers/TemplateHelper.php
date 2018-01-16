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
    const LANG_PATH = BASE_PATH . '/languages/general.lng';

    /** @var \Twig_Environment $twig */
    protected static $twig = null;
    protected static $lang = [];

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

        self::$lang = json_decode(file_get_contents(self::LANG_PATH), true);
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
        if (ConfigHelper::isDebug()) {
            if (false == isset(self::$lang[$id])) {
                self::$lang[$id] = "_{$id}Text";
                file_put_contents(self::LANG_PATH, json_encode(self::$lang, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        return isset(self::$lang[$id]) ? self::$lang[$id] : "_{$id}Text";
    }
}