<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 15:33
 */
namespace controllers\admin;

use helpers\ErrorHelper;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\TaskModel;

class TaskController extends BaseAdminController
{
    /** @var TaskModel|null $task */
    protected $task = null;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->header['js'][] = 'admin/task.js';
        $this->header['css'][] = 'admin/task.css';

        $taskId = $request->param('task_id', 0);
        $this->task = TaskModel::where('user_id', UserHelper::getUser()->user_id)->find($taskId);

        if (!is_null($this->task)) {
            $this->data['task'] = $this->task;
        }
    }

    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $task = TaskModel::select([ 'task_id' ])->where('user_id', UserHelper::getUser()->user_id)->orderBy('sort_order')->first();
        if (!is_null($task)) {
            return $response->redirect(UrlHelper::href("admin/task/{$task->task_id}"));
        }

        return $this->create($request, $response, $service, $app);
    }

    public function get(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (is_null($this->task)) {
            ErrorHelper::assert("Can't find task #{$this->task->task_id}");
            return $response->redirect(UrlHelper::href('admin'));
        }

        $this->data['tasks'] = TaskModel::select([ 'task_id', 'name' ])->where('user_id', UserHelper::getUser()->user_id)->orderBy('sort_order')->get();
        if ($app->optional['tests']) {
            $this->data['taskForm'] = TemplateHelper::render('admin/components/testsForm', $this->data);
        } else {
            $this->data['taskForm'] = TemplateHelper::render('admin/components/taskForm', $this->data);
        }

        return $this->render('task');
    }

    public function save(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (is_null($this->task)) {
            ErrorHelper::assert("Can't find task #{$this->task->task_id}");
            return $response->redirect(UrlHelper::href('admin'));
        }

        $taskInfo = $request->param('task', []);
        $taskInfo['is_enabled'] = $taskInfo['is_enabled'] ?: 0;
        $this->task->update($taskInfo);

        return $this->get($request, $response, $service, $app);
    }

    public function create(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->task = TaskModel::create([
            'name' => TemplateHelper::text('taskDefault'),
            'time_limit' => 100,
            'memory_limit' => 10,
            'max_score' => 100,
            'mulct' => 0,
            'user_id' => UserHelper::getUser()->user_id
        ]);
        return $response->redirect(UrlHelper::href("admin/task/{$this->task->task_id}"));
    }

    public function delete(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (is_null($this->task)) {
            ErrorHelper::assert("Can't find task #{$this->task->task_id}");
            return $response->redirect(UrlHelper::href('admin'));
        }
        $this->task->delete();

        return $this->index($request, $response, $service, $app);
    }
}
