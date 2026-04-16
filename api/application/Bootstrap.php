<?php
/**
 * Bootstrap — YAF 引导类
 * 所有 _init 前缀的方法会在 Application::bootstrap() 时依次执行
 */

use Yaf\Bootstrap_Abstract;
use Yaf\Dispatcher;
use Yaf\Registry;

class Bootstrap extends Bootstrap_Abstract
{
    /**
     * 初始化配置，注入到全局注册表
     */
    public function _initConfig(Dispatcher $dispatcher): void
    {
        $config = \Yaf\Application::app()->getConfig();
        Registry::set('config', $config);
    }

    /**
     * 初始化数据库连接
     */
    public function _initDatabase(Dispatcher $dispatcher): void
    {
        $config = Registry::get('config');
        $db = Db::getInstance(
            $config->db->dsn,
            $config->db->username,
            $config->db->password
        );
        Registry::set('db', $db);
    }

    /**
     * 关闭自动渲染视图（纯 JSON API，不需要模板引擎）
     */
    public function _initView(Dispatcher $dispatcher): void
    {
        $dispatcher->disableView();
    }

    /**
     * 统一处理 CORS（跨域）与 OPTIONS 预检
     */
    public function _initCors(Dispatcher $dispatcher): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
