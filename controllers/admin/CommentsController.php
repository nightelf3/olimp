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
        if ($request->param('comment_submit')) {
            CommentModel::create([
                'user_id' => UserHelper::getUser()->user_id,
                'task_id' => $this->data['task_id'],
                'text' => $request->param('comment', '')
            ]);
            return $response->redirect(UrlHelper::href("admin/comments/{$this->data['task_id']}"));
        }

        $this->data['tasks'] = TaskModel::select([ 'task_id', 'name' ])->where([
            'user_id' => UserHelper::getUser()->user_id
        ])->orderBy('sort_order')->get();

        $comments = [];
        $commentsData = CommentModel::where([
            'task_id' => $this->data['task_id']
        ])->orderBy('comment_id', 'ASC')->get();
        $index = 0;
        foreach ($commentsData as $comment) {
            if (!isset($comments[$comment->user->user_id])) {
                $comments[$comment->user->user_id] = [
                    'header' => $comment->user->username,
                    'user_id' => $comment->user->user_id,
                    'admin_id'=> UserHelper::getUser()->user_id,
                    'comments' => [],
                    'index' => $index++
                ];
            }

            $comments[$comment->user->user_id]['comments'][] = [
                'user_id' => $comment->user->user_id,
                'user' => $comment->user->username,
                'date' => $comment->created_at,
                'text' => $comment->text
            ];
        }

        // sort data in desc order, so new comments will be on top
        usort($comments, function($a, $b) {
            return $a['index'] < $b['index'];
        });


        $this->data['comments'] = [];
        foreach ($comments as $data) {
            $this->data['comments'][] = TemplateHelper::render('components/comments', $data);
        }

        return $this->render('comments');
    }
}
