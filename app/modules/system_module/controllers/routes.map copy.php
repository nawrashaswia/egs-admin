<?php

return [
  'views' => [
    '/system' => 'modules/system_module/views/index',
    '/system/settings' => 'modules/system_module/views/general_UI',
    '/system/maintenance' => 'modules/system_module/views/maintenance_UI',
    '/system/modules' => 'modules/system_module/views/modules_UI',
    '/system/users' => 'modules/system_module/views/users_UI',
    '/system/permissions' => 'modules/system_module/views/permissions_UI',
    '/system/users/add' => 'modules/system_module/views/users_add_UI',
    '/system/users/edit' => 'modules/system_module/views/users_edit_UI',
    '/system/router-manager' => 'modules/system_module/views/route_builder_UI',
    '/system/users/list' => 'modules/system_module/views/users/list_UI',
  ],

  'controllers' => [
    ['method' => 'post', 'path' => '/system/users/save', 'file' => 'save_user.php'],
    ['method' => 'get',  'path' => '/system/users/delete', 'file' => 'delete_user.php'],
    ['method' => 'post', 'path' => '/system/users/update', 'file' => 'update_user.php'],
    ['method' => 'post', 'path' => '/system/users/password', 'file' => 'change_password.php'],
    ['method' => 'post', 'path' => '/system/router-manager/save', 'file' => 'route_builder_save.php'],
    
    ['method' => 'post', 'path' => '/system/users/add', 'file' => 'users/add.php'],
    ['method' => 'post', 'path' => '/system/users/edit', 'file' => 'users/edit.php'],
    ['method' => 'get',  'path' => '/system/users/delete', 'file' => 'users/delete.php'],
    
    // Maintenance tools
    ['method' => 'post', 'path' => '/system/maintenance/folder_export', 'file' => 'maintenance/folder_export.php'],
    ['method' => 'post', 'path' => '/system/maintenance/save_export_selection', 'file' => 'maintenance/save_export_selection.php'],
    ['method' => 'get',  'path' => '/system/maintenance/save_export_selection', 'file' => 'maintenance/save_export_selection.php'],
    ['method' => 'get',  'path' => '/system/maintenance/full_zip_backup', 'file' => 'maintenance/full_zip_backup.php'],
    ['method' => 'get',  'path' => '/system/maintenance/db_info_export', 'file' => 'maintenance/db_info_export.php'],
    ['method' => 'get',  'path' => '/system/maintenance/db_backup', 'file' => 'maintenance/db_backup.php'],
    ['method' => 'post', 'path' => '/system/maintenance/db_restore', 'file' => 'maintenance/db_restore.php'],
    ['method' => 'get',  'path' => '/system/maintenance/full_system_backup', 'file' => 'maintenance/full_system_backup.php'],
  ],
];

 