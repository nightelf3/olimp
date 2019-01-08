<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:33
 */
namespace controllers;

use helpers\SettingsHelper;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\Request;

abstract class BaseController
{
    const ROOT_FOLDER = 'pages';

    protected $header = [
        'css' => [],
        'js' => []
    ];

    protected $data = [];

    public function __construct(Request $request)
    {
    }

    protected function render($template)
    {
        $this->header['user'] = UserHelper::isAuthenticated() ? UserHelper::getUser() : null;
        $this->header['enableRegistration'] = SettingsHelper::param('enableRegistration', false);
        if (UserHelper::isAuthenticated()) {
            $this->header['js'][] = 'user-card.js';
            if (SettingsHelper::isOlimpInProgress()) {
                $this->header['css'][] = 'timer.css';
                $this->header['js'][] = 'timer.js';
                $this->header['timer'] = TemplateHelper::render('components/timer', [
                    'olimpStart' => date("Y-m-d H:i:s", SettingsHelper::param('olimp_start', 0)),
                    'olimpContinuity' => SettingsHelper::param('olimp_duration', 0)
                ]);
            }

            $this->header['userForm'] = TemplateHelper::render('components/user', [
                'user' => UserHelper::getUser()
            ]);
        }

        $this->data['header'] = TemplateHelper::render($this::ROOT_FOLDER . '/common/header', $this->header);
        $this->data['footer'] = TemplateHelper::render($this::ROOT_FOLDER . '/common/footer', [
            'JSSettings' => [
                'liveUpdate' => UserHelper::isAuthenticated() ? UserHelper::getUser()->live_update : false
            ]
        ]);

        $this->data['isAuthenticated'] = UserHelper::isAuthenticated();

        return TemplateHelper::render($this::ROOT_FOLDER . "/{$template}", $this->data);
    }
}
