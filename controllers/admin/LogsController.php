<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 10.01.2022
 * Time: 21:27
 */
namespace controllers\admin;

use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\LogModel;
use models\QueueModel;

class LogsController extends BaseAdminController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        //TODO: add date to the data
        $this->data['logins'] = [];
        $data = LogModel::join('users', 'users.user_id', '=', 'logs.user_id')
            ->groupBy('logs.user_id')->havingRaw('count(*) > 1')
            ->whereRaw("data like 'login:%' AND is_admin = 0")
            ->select(['username', 'surname', 'name'])
            ->selectRaw("group_concat(distinct concat(mid(`data`, 8), ' ', oi_logs.created_at) separator '<br/>') as data")->get();
        foreach ($data as $row) {
            $this->data['logins'][] = [
                'login' => $row->username,
                'name' => "{$row->surname}&nbsp;{$row->name}",
                'data' => $row->data
            ];
        }

        $this->data['uploads'] = [];
        $data = QueueModel::join('users', 'users.user_id', '=', 'queue.user_id')
            ->groupBy('queue.user_id')->havingRaw('count(distinct upload_ip) > 1')
            ->select(['username', 'surname', 'name'])
            ->selectRaw("group_concat(distinct upload_ip separator '<br/>') as data")->get();
        foreach ($data as $row) {
            $this->data['uploads'][] = [
                'login' => $row->username,
                'name' => "{$row->surname}&nbsp;{$row->name}",
                'data' => $row->data
            ];
        }


        return $this->render('logs');
    }
}
