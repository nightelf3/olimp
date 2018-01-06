<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 14:43
 */
namespace controllers\admin;

use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

class IndexController extends BaseAdminController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        return $this->render('home');
    }
}
