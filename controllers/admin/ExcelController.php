<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 15:33
 */
namespace controllers\admin;

use helpers\RatingHelper;
use helpers\SessionHelper;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Illuminate\Contracts\Session\Session;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\QueueModel;
use models\TaskModel;
use models\UserModel;

class ExcelController extends BaseAdminController
{
    public function import(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $id = 0;
        $adminUser = UserModel::where('username', 'olimp')->first();
        if (!$adminUser)
        {
            $adminUser = new UserModel([
                'username' => 'olimp',
                'email' => 'olimp@rshu.edu.ua',
                'class' => 'Admin',
                'school' => 'Admin',
                'phone' => "(000) 000-00-00",
                'name' => 'Admin',
                'surname' => 'Admin',
                'score' => 0,
                'mulct' => 0,
                'old_score' => 0,
                'is_enabled' => 1,
                'live_update' => 1
            ]);
        }
        $adminUser->password = $this->generateRandomString();
        $users = [ $adminUser ];

        $objPHPExcel = \PHPExcel_IOFactory::load(UrlHelper::path('data/users.xls'));
        foreach ($objPHPExcel->getActiveSheet()->toArray(null, true, true, true) as $user) {
            $name = explode(' ', $user['A'], 3);
            $login = 'user' . $this->zerofill(++$id, 3);
            $users[] = new UserModel([
                'username' => $login,
                'password' => $this->generateRandomString(),
                'email' => "{$login}@rshu.edu.ua",
                'class' => $user['B'],
                'school' => $user['C'],
                'phone' => "(000) 000-00-00",
                'name' => $name[1],
                'surname' => $name[0],
                'score' => 0,
                'mulct' => 0,
                'old_score' => 0,
                'is_enabled' => 0,
                'live_update' => 0
            ]);
        }

        if (!empty($users)) {
            UserModel::where('is_admin', 0)->delete();
            foreach ($users as $user) {
                /** @var $user UserModel */
                $this->data['users'][] = $user->toArray();
                $user->generateGUID()->hashPassword()->save();
            }
        }
        return $this->render('import');
    }

    public function results(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        // create a PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // get tasks and order them
        $tasks = TaskModel::where([
            'user_id' => UserHelper::getUser()->user_id
        ])->orderBy('sort_order')->get();

        // get rating table
        $rating = RatingHelper::generate(UserHelper::getUser()->user_id, $tasks);

        // get classes and create a sheet for each of them
        $classes = UserModel::distinct('class')->orderByRaw('ABS(class) ASC')->select([ 'class' ])->get();
        while (count($classes) > $objPHPExcel->getSheetCount()) {
            $objPHPExcel->createSheet();
        }

        $sheetNum = 0;
        foreach ($classes as $class) {
            $objPHPExcel->setActiveSheetIndex($sheetNum++);
            $sheet = $objPHPExcel->getActiveSheet();

            $col = 'A';
            $row = 1;

            $sheet->setTitle("{$class->class} клас");
            $sheet->SetCellValue($col++.$row, '#');
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('fullname'));
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('school'));
            foreach ($tasks as $task) {
                $sheet->SetCellValue($col++.$row, $task->name);
            }
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('mulct'));
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('score'));
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('result'));

            foreach ($rating as $line) {
                if ($line['class'] != $class->class) {
                    continue;
                }

                $col = 'A';
                $row++;
                $sheet->SetCellValue($col++.$row, $row - 1);
                $sheet->SetCellValue($col++.$row, str_replace('&nbsp;', ' ', $line['name']));
                $sheet->SetCellValue($col++.$row, $line['school']);
                foreach ($tasks as $task) {
                    $curTask = $line['tasks'][$task->task_id];
                    $sheet->SetCellValue($col++.$row, '-' == $curTask['ok'] ? '-' : (isset($_GET['percents']) ? $curTask['ok'] : substr($curTask['ok'], 0, -1)) . (isset($_GET['try']) ? " ({$curTask['try']})" : ''));
                }
                $sheet->SetCellValue($col++.$row, $line['shtraff']);
                $sheet->SetCellValue($col++.$row, $line['score']);
                $sheet->SetCellValue($col++.$row, max($line['score'] - $line['shtraff'], 0));
            }
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="results.xls"');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function _results(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        // create a PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // get classes and create a sheet for each of them
        $classes = UserModel::distinct('class')->select([ 'class' ])->get();
        while (count($classes) > $objPHPExcel->getSheetCount()) {
            $objPHPExcel->createSheet();
        }

        // get enabled tasks by sort_order
        $tasks = TaskModel::where([
            'user_id' => UserHelper::getUser()->user_id,
            'is_enabled' => 1
        ])->orderBy('sort_order')->get();

        $sheetNum = 0;
        foreach ($classes as $class) {
            $objPHPExcel->setActiveSheetIndex($sheetNum++);
            $sheet = $objPHPExcel->getActiveSheet();

            $col = 'A';
            $row = 1;

            $sheet->setTitle("{$class->class} клас");
            $sheet->SetCellValue($col++.$row, '#');
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('fullname'));
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('school'));
            foreach ($tasks as $task) {
                $sheet->SetCellValue($col++.$row, $task->name);
            }
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('mulct'));
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('score'));
            $sheet->SetCellValue($col++.$row, TemplateHelper::text('result'));

            $users = UserModel::where('class', $class->class)->get();
            foreach ($users as $user) {
                /** @var UserModel $user */
                $col = 'A';
                $row++;
                $sheet->SetCellValue($col++.$row, $row - 1);
                $sheet->SetCellValue($col++.$row, "{$user->surname} {$user->name}");
                $sheet->SetCellValue($col++.$row, $user->school);

                foreach ($tasks as $task) {
                    /** @var TaskModel $task */

                    /** @var QueueModel $item */
                    $item = QueueModel::where([
                        'user_id' => $user->user_id,
                        'task_id' => $task->task_id
                    ])->orderBy('queue_id', 'desc')->first();

                    $score = 0;
                    if (is_null($item)) {
                        $score = 0;
                    } elseif ($item->stan == '9') {
                        $score = $task->max_score;
                    } elseif ($item->stan != '3' && $item->stan != '10') {
                        $score = round(((int)$task->tests_count - count(explode(',', $item->stan))) / ((float)$task->tests_count) * (int)$task->max_score);
                    }

                    $str = "{$score}";
                    if (isset($_GET['count'])) {
                        $count = QueueModel::where([
                            'user_id' => $user->user_id,
                            'task_id' => $task->task_id
                        ])->whereNotIn('stan', ['3', '9'])->count();
                        $str .= " ($count)";
                    }
                    $sheet->SetCellValue($col++.$row, $str);
                }

                $sheet->SetCellValue($col++.$row, $user->mulct);
                $sheet->SetCellValue($col++.$row, $user->score);
                $sheet->SetCellValue($col++.$row, max($user->score - $user->mulct, 0));
            }
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="results.xls"');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    private function zerofill($num, $zerofill = 5)
    {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }

    private function generateRandomString($length = 8)
    {
        $characters = '023456789abcdefghijkmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
