<?php

/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 13:35
 */
namespace controllers\components;

use helpers\TemplateHelper;
use Klein\Request;
use Klein\Response;

class UserFormComponent extends BaseComponent
{
    public function login(Request $request, Response $response, $service, $app)
    {
        return TemplateHelper::render('forms/login.twig');
    }
}
