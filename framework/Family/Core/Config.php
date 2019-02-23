<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/23
 * Time: 19:53
 */

namespace Family\Core;

class Config {

    public static $configMap;

    /**
     * 加载配置文件
     */
    public static function load() {
        $configPath = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config';
        self::$configMap = require $configPath . DIRECTORY_SEPARATOR . 'default.php';
    }

    /**
     * 获取配置
     * @param $key
     * @return |null
     */
    public static function get($key) {
        if (isset(self::$configMap[$key])) {
            return self::$configMap[$key];
        }
        return null;
    }
}