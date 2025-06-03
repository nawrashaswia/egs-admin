<?php
return [
    'views' => [
        '/general/attachment_manager/settings_ui'         => 'modules/general_module/views/attachment_manager/settings_ui',
        '/general/attachment_manager/attachment_rule_add' => 'modules/general_module/views/attachment_manager/attachment_rule_add',
        '/general/attachment_manager/edit_extensions_ui'  => 'modules/general_module/views/attachment_manager/edit_extensions_ui',
    ],

    'controllers' => [
        [
            'method' => 'post',
            'path'   => '/general/attachment_manager/save_rule',
            'file'   => 'attachment_manager/extension_rules_save.php',
        ],
        [
            'method' => 'post',
            'path'   => '/general/attachment_manager/edit_extensions',
            'file'   => 'attachment_manager/edit_extensions.php',
        ],
        [
            'method' => 'get',
            'path'   => '/general/attachment_manager/delete_extension_rule',
            'file'   => 'attachment_manager/delete_extension_rule.php',
        ],
        [
            'method' => 'get',
            'path'   => '/general/attachment_manager/toggle_extension_rule',
            'file'   => 'attachment_manager/toggle_extension_rule.php',
        ],
        [
            'method' => 'post',
            'path'   => '/ajax/general_module/handle_attachment_upload',
            'file'   => 'ajax/general_module/handle_attachment_upload.php',
        ],
        [
            'method' => 'get',
            'path'   => '/ajax/general_module/get_attachment_list',
            'file'   => 'ajax/general_module/get_attachment_list.php',
        ],

        // ✅ Log Manager UI route
[
    'method' => 'get',
    'path'   => '/general/logmanager',
    'handler' => function () {
        require_once APP_PATH . '/helpers/general_module/logmanager/LogQueryHelper.php';
        require_once APP_PATH . '/modules/general_module/controllers/logmanager/LogManagerController.php';
        \Modules\general_module\Controllers\LogManager\LogManagerController::index();
    },
],
[
    'method' => 'post',
    'path'   => '/general/logmanager/toggle_trace_mode',
    'file'   => 'logmanager/toggle_trace_mode.php',
],

[
    'method' => 'get',
    'path'   => '/general/logmanager/construction',
    'handler' => function () {
        require_once APP_PATH . '/helpers/general_module/logmanager/LogQueryHelper.php';
        require_once APP_PATH . '/modules/general_module/controllers/logmanager/LogManagerController.php';
        \Modules\general_module\Controllers\LogManager\LogManagerController::construction();
    },
],

[
    'method' => 'post',
    'path'   => '/general/logmanager/delete_all',
    'handler' => function () {
        require_once APP_PATH . '/modules/general_module/controllers/logmanager/LogManagerController.php';
        \Modules\general_module\Controllers\LogManager\LogManagerController::deleteAll();
    },
],

[
    'method' => 'post',
    'path'   => '/general/logmanager/delete_all_construction',
    'handler' => function () {
        require_once APP_PATH . '/modules/general_module/controllers/logmanager/LogManagerController.php';
        \Modules\general_module\Controllers\LogManager\LogManagerController::deleteAllConstruction();
    },
],

[
    'method' => 'get',
    'path'   => '/ajax/general_module/fetch_construction_logs',
    'handler' => function () {
        require_once APP_PATH . '/modules/general_module/controllers/logmanager/LogManagerController.php';
        \Modules\general_module\Controllers\LogManager\LogManagerController::ajaxFetchConstructionLogs();
    },
],

    ],
];
