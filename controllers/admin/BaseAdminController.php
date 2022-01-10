<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2017
 * Time: 14:46
 */
namespace controllers\admin;

use controllers\BaseController;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;

abstract class BaseAdminController extends BaseController
{
    const ROOT_FOLDER = 'admin';

    protected function render($template)
    {
        $this->header['userInfo'] = TemplateHelper::render('components/user', [
            'user' => UserHelper::getUser(),
            'showScore' => false,
            'links' => [
                [ 'link' => UrlHelper::href('task'), 'text' => TemplateHelper::text('tasks') ],
                [ 'link' => UrlHelper::href('rating'), 'text' => TemplateHelper::text('rating') ]
            ]
        ]);
        $this->header['css'][] = 'admin/style.css';
        return parent::render($template);
    }
}
