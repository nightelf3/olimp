<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 16.01.2018
 * Time: 21:58
 */
namespace controllers\admin;

use helpers\ControllerHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Exceptions\HttpException;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CompilationErrorModel;
use models\QueueModel;
use models\TaskModel;
use models\UserModel;

class CheckerController extends BaseAdminController
{
    public function queue(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $userAdmin = UserModel::where([
            'username' => $request->cookies()->get('alog', ''),
            'guid' => $request->cookies()->get('apass', ''),
            'is_admin' => true,
            'is_enabled' => true
        ])->first();
        if (!UserHelper::isAdmin() && is_null($userAdmin)) {
            throw HttpException::createFromCode(404);
        }

        if ($_GET['get'] == "file") {
            /** @var QueueModel $queue */
            $queue = QueueModel::join('users', 'users.user_id', '=', 'queue.user_id')->where('queue.queue_id', $_GET['id'])->first();

            $file = fopen(UrlHelper::path("users/{$queue->username}/{$queue->task_id}/{$queue->filename}"), "r");
            while (!feof($file)) {
            echo fgets($file);
            }
            fclose($file);

            exit();
        }
        echo '<html>
        <head>
            <title>SITE_NAME - GetQueue</title>
        </head>
        
        <body id="BodyId">';

        if ($_GET['get'] == "task")	{
            echo '<div id="superUniqId"></div>';
            ;
            foreach (TaskModel::get() as $task) {
                /** @var TaskModel $task */
                $count = (int)$task->tests_count;

                echo '<div class="task"><div class="task_time">'.(int)$task->time_limit.'</div><div class="task_memory">'.(int)$task->memory_limit.'</div><div class="task_num">'.$count.'</div>';

                $inp = explode("\r\n\r\n", $task->input);
                $out = explode("\r\n\r\n", $task->output);
                for ($i = 0; $i < $count; ++$i) {
                    echo '<div class="inp">'.$inp[$i].'</div><div class="out">'.$out[$i].'</div>';
                }
                echo '</div>';
            }
        } elseif ($_GET['get'] == "queue") {
            $limit = (isset($_GET['limit']) ? (int)$_GET['limit'] : 1);
            $queue = QueueModel::where('stan', 0)->take($limit)->get();
            foreach ($queue as $lol) {
                $lol->update([
                    'stan' => 1
                ]);
            }

            $bl = true;
            $tasks = TaskModel::get()->toArray();
            foreach ($queue as $row) {
                $zadId = array_search($row->task_id, array_column($tasks, 'task_id'));
                /** @var QueueModel $row */
                $lol = QueueModel::join('users', 'users.user_id', '=', 'queue.user_id')
                    ->join('compilers', 'compilers.compiler_id', '=', 'queue.compiler_id')
                    ->where('queue.queue_id', $row->queue_id)->first();
                echo '<div class="task"><div class="user_name">'.$lol->username.'</div><div class="zad_id">'.$zadId.'</div><div class="uni_id">'.$lol->queue_id.'</div><div class="type">.'.$lol->ext.'</div></div>';
                $bl = false;
            }

            if ($bl) {
                echo "We havn't tasks NOW! BITCH!";
            }
        } elseif ($_GET['get'] == "compilation") {
            /*0 - тільки закинута
                1 - в черзі
                2 - компілюється
                3 - помилка компіляції (месседж!)
                4 - скомпільовано/виконується
                5 - невірна відповідь
                6 - помилка виконання
                7 - дофіга пам'яті
                8 - дофіга часу
                9 - успіх*/
            $stan = $_GET['answer'];
            $tests = $_GET['test'];
            $id = (int)$_GET['id'];
            QueueModel::where('queue_id', $id)->update([
                'stan' => $stan,
                'tests' => $tests
            ]);

            if ('3' == $stan) {
                CompilationErrorModel::create([
                    'queue_id' => $id,
                    'error' => $_GET['message']
                ]);
            }

            /** @var UserModel $user */
            $user = UserModel::join('queue', 'queue.user_id', '=', 'users.user_id')->where('queue.queue_id', $id)->first();
            ControllerHelper::updateResults($user);
        }
        echo '</body>
        </html>';
    }
}
