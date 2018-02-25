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
    [['GET'], '/task', 'task#task', [ 'login' => true ]],
    [['GET'], '/task/[i:user_id]', 'task#task', [ 'login' => true ]],
    [['GET', 'POST'], '/task/[i:user_id]/[i:task_id]', 'task#index', [ 'login' => true ]],
    [['GET'], '/compile-log/[i:queue_id]', 'task#compile', [ 'login' => true ]],
    [['GET', 'POST'], '/user', 'account#user', [ 'login' => true ]],
    [['GET'], '/rating', 'rating#index', [ 'login' => true ]],
    [['GET'], '/rating/[i:user_id]', 'rating#get', [ 'login' => true ]],

    [['GET'], '/admin', 'info#index', [ 'admin' => true ]],
    [['GET'], '/admin/users', 'info#users', [ 'admin' => true ]],
    [['GET'], '/admin/sysinfo', 'info#sysinfo', [ 'admin' => true ]],
    [['POST'], '/admin/sysinfo/event', 'info#sysEvent', [ 'admin' => true ]],

    [['GET'], '/admin/timer', 'timer#index', [ 'admin' => true ]],
    [['POST'], '/admin/timer', 'timer#save', [ 'admin' => true ]],

    [['GET'], '/admin/task', 'task#index', [ 'admin' => true ]],
    [['GET'], '/admin/task/new', 'task#create', [ 'admin' => true ]],
    [['GET'], '/admin/task/[i:task_id]', 'task#get', [ 'admin' => true ]],
    [['POST'], '/admin/task/[i:task_id]', 'task#save', [ 'admin' => true ]],
    [['GET'], '/admin/task/[i:task_id]/tests', 'task#get', [ 'admin' => true, 'optional' => [ 'tests' => true ] ]],
    [['POST'], '/admin/task/[i:task_id]/tests', 'task#save', [ 'admin' => true, 'optional' => [ 'tests' => true ] ]],
    [['GET'], '/admin/task/[i:task_id]/delete', 'task#delete', [ 'admin' => true ]],

    //TODO: fix it
    [['GET'], '/admin/queue', 'admin\checker#queue'],
];