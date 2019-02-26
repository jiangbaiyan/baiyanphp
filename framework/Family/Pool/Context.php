<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/24
 * Time: 10:23
 */

namespace Family\Pool;

use Family\Coroutine\Coroutine;

/**
 * Class Context
 * @package Family\Pool
 * @desc context pool，请求之间隔离，请求之内任何地方可以存取
 */
class Context {

    public static $pool = [];

    /**
     * 可以任意协程获取到context
     * @return mixed|null
     */
    public static function getContext() {
        $id = Coroutine::getPid();
        if (isset(self::$pool[$id])) {
            return self::$pool[$id];
        }
        return null;
    }

    public static function clear() {
        $id = Coroutine::getuid();
        if (isset(self::$pool[$id])) {
            unset(self::$pool[$id]);
        }
    }

    public static function set($context) {
        $id = Coroutine::getuid();
        self::$pool[$id] = $context;
    }


}