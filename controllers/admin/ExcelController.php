<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 15:33
 */
namespace controllers\admin;

use helpers\UrlHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\UserModel;

class ExcelController extends BaseAdminController
{
    public function import(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        return 'TODO: add a proper check';

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
