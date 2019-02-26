<?php
/**
 * 协程操作类,避免使用全局变量(跨请求/只读),用context取而代之
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/24
 * Time: 10:34
 */

namespace Family\Coroutine;

use Swoole\Coroutine as SwCo;

class Coroutine {

    public static $idMaps = [];

    /**
     * 获取当前协程id
     * @return int
     */
    public static function getId() {
        return SwCo::getuid();
    }

    /**
     * @desc 只有根协程才能设置,为onRequest回调后的第一个协程，把根协程Id设置为自己
     */
    public static function setBaseId() {
        $id = self::getId();
        self::$idMaps[$id] = $id;
        return $id;
    }

    /**
     * 获取根协程id
     * @param null $id
     * @param int $cur
     * @return int|mixed|null
     */
    public static function getPid($id = null, $cur = 1) {
        if ($id === null) {
            $id = self::getId();
        }
        if (isset(self::$idMaps[$id])) {
            return self::$idMaps[$id];
        }
        return $cur ? $id : -1;
    }

    /**
     * 判断是否是根协程
     * @return bool
     */
    public static function checkBaseCo() {
        $id = SwCo::getuid();//当前协程id
        if (empty(self::$idMaps[$id])) {
            return false;
        }
        if ($id !== self::$idMaps[$id]) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param $cb
     * @param null $deferCb
     * @desc 从协程中创建协程，可保持根协程id的传递
     */
    public static function create($cb, $deferCb = null) {
        $nid = self::getId();
        return go(function () use ($cb, $deferCb, $nid) {//协程嵌套
            $id = SwCo::getuid();
            defer(function () use ($deferCb, $id) {
                self::call();
                self::clear($id);
            });
            $pid = self::getPid($nid);
            if ($pid == -1) {
                $pid = $nid;
            }
            self::$idMaps[$id] = $pid;//子协程知道了根协程的id
            self::call($cb);
        });
    }

    /**
     * 执行回调函数
     * @param $cb
     * @param $args
     * @return null
     */
    public static function call($cb, $args) {
        if (empty($cb)) {
            return null;
        }
        $ret = null;
        if (is_object($cb) || is_string($cb) && function_exists($cb)) {
            $ret = $cb(...$args);
        } else if (is_array($cb)) {
            list($obj, $mhd) = $cb;
            $ret = \is_object($obj) ? $obj->$mhd(...$args) : $obj::$mhd(...$args);
        }
        return $ret;
    }

    /**
     * @param null $id
     * @desc 协程退出，清除关系树
     */
    public function clear($id = null) {
        if (null === $id) {
            $id = self::getId();
        }
        unset(self::$idMaps[$id]);
    }

}