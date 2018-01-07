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
    [['GET', 'POST'], '/task/[i:task_id]', 'task#index', [ 'login' => true ]],
    [['GET'], '/task', 'task#task', [ 'login' => true ]],
    [['GET', 'POST'], '/user', 'account#user', [ 'login' => true ]],
    [['GET'], '/rating', 'rating#index', [ 'login' => true ]],

    [['GET'], '/admin', 'index#index', [ 'admin' => true ]], // do we need this one?
    [['GET'], '/admin/timer', 'timer#index', [ 'admin' => true ]],
    [['POST'], '/admin/timer', 'timer#save', [ 'admin' => true ]],

    [['GET'], '/admin/task', 'task#index', [ 'admin' => true ]],
    [['GET'], '/admin/task/new', 'task#create', [ 'admin' => true ]],
    [['GET'], '/admin/task/[i:task_id]', 'task#get', [ 'admin' => true ]],
    [['POST'], '/admin/task/[i:task_id]', 'task#save', [ 'admin' => true ]],
    [['GET'], '/admin/task/[i:task_id]/tests', 'task#get', [ 'admin' => true, 'optional' => [ 'tests' => true ] ]],
    [['POST'], '/admin/task/[i:task_id]/tests', 'task#save', [ 'admin' => true, 'optional' => [ 'tests' => true ] ]],
    [['GET'], '/admin/task/[i:task_id]/delete', 'task#delete', [ 'admin' => true ]],
];