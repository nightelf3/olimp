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
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        \mail($to, TemplateHelper::text($subject),
            htmlspecialchars_decode(TemplateHelper::render("mail/{$template}", $data)),
            $headers);
    }
}
