<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 14:43
 */
namespace controllers\admin;

use helpers\ControllerHelper;
use helpers\ErrorHelper;
use helpers\SettingsHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CommentModel;
use models\CompilationErrorModel;
use models\CheckerModel;
use models\QueueModel;
use models\TaskModel;
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
        $this->data['user'] = UserModel::get();
        return $this->render('table');
    }

    public function sysinfo(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->header['css'][] = 'admin/sysinfo.css';
        $this->header['js'][] = 'admin/sysinfo.js';

        $this->data['settings'] = [
            'olimpCheckerLogin' => UserHelper::getUser()->username,
            'olimpCheckerPassword' => UserHelper::getUser()->guid,
            'useLastResults' => SettingsHelper::param('useLastResults', false),
            'indexContent' => SettingsHelper::param('indexContent', '<p></p>'),
            'enableRegistration' => SettingsHelper::param('enableRegistration', false),
            'enable_comments' => SettingsHelper::param('enable_comments', false),
            'enable_rating' => SettingsHelper::param('enable_rating', false),
            'single_login' => SettingsHelper::param('single_login', false),
            'is_enabled' => UserHelper::getUser()->is_enabled
        ];

        $this->data['checkers'] = CheckerModel::where([
            'user_id' => UserHelper::getUser()->user_id
        ])->get();
        $this->data['checkerName'] = UserHelper::getUser()->username . '-checker-' . (count($this->data['checkers']) + 1);
        return $this->render('sysinfo');
    }

    public function sysEvent(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $event = $request->param('event', []);

        $taskIds = array_column(TaskModel::select([ 'task_id' ])
            ->where('user_id', UserHelper::getUser()->user_id)
            ->get()->toArray(), 'task_id');
        if (isset($event['delete_results'])) {
            //TODO: drop Users folder?
            CommentModel::whereIn('task_id', $taskIds)->delete();
            QueueModel::whereIn('task_id', $taskIds)->delete();
            ControllerHelper::updateAllResults();
        } elseif (isset($event['reset_results'])) {
            QueueModel::whereIn('task_id', $taskIds)->update([
                'stan' => 0,
                'tests' => ''
            ]);
            $queueIds = array_column(QueueModel::select([ 'queue_id' ])->whereIn('task_id', $taskIds)
                ->get()->toArray(), 'queue_id');
            CompilationErrorModel::whereIn('queue_id', $queueIds)->delete();
            ControllerHelper::updateAllResults();
        }

        return $response->redirect(UrlHelper::href('admin/sysinfo'));
    }

    public function sysSettings(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        /** @var Array $settings */
        $settings = $request->param('settings', []);

        if (isset($settings['is_enabled']))
        {
            /** @var UserModel $user */
            $user = UserHelper::getUser();
            $user->is_enabled = $settings['is_enabled'];
            $user->save();

            unset($settings['is_enabled']);
        }

        foreach (array_keys($settings) as $key) {
            SettingsHelper::setParam($key, isset($settings[$key]) ? $settings[$key] : SettingsHelper::param($key, false));
        }

        return $response->redirect(UrlHelper::href('admin/sysinfo'));
    }

    public function checkers(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        /** @var CheckerModel $checker */
        $checker = CheckerModel::find($request->param('checker_id', 0));
        if (is_null($checker)) {
            ErrorHelper::assert("Can't find the checker");
            return $response->redirect(UrlHelper::href('admin/sysinfo'));
        }

        switch ($request->param('action', ""))
        {
        case "remove":
            $checker->delete();
            break;
        case "toggle":
            $checker->is_active = !$checker->is_active;
            $checker->update();
            break;
        }

        return $response->redirect(UrlHelper::href('admin/sysinfo'));
    }

    public function checkerConfig(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $json = [
            "api" => UrlHelper::href('api'),
            "username" => UserHelper::getUser()->username,
            "password" => UserHelper::getUser()->guid,
            "checkername" => $request->param('checker-name')
        ];
        return $response->json($json);
    }
}
