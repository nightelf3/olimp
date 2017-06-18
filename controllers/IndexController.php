<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:33
 */
namespace controllers;

use Klein\Request;
use Klein\Response;

class IndexController extends BaseController
{
    public function index(Request $request, Response $response, $service, $app)
    {
        $this->data['body'] = "Test body";

        return $this->render('home');
    }
}
