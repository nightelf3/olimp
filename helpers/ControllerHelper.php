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
        $res_arr = [
            'res'		=> 0,
            'shtraff'	=> 0
        ];
        $res_tmp = [];
        $queue1 = QueueModel::join('tasks', 'tasks.task_id', '=', 'queue.task_id')->where('queue.user_id', $user->user_id)
            ->orderBy('queue_id', 'desc')->get();
        foreach ($queue1 as $row) {
            if (preg_match("/([35678]|10)/u", $row->stan)) {
                if (!isset($res_tmp[$row->task_id])) {
                    if ('10' != $row->stan && '3' != $row->stan) {
                        $res_arr['res'] += round(((float)$row->max_score / max((float)$row->tests_count, 1)) * max((int)$row->tests_count - count(explode(',', $row->stan)), 0));
                    }
                    $res_tmp[$row->task_id] = (int)('3' != $row->stan);
                } elseif ('3' != $row->stan) {
                    $res_arr['shtraff'] += $row->mulct;
                }
            }
            elseif (!isset($res_tmp[$row->task_id]) && '9' == $row->stan) {
                $res_arr['res'] += $row->max_score;
                $res_tmp[$row->task_id] = 0;
            }
        }
        $user->update([
            'score' =>  round($res_arr['res']),
            'mulct' => $res_arr['shtraff']
        ]);
    }
}