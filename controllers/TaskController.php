<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 01.12.2017
 * Time: 12:11
 */
namespace controllers;

use helpers\UrlHelper;
use Klein\App;
use Klein\Exceptions\HttpException;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\TaskModel;

class TaskController extends BaseController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->data['tasks'] = TaskModel::select([ 'task_id', 'name' ])->orderBy('sort_order')->get();
        $task = TaskModel::select([ 'task_id', 'task' ])->find($request->param('task_id', 0));

        if (is_null($task)) {
            throw HttpException::createFromCode(404);
        }
        $this->data['currentTask'] = $task;
        return $this->render('task');
    }

    public function indexRedirect(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $task = TaskModel::select([ 'task_id' ])->first();
        return $response->redirect(UrlHelper::href("task/{$task->task_id}"));
    }
}
