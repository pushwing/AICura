<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/


$hook['post_controller_constructor'][] = array(
//$hook['post_controller'][] = array(
    'function' => 'checkKey',
    'filename' => 'preProcess.php',
    'filepath' => 'hooks'
);

$hook['pre_system'][] = [
    'function' => 'setExceptionHandler',
    'filename' => 'exceptionHook.php',
    'filepath' => 'hooks'
];

/*
$hook['post_controller_constructor'][] = array(
    'class'    => 'HooksAPi',
    'function' => 'hooking',
    'filename' => 'hooksApi.php',
    'filepath' => 'hooks'
);
*/