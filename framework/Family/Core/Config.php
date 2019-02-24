<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/23
 * Time: 19:53
 */

namespace Family\Core;

use Family\Family;

class Config {

    public static $configMap;

    /**
     * 加载配置文件
     */
    public static function load() {
        $configPath = Family::$applicationPath . DS . 'config';
        self::$configMap = require $configPath . DS . 'default.php';
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