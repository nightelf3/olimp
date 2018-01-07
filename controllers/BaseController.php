<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:33
 */
namespace controllers;

use helpers\TemplateHelper;
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
        $this->data['header'] = TemplateHelper::render($this::ROOT_FOLDER . '/common/header', $this->header);
        $this->data['footer'] = TemplateHelper::render($this::ROOT_FOLDER . '/common/footer');

        return TemplateHelper::render($this::ROOT_FOLDER . "/{$template}", $this->data);
    }
}
