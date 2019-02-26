<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/23
 * Time: 18:08
 */

namespace Family;

use Family\Core\Config;
use Family\Core\Route;
use Family\Coroutine\Context;
use Family\Coroutine\Coroutine;
use Swoole\Http\Server;

class Family {

    public static $rootPath;

    public static $frameworkPath;

    public static $applicationPath;

    /**
     * 运行框架
     */
    final public static function run() {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        self::$rootPath =  dirname(dirname(__DIR__));
        self::$frameworkPath = self::$rootPath . DS . 'framework';
        self::$applicationPath = self::$rootPath . DS . 'application';
        spl_autoload_register(array(__CLASS__, 'autoLoader'));
        Config::load();
        $http = new Server('0.0.0.0', 9999);
        $http->set([
            'worker_num' => 1
        ]);
        $http->on('request', function ($request, $response){
            try {
                //初始化根协程ID
                $coId = Coroutine::setBaseId();
                //初始化上下文
                $context = new Context($request, $response);
                //存放容器pool
                Pool\Context::set($context);
                //协程退出，自动清空
                defer(function () use ($coId) {
                    //清空当前pool的上下文，释放资源
                    Pool\Context::clear($coId);
                });
                $result = Route::dispatch($request->server['path_info']);
                $response->end($result);
            } catch (\Exception $e) {
                print_r($e);
                $response->end($e->getMessage());
            } catch (\Error $e) {
                print_r($e);
                $response->status(500);
                $response->end($e->getMessage());
            } catch (\Throwable $e) {
                print_r($e->getTrace());
                $response->status(500);
            }

        });
        $http->start();
    }

    /**
     * 自动加载器
     * @param $class
     */
    final public static function autoLoader($class) {
        $rootPath = dirname(dirname(__DIR__));
        //把类转为目录，eg \a\b\c => /a/b/c.php
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        $findPath = [
            $rootPath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR,
            $rootPath . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
        ];
        foreach ($findPath as $path) {
            $realPath = $path . $classPath;
            if (is_file($realPath)) {
                require $realPath;
                return;
            }
        }
    }
}