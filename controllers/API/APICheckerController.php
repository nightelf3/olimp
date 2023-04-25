<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 09.04.2023
 * Time: 19:41
 */
namespace controllers\API;

use helpers\classes\enums\TaskStatusEnum;
use helpers\ControllerHelper;
use helpers\SettingsHelper;
use helpers\UrlHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\CheckerModel;
use models\CompilationErrorModel;
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

        return $response->json([
            'successed' => 1,
            'checkertoken' => $checker->token
        ]);
    }

    public function message(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $checker = $this->getChecker($request);
        if (is_null($checker)) {
            return $response->json([
                'successed' => 1,
                'message' => 'logout'
            ]);
        }

        if (!$checker->is_active) {
            return $response->json([
                'successed' => 1,
                'message' => 'authentification'
            ]);
        }

        $queue = QueueModel::select([ '*', 'tasks.name as task_name'])
            ->join('tasks', 'tasks.task_id', '=', 'queue.task_id')
            ->join('users', 'users.user_id', '=', 'queue.user_id')
            ->join('compilers', 'compilers.compiler_id', '=', 'queue.compiler_id')
            ->where([
                'tasks.user_id' => $checker->user_id,
                'tasks.is_enabled' => true,
                'queue.stan' => TaskStatusEnum::NoAction
            ])->take($request->param('limit', 1))->get();
        if ($queue->isEmpty()) {
            return $response->json([
                'successed' => 1,
                'message' => 'idle'
            ]);
        }

        // update stan
        QueueModel::whereIn('queue_id', $queue->map(function ($e) { return $e->queue_id; }))->update([
            'stan' => TaskStatusEnum::InQueue
        ]);

        $json = [
            'successed' => 1,
            'tasks' => [],
            'message' => 'task'
        ];
        foreach ($queue as $item) {
            $fileData = file_get_contents(UrlHelper::path("users/{$item->username}/{$item->task_id}/{$item->filename}"));
            $jsonItem = [
                'queue_id' => (int)$item->queue_id,
                'use_files' => strcasecmp($item->input_file, 'stdin') != 0 || strcasecmp($item->output_file, 'stdout'),
                'input_file' => $item->input_file,
                'output_file' => $item->output_file,
                'time_limit' => (int)$item->time_limit,
                'memory_limit' => (int)$item->memory_limit,
                'text' => base64_encode($fileData),
                'extension' => $item->ext,
                'username' => $item->username,
                'task_name' => $item->task_name,
                'tests' => []
            ];

            $input = explode("\r\n\r\n", $item->input);
            $output = explode("\r\n\r\n", $item->output);
            $count = (int)$item->tests_count;
            for ($i = 0; $i < $count; $i++) {
                $jsonItem['tests'][] = [
                    'input' => base64_encode($input[$i]),
                    'output' => base64_encode($output[$i]),
                ];
            }

            $json['tasks'][] = $jsonItem;
        }
        return $response->json($json);
    }

    public function results(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        /** @var CheckerModel $checker */
        $checker = $this->getChecker($request);
        if (is_null($checker)) {
            return $this->jsonError($response, 403, "Authentication failed");
        }

        /** @var QueueModel $queue */
        $queue = $request->param('queue_id') ? QueueModel::find($request->param('queue_id')) : null;
        if (is_null($queue)) {
            return $this->jsonError($response, 400, "Can't find the queue item");
        }

        $states = $request->param('states');
        if (is_null($queue)) {
            return $this->jsonError($response, 400, "Can't find the queue item");
        }

        $updateRating = false;
        $states = array_map(function ($e) {
            return new TaskStatusEnum((int)$e, TaskStatusEnum::ResponseError);
        }, explode(',', $states));
        if (count($states) == 1) {
            $state = $states[0];
            switch ($state->value())
            {
            case TaskStatusEnum::InQueue:
                return $this->jsonError($response, 400, "Item is already in the queue");

            case TaskStatusEnum::InvalidOutputStream:
            case TaskStatusEnum::Succeed:
                $updateRating = true;
                $queue->update([
                    'stan' => $state->value(),
                    'tests' => null
                ]);
                break;

            case TaskStatusEnum::NoAction:
            case TaskStatusEnum::Compiling:
            case TaskStatusEnum::InProgress:
                $queue->update([
                    'stan' => $state->value(),
                    'tests' => null
                ]);
                break;

            case TaskStatusEnum::CompilingError:
                $message = $request->param('message');
                if (is_null($message)) {
                    return $this->jsonError($response, 400, "Compilation message is missing");
                }
                $message = base64_decode($message, true);
                if (!$message) {
                    return $this->jsonError($response, 400, "Compilation message is not valid");
                }
                CompilationErrorModel::create([
                    'queue_id' => $queue->queue_id,
                    'error' => $message
                ]);
                break;
            }
        } else {
            $message = $request->param('message');
            if (is_null($message)) {
                return $this->jsonError($response, 400, "Response message is missing");
            }
            $tests = array_map(function ($e) {
                return (int)$e;
            }, explode(',', $message));
            if (count($tests) != count($states)) {
                return $this->jsonError($response, 400, "States are not valid");
            }

            $updateRating = true;
            $queue->update([
                'stan' => implode(',', array_map(function ($e) { return $e->value(); }, $states)),
                'tests' => implode(',', $tests),
            ]);
        }

        if ($updateRating) {
            ControllerHelper::updateResults($queue->user());
        }

        return $response->json([ 'successed' => 1 ]);
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
            'successed' => 0,
            'error' => $message
        ]);
    }
}
