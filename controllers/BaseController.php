<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:33
 */
namespace controllers;

use helpers\TemplateHelper;

abstract class BaseController
{
    protected $data = [];

    protected function render($template)
    {
        //TODO: change
        $this->data['header'] = TemplateHelper::render("pages/common/header");
        $this->data['footer'] = TemplateHelper::render("pages/common/footer");

        return TemplateHelper::render("pages/{$template}", $this->data);
    }
}
