<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 03.12.2017
 * Time: 12:44
 */
namespace controllers;

use helpers\classes\enums\TaskStatusEnum;
use helpers\ConfigHelper;
use helpers\SettingsHelper;
use helpers\UrlHelper;
use Illuminate\Database\Capsule\Manager as Capsule;
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
        $userId = $request->param('user_id', 0);
        $tasks = TaskModel::select([ 'task_id', 'name' ])
            ->where('user_id', $userId)->orderBy('sort_order')->get();
        $this->data['table'] = $this->getRatingTable($userId, $tasks);
        $this->data['tasks'] = $tasks;
        $this->data['showLastResults'] = SettingsHelper::param('useLastResults', false);

        return $this->render('rating');
    }

    private function getRatingTable($adminId, $tasks)
    {
        $prefix = ConfigHelper::get('database', 'prefix');

        $oldScore = SettingsHelper::param('useLastResults', false) ? '`users`.`old_score`' : '0';
        $sql = "
            SELECT `users`.`username`, `users`.`name`, `users`.`surname`, `users`.`score`, `users`.`mulct`, {$oldScore} AS `old_score_new`,
                `tasks`.`task_id`, `tasks`.`tests_count`,
                `queue`.`queue_id`, `queue`.`stan`, `queue`.`try`
            FROM (
                SELECT *, COUNT(queue_id) AS `try`
                FROM (
                    SELECT `queue_id`, `stan`, `queue`.`task_id`, `queue`.`user_id`
                    FROM `{$prefix}queue` AS `queue`
                    INNER JOIN `{$prefix}tasks` AS `tasks` ON `tasks`.`task_id` = `queue`.`task_id`
                    WHERE `tasks`.`user_id` = '{$adminId}' AND `stan` NOT IN ('0', '1', '2', '4')
                    ORDER BY queue_id DESC
                ) AS tmp
                GROUP BY `task_id`, `user_id`
            ) AS `queue`
            INNER JOIN `{$prefix}tasks` AS `tasks` ON `tasks`.`task_id` = `queue`.`task_id`
            RIGHT JOIN `{$prefix}users` AS `users` ON `queue`.`user_id` = `users`.`user_id`
            ORDER BY `queue_id` IS NULL, `users`.`score` + `old_score_new` - `users`.`mulct` DESC, `queue_id` ASC
        ";
        $db = Capsule::connection('default');
        $results = $db->select($db->raw($sql));

        $arr = [];
        foreach ($results as $row) {
            if (!isset($arr[$row->username])) {
                $arr[$row->username] = [
                    'login' => $row->username,
                    'name' => "{$row->name}&nbsp;{$row->surname}",
                    'shtraff' => (int)$row->mulct,
                    'score' => ((int)$row->score + (int)$row->old_score_new),
                    'tasks' => [],
                    'old_res' => (int)$row->old_score_new
                ];
            }

            if (is_null($row->stan)) {
                continue;
            }

            $arr[$row->username]['tasks'][$row->task_id]['try'] = $row->try;
            if ('9' == $row->stan) {
                // succeed
                $arr[$row->username]['tasks'][$row->task_id]['ok'] = '100%';
            } elseif (in_array($row->stan, ['3', '10'])) {
                // incorrect output
                $arr[$row->username]['tasks'][$row->task_id]['ok'] = '0%';
            } else {
                // another type of error
                $arr[$row->username]['tasks'][$row->task_id]['ok'] = round(((int)$row->tests_count - count(explode(',', $row->stan))) / ((float)$row->tests_count) * 100) . '%';
            }
        }

        // fill empty tasks
        foreach ($arr as &$row) {
            foreach ($tasks as $task) {
                if (!isset($row['tasks'][$task->task_id]))
                {
                    $row['tasks'][$task->task_id] = [
                        'try' => 0,
                        'ok' => '-'
                    ];
                }
            }
        }

        return $arr;
    }
}
