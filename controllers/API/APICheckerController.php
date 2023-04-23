<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 09.04.2023
 * Time: 19:41
 */
namespace controllers\API;

use helpers\SettingsHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CheckerModel;
use models\QueueModel;
use models\UserModel;

class APICheckerController
{
    public function register(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if (is_null($request->param('username')) || is_null($request->param('password')) || is_null($request->param('checkername'))) {
            return $this->jsonError($response, 400, "Registration parameters are not specified");
        }

        $user = UserModel::where([
            'username' => $request->param('username'),
            'guid' => $request->param('password'),
            'is_admin' => true
        ])->first();
        if (is_null($user)) {
            return $this->jsonError($response, 403, "Authentication failed");
        }

        $checker = CheckerModel::where([
            'name' => $request->param('checkername'),
            'user_id' => $user->user_id
        ])->first();
        if (is_null($checker)) {
            $checker = CheckerModel::create([
                'name' => $request->param('checkername'),
                'user_id' => $user->user_id,
                'token' => SettingsHelper::guid()
            ]);
        } else {
            $checker->token = SettingsHelper::guid();
            $checker->update();
        }

        if (is_null($checker)) {
            return $this->jsonError($response, 500, "Registration failed");
        }

        return $response->json([ 'checkertoken' => $checker->token ]);
    }

    public function message(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $checker = $this->getChecker($request);
        if (is_null($checker)) {
            return $response->json([ 'message' => 'logout' ]);
        }

        if (!$checker->is_active) {
            return $response->json([ 'message' => 'idle' ]);
        }

        $queue = QueueModel::join('tasks', 'tasks.task_id', '=', 'queue.task_id')
            ->where([
                'tasks.user_id' => $checker->user_id,
                'tasks.is_enabled' => true,
                'queue.stan' => 0
            ])->take($request->param('limit', 1))->get();
        if ($queue->isEmpty()) {
            return $response->json([ 'message' => 'idle' ]);
        }

        $queue->update([ 'stan' => 1 ]);
        $json = [
            'tasks' => [],
            'message' => 'task'
        ];
        foreach ($queue as $item) {
            $jsonItem = [
                'time_limit' => $item->time_limit,
                'memory_limit' => $item->memory_limit,
                'text' => 'TODO',
                'tests' => []
            ];

            $input = explode("\r\n\r\n", $item->input);
            $output = explode("\r\n\r\n", $item->output);
            $count = (int)$item->tests_count;
            for ($i = 0; $i < $count; $i++) {
                $jsonItem['tests'][] = [
                    'input' => $input[$i],
                    'output' => $output[$i],
                ];
            }

            $json['tasks'][] = $jsonItem;
        }
        return $response->json($json);
    }

    public function logout(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $checker = $this->getChecker($request);
        if (!is_null($checker)) {
            $checker->delete();
        }
        return $response->json([ 'successed' => 1 ]);
    }

    protected function getChecker(Request $request)
    {
        if (is_null($request->param('checkername')) || is_null($request->param('checkertoken'))) {
            return null;
        }

        return CheckerModel::where([
            'name' => $request->param('checkername'),
            'token' => $request->param('checkertoken')
        ])->first();
    }

    protected function jsonError(Response $response, int $code, $message) {
        $response->code($code);
        return $response->json([
            'error' => $message
        ]);

    }
}
