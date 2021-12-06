<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 06.12.2021
 * Time: 22:53
 */
namespace controllers\admin;

use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

class CommentsController extends BaseAdminController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        return $this->render('comments');
    }

    public function get(Request $request, Response $response, ServiceProvider $service, App $app)
    {
    }
}
