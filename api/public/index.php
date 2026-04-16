<?php
/**
 * 入口文件
 * 所有 HTTP 请求均通过此文件进入 YAF 框架
 */

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__));
defined('CONF_PATH') || define('CONF_PATH', APP_PATH . '/conf/app.ini');

// 设置时区
date_default_timezone_set('Asia/Shanghai');

$app = new Yaf\Application(CONF_PATH);
$app->bootstrap()->run();
