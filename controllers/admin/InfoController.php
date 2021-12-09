<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 14:43
 */
namespace controllers\admin;

use helpers\ControllerHelper;
use helpers\SettingsHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CommentModel;
use models\CompilationErrorModel;
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
            'is_enabled' => UserHelper::getUser()->is_enabled
        ];

        $this->data['user'] = UserHelper::getUser();
        return $this->render('sysinfo');
    }

    public function sysEvent(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $event = $request->param('event', []);

        $taskIds = array_column(TaskModel::select([ 'task_id' ])
            ->where('user_id', UserHelper::getUser()->user_id)
            ->get()->toArray(), 'task_id');
        if (isset($event['delete_results'])) {
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
        $settings = $request->param('settings', []);
        /** @var UserModel $user */
        $user = UserHelper::getUser();

        $user->is_enabled = $settings['is_enabled'] ?: 0;
        $user->save();

        SettingsHelper::setParam('useLastResults', $settings['useLastResults'] ?: 0);
        SettingsHelper::setParam('indexContent', $settings['indexContent'] ?: '<p></p>');
        SettingsHelper::setParam('enableRegistration', $settings['enableRegistration'] ?: 0);
        SettingsHelper::setParam('enable_comments', $settings['enable_comments'] ?: 0);
        SettingsHelper::setParam('enable_rating', $settings['enable_rating'] ?: 0);

        return $response->redirect(UrlHelper::href('admin/sysinfo'));
    }
}
