<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 06.12.2021
 * Time: 22:53
 */
namespace controllers\admin;

use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CommentModel;
use models\TaskModel;

class CommentsController extends BaseAdminController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $tasks = TaskModel::select([ 'task_id', 'name' ])->where([
            'user_id' => UserHelper::getUser()->user_id
        ])->orderBy('sort_order')->get();
        if (empty($tasks)) {
            return $this->render('comments', [ 'tasks' => $tasks ]);
        }
        return $response->redirect(UrlHelper::href("admin/comments/{$tasks[0]->task_id}"));
    }

    public function task(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->data['task_id'] = $request->param('task_id', 0);
        $this->data['tasks'] = TaskModel::select([ 'task_id', 'name' ])->where([
            'user_id' => UserHelper::getUser()->user_id
        ])->orderBy('sort_order')->get();

        $this->data['comments'] = [];
        foreach (CommentModel::where('task_id', $this->data['task_id'])->get() as $comment) {
            $this->data['comments'][] = TemplateHelper::render('components/comments', [
                'header' => "user002",
                'user_id' => 0,
                'comments' => []
            ]);
        }

        return $this->render('comments');
    }

    public function get(Request $request, Response $response, ServiceProvider $service, App $app)
    {
    }
}
