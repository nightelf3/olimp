<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 14:43
 */
namespace controllers\admin;

use helpers\UrlHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CompilationErrorModel;
use models\QueueModel;
use models\UserModel;

class InfoController extends BaseAdminController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        return $response->redirect('admin/timer');
    }

    public function users(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->header['css'][] = 'admin/users.css';
        $this->data['users'] = UserModel::get();
        return $this->render('table');
    }

    public function sysinfo(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->header['css'][] = 'admin/sysinfo.css';
        $this->header['js'][] = 'admin/sysinfo.js';

        return $this->render('sysinfo');
    }

    public function sysEvent(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $event = $request->param('event', []);

        if (isset($event['delete_results'])) {
            QueueModel::truncate();
            UserModel::update([
                'mulct' => 0,
                'score' => 0
            ]);
        } elseif (isset($event['reset_results'])) {
            QueueModel::update([
                'stan' => 0,
                'tests' => ''
            ]);
            CompilationErrorModel::truncate();
        }

        return $response->redirect(UrlHelper::href('admin/sysinfo'));
    }
}
