<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:33
 */
namespace controllers;

use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\Request;
use Klein\Response;

class IndexController extends BaseController
{
    public function index(Request $request, Response $response, $service, $app)
    {
        $errors = [];
        if ($request->method('post')) {
            $errors = UserHelper::login($request);
            if (empty($errors)) {
                return $response->redirect(UrlHelper::href());
            }
        }

        if (UserHelper::isAuthenticated()) {
            $this->data['userForm'] = TemplateHelper::render('components/user', [ 'user' => UserHelper::getUser() ]);
        } else {
            $this->data['userForm'] = TemplateHelper::render('components/login', [ 'errors' => $errors ]);
        }

        return $this->render('home');
    }
}
