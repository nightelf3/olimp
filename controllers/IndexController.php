<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:33
 */
namespace controllers;

use helpers\SettingsHelper;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

class IndexController extends BaseController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $errors = [];
        if ($request->method('post')) {
            $errors = UserHelper::login($request);
            if (empty($errors)) {
                return $response->redirect(UrlHelper::href('task'));
            }
        }

        if (!UserHelper::isAuthenticated()){
            $this->data['loginForm'] = TemplateHelper::render('components/login', [ 'errors' => $errors ]);
        }

        $this->data['indexContent'] = SettingsHelper::param('indexContent', '<p></p>');
        return $this->render('home');
    }
}
