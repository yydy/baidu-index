<?php
$sys_config = [
    'static_file_path' => '',
    'upload_path' => '',
];
$params = require_once app_path() . '/sys_params.php';
return array_merge($sys_config, $params);