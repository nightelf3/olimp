<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:02
 */

return [
    [['GET', 'POST'], '/', 'index#index'],

    [['GET', 'POST'], '/registration', 'account#registration'],
    [['GET', 'POST'], '/forgot', 'account#forgot'],
    [['GET'], '/logout', 'account#logout'],
    [['GET'], '/task/[i:task_id]', 'task#index', [ 'login' => true ]],
    [['GET'], '/task', 'task#indexRedirect', [ 'login' => true ]],
];