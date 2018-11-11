<?php
/**
 * Created by PhpStorm.
 * User: baiyan
 * Date: 2018-11-6
 * Time: 14:32
 */

define('APP_PATH' , dirname(__DIR__) . '/');

define('APP_DEBUG',true);

include_once(APP_PATH . 'baiyanphp/byphp.php');

$config = require_once(APP_PATH . 'config/config.php');

(new byphp\byphp($config))->run();