<?php
return array (
  'views' => 
  array (
  ),
  'controllers' => 
  array (
    array(
      'method' => 'get',
      'path' => '/hr/countries',
      'file' => 'main_hr/countries/index.php',
    ),
    array(
      'method' => 'get',
      'path' => '/hr/countries/add',
      'file' => 'main_hr/countries/add.php',
    ),
    array(
      'method' => 'get',
      'path' => '/hr/countries/edit',
      'file' => 'main_hr/countries/edit.php',
    ),
    array(
      'method' => 'get',
      'path' => '/hr/countries/delete',
      'file' => 'main_hr/countries/delete.php',
    ),
    array(
      'method' => 'post',
      'path' => '/hr/countries/store',
      'file' => 'main_hr/countries/store.php',
    ),
    array(
      'method' => 'post',
      'path' => '/hr/countries/update',
      'file' => 'main_hr/countries/update.php',
    ),
  ),
);
