<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 01.12.2017
 * Time: 12:11
 */
namespace controllers;

use helpers\ErrorHelper;
use helpers\SettingsHelper;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Exceptions\HttpException;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CommentModel;
use models\CompilationErrorModel;
use models\CompilerModel;
use models\QueueModel;
use models\TaskModel;
use models\UserModel;

class TaskController extends BaseController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (!SettingsHelper::isOlimpStarts() && !UserHelper::isAdmin()) {
            return $response->redirect(UrlHelper::href('task'));
        }
        
        $userId = $request->param('user_id', 0);
        
        /** @var TaskModel $task */
        $task = TaskModel::select([ 'task_id', 'task', 'is_enabled' ])->where([
            'user_id' => $userId
        ])->find($request->param('task_id', 0));
        
        if (SettingsHelper::isOlimpInProgress()) {
            $errors = $this->uploadFile($request);
            if ($request->files()->count() == 1 && empty($errors)) {
                return $response->redirect(UrlHelper::href("task/{$userId}/{$task->task_id}"));
            }
            $this->data['uploadForm'] = TemplateHelper::render('components/upload', [
                'error' => $errors,
                'task_id' => $task->task_id
            ]);
        }
        
        if ($request->param('comment_submit')) {
            CommentModel::create([
                'user_id' => UserHelper::getUser()->user_id,
                'task_id' => $task->task_id,
                'text' => $request->param('comment', '')
            ]);
            return $response->redirect(UrlHelper::href("task/{$userId}/{$task->task_id}"));
        }

        $this->header['js'][] = 'task.js';


        $tasks = TaskModel::select([ 'task_id', 'name' ])->where([
            'user_id' => $userId
        ])->orderBy('sort_order')->get();
        $this->data['tasks'] = $tasks;

        $this->data['userId'] = $userId;
        $this->data['taskTabs'] = TemplateHelper::render('components/task_tabs', [
            'userId' => $userId,
            'tasks' => $tasks,
            'currentTask' => $task
        ]);

        if (is_null($task)) {
            throw HttpException::createFromCode(404);
        } elseif (!$task->is_enabled) {
            $this->data['errors']['taskIsNotEnabled'] = true;
            $this->data['currentTask']['task_id'] = $task->task_id;
            return $this->render('task');
        }

        $queue = UserHelper::getUser()->getQueue($task->task_id)->toArray();
        foreach ($queue as &$item) {
            $item['stan'] = explode(',', $item['stan']);
            $item['tests'] = $item['tests'] ? explode(',', $item['tests']) : [];
        }
        $this->data['queueInfo'] = TemplateHelper::render('components/queue', [ 'queue' => $queue, 'task' => $task ]);
        $this->data['currentTask'] = $task;

        $comments = [];
        $commentsData = CommentModel::where([
            'user_id' => UserHelper::getUser()->user_id,
            'task_id' => $task->task_id
        ])->orderBy('comment_id', 'ASC')->get();
        foreach ($commentsData as $comment) {
            $comments[] = [
                'user' => UserHelper::getUser()->username,
                'date' => $comment->created_at,
                'text' => $comment->text
            ];
        }
        $this->data['commentsForm'] = TemplateHelper::render('components/comments', [ 'comments' => $comments ]);

        return $this->render('task');
    }

    public function task(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (!SettingsHelper::isOlimpStarts() && !UserHelper::isAdmin()) {
            $this->data['username'] = UserHelper::getUser()->username;
            return $this->render('wait');
        }

        $userId = $request->param('user_id', 0);
        $task = TaskModel::select(['task_id'])->where([
            'user_id' => $userId,
            'is_enabled' => true
        ])->orderBy('sort_order')->first();
        if (!is_null($task)) {
            return $response->redirect(UrlHelper::href("task/{$userId}/{$task->task_id}"));
        }

        $this->data['controller'] = 'task';
        $users = UserModel::join('tasks', 'tasks.user_id', '=', 'users.user_id')->groupBy('users.user_id')->where([
            'is_admin' => 1,
            'users.is_enabled' => true,
            'tasks.is_enabled' => true
        ])->get();
        if (1 == $users->count()) {
            return $response->redirect(UrlHelper::href("task/{$users[0]['user_id']}"));
        }

        $this->data['users'] = $users;
        return $this->render('list');
    }

    public function compile(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $error = CompilationErrorModel::join('queue', 'queue.queue_id', '=', 'compilation_errors.queue_id')
            ->join('users', 'queue.user_id', '=', 'users.user_id')
            ->join('tasks', 'queue.task_id', '=', 'tasks.task_id')
            ->where([
                'queue.queue_id' => $request->param('queue_id', 0),
                'users.user_id' => UserHelper::getUser()->user_id
            ])->select([
                'compilation_errors.error',
                'tasks.name',
                'tasks.task_id',
                'tasks.user_id'
            ])->first();
        if (is_null($error)) {
            ErrorHelper::assert("You haven't access to this log.");
            return $response->redirect(UrlHelper::href('task'));
        }

        $this->data['compileLog'] = preg_replace("/<\s*br\s*\/?>/i", "\n", $error->error);
        $this->data['taskName'] = $error->name;
        $this->data['taskId'] = $error->task_id;
        $this->data['userId'] = $error->user_id;
        return $this->render('compile');
    }

    public function update(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (!SettingsHelper::isOlimpStarts() && !UserHelper::isAdmin()) {
            throw HttpException::createFromCode(403);
        }

        $userId = $request->param('user_id', 0);
        $this->data['userId'] = $userId;

        /** @var TaskModel $task */
        $task = TaskModel::select([ 'task_id', 'task', 'is_enabled' ])->where([
            'user_id' => $userId
        ])->find($request->param('task_id', 0));
        if (is_null($task)) {
            throw HttpException::createFromCode(404);
        } elseif (!$task->is_enabled) {
            throw HttpException::createFromCode(403);
        }

        $queue = UserHelper::getUser()->getQueue($task->task_id)->toArray();
        foreach ($queue as &$item) {
            $item['stan'] = explode(',', $item['stan']);
            $item['tests'] = $item['tests'] ? explode(',', $item['tests']) : [];
        }

        $tasks = TaskModel::select([ 'task_id', 'name' ])->where([
            'user_id' => $userId
        ])->orderBy('sort_order')->get();

        return $response->json([
            'queue' => TemplateHelper::render('components/queue', [ 'queue' => $queue ]),
            'taskTabs' => TemplateHelper::render('components/task_tabs', [
                'userId' => $userId,
                'tasks' => $tasks,
                'currentTask' => $task
            ])
        ]);
    }

    private function uploadFile(Request $request)
    {
        $errors = [];

        if ($request->files()->count() && SettingsHelper::isOlimpInProgress()) {
            $userFilename = $request->files()->get('userfile')['name'];
            $ext = mb_strtolower(pathinfo($userFilename)['extension'], "utf-8");
            $filename = uniqid(mt_rand(), false) . ".{$ext}";

            /** @var CompilerModel $compiler */
            $compiler = CompilerModel::select([ 'compiler_id' ])->where('ext', $ext)->first();
            if (is_null($compiler)) {
                $errors[] = TemplateHelper::text('incorrectExt') . $ext;
            }

            $uploadPath = UserHelper::getUser()->userFolder . "/{$request->param('task_id', 0)}";
            if (false == file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            if (empty($errors)) {
                if (move_uploaded_file($request->files()->get('userfile')['tmp_name'], "{$uploadPath}/{$filename}")) {
                    QueueModel::create([
                        'user_id' => UserHelper::getUser()->user_id,
                        'task_id' => $request->param('task_id', 0),
                        'user_filename' => $userFilename,
                        'filename' => $filename,
                        'compiler_id' => $compiler->compiler_id,
                        'upload_ip' => $request->server()->get('REMOTE_ADDR', '0.0.0.0')
                    ]);
                } else {
                    $errors[] = TemplateHelper::text('uploadError');
                }
            }
        }

        return implode('<br />', $errors);
    }
}
