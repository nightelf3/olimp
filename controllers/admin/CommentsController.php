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
        $this->header['js'][] = 'admin/comments.js';

        $this->data['task_id'] = $request->param('task_id', 0);
        if ($request->param('comment_submit')) {
            CommentModel::create([
                'from_id' => UserHelper::getUser()->user_id,
                'to_id' => $request->param('user_id', UserHelper::getUser()->user_id),
                'task_id' => $this->data['task_id'],
                'text' => $request->param('comment', '')
            ]);
            return $response->redirect(UrlHelper::href("admin/comments/{$this->data['task_id']}"));
        }

        $this->data['tasks'] = TaskModel::select([ 'tasks.task_id', 'name' ])->selectRaw('count(comment_id) as comments_count')
            ->leftJoin('comments', 'comments.task_id', '=', 'tasks.task_id')
            ->groupBy('tasks.task_id')
            ->where([
                'user_id' => UserHelper::getUser()->user_id
            ])->orderBy('sort_order')->get();

        $comments = [];
        $commentsData = CommentModel::where([
            'task_id' => $this->data['task_id']
        ])->orderBy('comment_id', 'ASC')->get();
        foreach ($commentsData as $comment) {
            if (!isset($comments[$comment->to->user_id])) {
                $comments[$comment->to->user_id] = [
                    'header' => $comment->to->username,
                    'user_id' => $comment->to->user_id,
                    'admin_id' => UserHelper::getUser()->user_id,
                    'comments' => []
                ];
            }

            $comments[$comment->to->user_id]['comments'][] = [
                'comment_id' => $comment->comment_id,
                'user_id' => $comment->from->user_id,
                'user' => $comment->from->username,
                'date' => $comment->created_at,
                'text' => $comment->text
            ];
        }

        // sort data in desc order, so new comments will be on top
        usort($comments, function($a, $b) {
            $isAnsweredA = end($a['comments'])['user_id'] == UserHelper::getUser()->user_id;
            $isAnsweredB = end($b['comments'])['user_id'] == UserHelper::getUser()->user_id;
            if ($isAnsweredA ^ $isAnsweredB) {
                return $isAnsweredA > $isAnsweredB;
            }

            return end($a['comments'])['comment_id'] < end($b['comments'])['comment_id'];
        });


        $this->data['comments'] = [];
        foreach ($comments as $data) {
            $this->data['comments'][] = TemplateHelper::render('components/comments', $data);
        }

        return $this->render('comments');
    }
}
