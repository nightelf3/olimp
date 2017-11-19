<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 19.11.2017
 * Time: 19:48
 */
namespace helpers;

class MailHelper extends BaseHelper
{
    public static function mail($to, $subject, $template, $data = [])
    {
        \mail($to, TemplateHelper::text($subject), TemplateHelper::render("mail/{$template}", $data));
    }
}
