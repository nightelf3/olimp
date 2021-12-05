<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 18.06.2017
 * Time: 3:50
 */

require_once(dirname(__FILE__) . '/includes/autoload.php');

$database = helpers\ConfigHelper::getDatabaseSettings();

return [
    'paths' => [
      'migrations' => helpers\UrlHelper::path(helpers\ConfigHelper::get('migration', 'path'))
    ],
    'environments' => [
        'default_migration_table' => helpers\ConfigHelper::get('migration', 'table'),
        'default_database' => 'default',
        'default' => [
            'adapter' => $database['driver'],
            'host' => $database['host'],
            'name' => $database['database'],
            'user' => $database['username'],
            'pass' => $database['password'],
            'port' => $database['port'],
            'charset' => $database['charset'],
            'collation' => $database['collation'],
            'table_prefix' => $database['prefix']
        ]
    ],
    'version_order' => 'creation'
];