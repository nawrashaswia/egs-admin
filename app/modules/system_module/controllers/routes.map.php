<?php

return array (
  'views' => 
  array (
    '/system' => 'modules/system_module/views/index',
    '/system/dddd' => 'modules/system_module/views/DDDD.php',
    '/system/maintenance' => 'modules/system_module/views/maintenance_UI',
    '/system/modules' => 'modules/system_module/views/modules_UI',
    '/system/permissions' => 'modules/system_module/views/permissions_UI',
    '/system/router-manager' => 'modules/system_module/views/route_builder_UI',
    '/system/settings' => 'modules/system_module/views/general_UI',
    '/system/users' => 'modules/system_module/views/users_UI',
    '/system/users/add' => 'modules/system_module/views/users_add_UI',
    '/system/users/edit' => 'modules/system_module/views/users_edit_UI',
    '/system/users/list' => 'modules/system_module/views/users/list_UI',
    '/system/performance' => 'modules/system_module/views/performancecheckui',
  ),
  'controllers' => 
  array (
    // Maintenance routes
    array (
      'method' => 'get',
      'path' => '/system/maintenance/db_backup',
      'file' => 'maintenance/db_backup.php',
    ),
    array (
      'method' => 'get',
      'path' => '/system/maintenance/db_info_export',
      'file' => 'maintenance/db_info_export.php',
    ),
    array (
      'method' => 'post',
      'path' => '/system/maintenance/db_restore',
      'file' => 'maintenance/db_restore.php',
    ),
    array (
      'method' => 'post',
      'path' => '/system/maintenance/folder_export',
      'file' => 'maintenance/folder_export.php',
    ),
    array (
      'method' => 'get',
      'path' => '/system/maintenance/full_system_backup',
      'file' => 'maintenance/full_system_backup.php',
    ),
    array (
      'method' => 'get',
      'path' => '/system/maintenance/full_zip_backup',
      'file' => 'maintenance/full_zip_backup.php',
    ),
    array (
      'method' => 'post',
      'path' => '/system/maintenance/save_export_selection',
      'file' => 'maintenance/save_export_selection.php',
    ),
    array (
      'method' => 'post',
      'path' => '/system/maintenance/toggle_debug_mode',
      'file' => 'maintenance/toggle_debug_mode.php',
    ),

    // Router manager routes
    array (
      'method' => 'post',
      'path' => '/system/router-manager/save',
      'file' => 'route_builder_save.php',
    ),

    // User management routes
    array (
      'method' => 'post',
      'path' => '/system/users/save',
      'file' => 'users/save.php',
    ),
    array (
      'method' => 'post',
      'path' => '/system/users/update',
      'file' => 'users/update.php',
    ),
    array (
      'method' => 'get',
      'path' => '/system/users/delete',
      'file' => 'users/delete.php',
    ),
    array (
      'method' => 'post',
      'path' => '/system/users/password',
      'file' => 'users/change_password.php',
    ),
    array(
      'method' => 'get',
      'path' => '/system/maintenance/scan_permissions',
      'file' => 'maintenance/scan_permissions.php',
    ),
    
  ),
);
