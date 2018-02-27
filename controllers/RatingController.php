<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 03.12.2017
 * Time: 12:44
 */
namespace controllers;

use helpers\classes\enums\TaskStatusEnum;
use helpers\ControllerHelper;
use helpers\SettingsHelper;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\TaskModel;
use models\UserModel;

class RatingController extends BaseController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->data['controller'] = 'rating';
        $users = UserModel::join('tasks', 'tasks.user_id', '=', 'users.user_id')
            ->groupBy('users.user_id')->where([
                'is_admin' => true,
                'users.is_enabled' => true,
                'tasks.is_enabled' => true
            ])->get();
        if (1 == $users->count()) {
            return $response->redirect(UrlHelper::href("rating/{$users[0]['user_id']}"));
        }
        $this->data['users'] = $users;
        return $this->render('list');
    }

    public function get(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->data['tasks'] = TaskModel::select([ 'task_id', 'name' ])
            ->where('user_id', $request->param('user_id', 0))->orderBy('sort_order')->get();
        $this->data['table'] = $this->getRatingTable($request);
        $this->data['showLastResults'] = SettingsHelper::param('useLastResults', false);

        ControllerHelper::updateResults(UserHelper::getUser());

        return $this->render('rating');
    }

    private function getRatingTable(Request $request)
    {
        $arr = [];
        $adminId = $request->param('user_id', 0);
        $results = UserModel::leftJoin('queue', 'users.user_id', '=', 'queue.user_id')->leftJoin('tasks', function($join) use ($adminId) {
            $join->on('queue.task_id', '=', 'tasks.task_id')->where('tasks.user_id', $adminId);
        })->select([
            'users.username', 'users.name as uname', 'users.surname', 'users.score', 'users.mulct', 'users.old_score',
            'tasks.task_id', 'tasks.name', 'tasks.tests_count',
            'queue.queue_id', 'queue.stan'
        ])->orderBy('queue_id', 'desc')->get();

        foreach ($results as $row) {
            if (false == SettingsHelper::param('useLastResults', false)) {
                $row->old_score = 0;
            }
            if (!isset($arr[$row->username])) {
                $arr[$row->username] = [
                    'login' => $row->username,
                    'name' => "{$row->uname}&nbsp;{$row->surname}",
                    'shtraff' => (int)$row->mulct,
                    'score' => ((int)$row->score + (int)$row->old_score),
                    'res_m' => ((int)$row->score + (int)$row->old_score)*1000 - (int)$row->mulct
                ];
            }

            if (is_null($row->task_id)) {
                continue;
            }

            if (preg_match("/([35678]|(10))/u", $row->stan)) {
                if (isset($arr[$row->username]['tasks'][$row->task_id])) {
                    $arr[$row->username]['tasks'][$row->task_id]['try'] += 1;
                } else {
                    $arr[$row->username]['tasks'][$row->task_id]['try'] = 1;
                }

                if (!isset($arr[$row->username]['tasks'][$row->task_id]['ok'])) {
                    if (TaskStatusEnum::CompilingError == $row->stan || TaskStatusEnum::InvalidOutputStream == $row->stan)
                        $arr[$row->username]['tasks'][$row->task_id]['ok'] = '0%';
                    else
                        $arr[$row->username]['tasks'][$row->task_id]['ok'] = round(((int)$row->tests_count - count(explode(',', $row->stan)))/((float)$row->tests_count)*100).'%';

                    if (isset($arr[$row->username]['time_summ']))
                        $arr[$row->username]['time_summ'] += $row->queue_id;
                    else
                        $arr[$row->username]['time_summ'] = $row->queue_id;
                }
            }
            elseif (TaskStatusEnum::Succeed == $row->stan) {
                if (!isset($arr[$row->username]['tasks'][$row->task_id]['ok'])) {
                    $arr[$row->username]['tasks'][$row->task_id]['ok'] = '100%';
                    $arr[$row->username]['time_summ'] = $row->queue_id;
                } else {
                    if (isset($arr[$row->username]['tasks'][$row->task_id])) {
                        $arr[$row->username]['tasks'][$row->task_id]['try'] += 1;
                    } else {
                        $arr[$row->username]['tasks'][$row->task_id]['try'] = 1;
                    }
                }
            }
        }

        $taskIds = TaskModel::select([ 'task_id' ])->where('user_id', $adminId)->orderBy('sort_order')->get();
        foreach ($arr as &$row) {
            $bl = true;
            foreach ($taskIds as $taskId) {
                $bl = $bl && !isset($row['tasks'][$taskId->task_id]);
                if (!isset($row['tasks'][$taskId->task_id]))
                {
                    $row['tasks'][$taskId->task_id] = [
                        'try' => 0,
                        'ok' => '-'
                    ];
                }
            }
            if ($bl && $row['score'] == 0) {
                $row['res_m'] = -PHP_INT_MAX;
            }
        }

        usort($arr, function ($a, $b) {
            if ($a['time_summ'] == $b['time_summ']) {
                return 0;
            }
            return ($a['time_summ'] < $b['time_summ']) ? 1 : -1;
        });

        $count = count($arr);
        for($i = 0; $i < $count - 1; $i++) {
            for($j = $i + 1; $j < $count; $j++) {
                if($arr[$i]['res_m'] < $arr[$j]['res_m']) {
                    $tmp = $arr[$i];
                    $arr[$i] = $arr[$j];
                    $arr[$j] = $tmp;
                }
            }
        }

        return $arr;
    }
}
