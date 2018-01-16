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
use models\CompilationErrorModel;
use models\CompilerModel;
use models\QueueModel;
use models\TaskModel;

class TaskController extends BaseController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (!SettingsHelper::isOlimpStarts() && !UserHelper::isAdmin()) {
            return $response->redirect(UrlHelper::href('task'));
        }

        $this->data['tasks'] = TaskModel::select([ 'task_id', 'name' ])->orderBy('sort_order')->get();

        /** @var TaskModel $task */
        $task = TaskModel::select([ 'task_id', 'task' ])->find($request->param('task_id', 0));
        if (is_null($task)) {
            throw HttpException::createFromCode(404);
        }

        if (SettingsHelper::isOlimpInProgress()) {
            $this->data['uploadForm'] = TemplateHelper::render('components/upload', [
                'error' => $this->uploadFile($request),
                'task_id' => $task->task_id
            ]);
        }

        $queue = UserHelper::getUser()->getQueue($task->task_id)->toArray();
        foreach ($queue as &$item) {
            $item['stan'] = explode(',', $item['stan']);
            $item['tests'] = $item['tests'] ? explode(',', $item['tests']) : [];
        }
        $this->data['queueInfo'] = TemplateHelper::render('components/queue', [ 'queue' => $queue ]);
        $this->data['currentTask'] = $task;

        return $this->render('task');
    }

    public function task(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (SettingsHelper::isOlimpStarts()) {
            $task = TaskModel::select([ 'task_id' ])->first();
            return $response->redirect(UrlHelper::href("task/{$task->task_id}"));
        }
        $this->data['username'] = UserHelper::getUser()->username;
        return $this->render('wait');
    }

    public function compile(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $error = CompilationErrorModel::join('queue', 'queue.queue_id', '=', 'compilation_errors.queue_id')
            ->join('users', 'queue.user_id', '=', 'users.user_id')
            ->where([
                'queue.queue_id' => $request->param('queue_id', 0),
                'users.user_id' => UserHelper::getUser()->user_id
            ])->first();
        if (is_null($error)) {
            ErrorHelper::assert("You haven't access to this log.");
            return $response->redirect(UrlHelper::href('task'));
        }

        $this->data['compileLog'] = $error->error;
        return $this->render('compile');
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
