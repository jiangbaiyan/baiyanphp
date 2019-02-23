<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/23
 * Time: 20:29
 */

namespace Family\Core;

class Route {

    /**
     * 路由
     * @param $path
     * @return mixed
     */
    public static function dispatch($path) {
        if (empty($path) || '/' == $path) {
            $controller = 'index';
            $method = 'index';
        } else {
            $maps = explode('/', $path);
            $controller = $maps[1];
            $method = $maps[2];
        }
        $controllerClass = 'controller' . '\\' . $controller;
        $class = new $controllerClass();
        return $class->$method();
    }

}