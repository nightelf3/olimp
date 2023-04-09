<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 09.01.2019
 * Time: 13:08
 */
namespace helpers;

use Illuminate\Database\Capsule\Manager as Capsule;

class RatingHelper extends BaseHelper
{
    public static function generate($adminId, $tasks)
    {
        $prefix = ConfigHelper::get('database', 'prefix');

        $oldScore = SettingsHelper::param('useLastResults', false) ? '`users`.`old_score`' : '0';
        $sql = "
            SELECT `users`.`username`, `users`.`name`, `users`.`surname`, `users`.`score`, `users`.`mulct`, {$oldScore} AS `old_score_new`,
                `tasks`.`task_id`, `tasks`.`tests_count`,
                `users`.`class`, `users`.`school`,
                `queue`.`queue_id`, `queue`.`stan`, `queue`.`try`,
                `users`.`is_admin`
            FROM (
                SELECT *, COUNT(queue_id) AS `try`
                FROM (
                    SELECT `queue_id`, `stan`, `queue`.`task_id`, `queue`.`user_id`
                    FROM `{$prefix}queue` AS `queue`
                    INNER JOIN `{$prefix}tasks` AS `tasks` ON `tasks`.`task_id` = `queue`.`task_id`
                    WHERE `tasks`.`user_id` = '{$adminId}' AND `stan` NOT IN ('0', '1', '2', '4')
                    ORDER BY queue_id DESC
                    LIMIT 18446744073709551615
                ) AS tmp
                GROUP BY `task_id`, `user_id`
            ) AS `queue`
            INNER JOIN `{$prefix}tasks` AS `tasks` ON `tasks`.`task_id` = `queue`.`task_id`
            RIGHT JOIN `{$prefix}users` AS `users` ON `queue`.`user_id` = `users`.`user_id`
            ORDER BY `queue_id` IS NULL, `users`.`score` + `old_score_new` - `users`.`mulct` DESC, `users`.`class` + 0 DESC, `queue_id` ASC, `users`.`username`
        ";
        $db = Capsule::connection('default');
        $results = $db->select($db->raw($sql));

        $arr = [];
        foreach ($results as $row) {
            if ($row->is_admin) {
                continue;
            }

            if (!isset($arr[$row->username])) {
                $arr[$row->username] = [
                    'login' => $row->username,
                    'name' => "{$row->surname}&nbsp;{$row->name}",
                    'shtraff' => (int)$row->mulct,
                    'score' => ((int)$row->score + (int)$row->old_score_new),
                    'class' => $row->class,
                    'school' => $row->school,
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
