<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:38
 */
namespace helpers;

use Klein\Klein;
use models\QueueModel;
use models\UserModel;

class ControllerHelper extends BaseHelper
{
    /**
     * @var Klein
     */
    protected static $klein = null;

    public static function getKlein()
    {
        if (null == self::$klein) {
            self::$klein = new Klein();
        }

        return self::$klein;
    }

    public static function getControllerClass($name, $sufix = 'Controller')
    {
        $namespaces = explode("\\", $name);
        $controller = ucfirst(array_pop($namespaces)) . $sufix;
        if ($namespaces) {
            return "controllers\\" . implode("\\", $namespaces) . "\\{$controller}";
        }

        return "controllers\\{$controller}";
    }

    public static function getComponent($name, $action = 'index', $data = [])
    {
        $class = \helpers\ControllerHelper::getControllerClass($name, 'Component');
        $action = $action ?: 'index';
        $controller = new $class($data);
        $klein = self::getKlein();

        if (method_exists($controller, $action)) {
            return $controller->$action($klein->request(), $klein->response(), $klein->service(), $klein->app());
        }

        ErrorHelper::assert(false, "Can't find {$controller}->{$action}");
        return $klein->response()->code(404);
    }

    public static function updateAllResults()
    {
        foreach (UserModel::get() as $user) {
            self::updateResults($user);
        }
    }

    public static function updateResults(UserModel $user)
    {
        $calculatedTasks = [];
        $results = [
            'score' => 0,
            'mulct' => 0
        ];
        $queue = QueueModel::join('tasks', 'tasks.task_id', '=', 'queue.task_id')
            ->where('queue.user_id', $user->user_id)
            ->orderBy('queue_id', 'desc')->get();

        foreach ($queue as $row) {
            if (in_array($row->stan, [ '0', '1', '2', '3', '4' ])) {
                continue;
            }

            if ('9' == $row->stan && !in_array($row->task_id, $calculatedTasks)) {
                // succeed
                $results['score'] += $row->max_score;
                $calculatedTasks[] = $row->task_id;
            } elseif ('10' == $row->stan) {
                // incorrect output
                $results['mulct'] += $row->mulct;
            } else {
                // another type of error
                if (!in_array($row->task_id, $calculatedTasks)) {
                    $results['score'] += round(((float)$row->max_score / max((float)$row->tests_count, 1)) * max((int)$row->tests_count - count(explode(',', $row->stan)), 0));
                    $calculatedTasks[] = $row->task_id;
                }
                $results['mulct'] += $row->mulct;
            }
        }

        $results['score'] = round($results['score']);
        $user->update($results);
    }
}