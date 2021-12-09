<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 03.12.2017
 * Time: 12:44
 */
namespace controllers;

use helpers\RatingHelper;
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
        $this->header['js'][] = 'rating.js';

        $userId = $request->param('user_id', 0);
        $tasks = TaskModel::select([ 'task_id', 'name' ])
            ->where('user_id', $userId)->orderBy('sort_order')->get();
        if (SettingsHelper::param('enable_rating', false) || UserHelper::isAdmin())
        {
            $this->data['ratingTable'] = TemplateHelper::render('components/rating_table', [
                'table' => RatingHelper::generate($userId, $tasks),
                'tasks' => $tasks,
                'showLastResults' => SettingsHelper::param('useLastResults', false)
            ]);
        }

        return $this->render('rating');
    }

    public function update(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $userId = $request->param('user_id', 0);
        $tasks = TaskModel::select([ 'task_id', 'name' ])
            ->where('user_id', $userId)->orderBy('sort_order')->get();

        return $response->json([
            'rating' => TemplateHelper::render('components/rating_table', [
                'table' => RatingHelper::generate($userId, $tasks),
                'tasks' => $tasks,
                'showLastResults' => SettingsHelper::param('useLastResults', false)
            ])
        ]);
    }
}
