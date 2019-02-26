<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/26
 * Time: 09:42
 */

namespace Family\Pool;

use Family\Db\Mysql as DB;

use swoole_channel;

class Mysql {

    private static $instance;

    private $pool;//使用swoole_channel实现连接池

    private $config;

    /**
     * 单例
     * @param null $config
     * @return Mysql
     * @throws \Exception
     */
    public static function getInstance($config = null) {
        if (empty(self::$instance)) {
            if (empty($config)) {
                throw new \Exception('mysql config empty');
            }
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    /**
     * Mysql constructor.
     * @param $config
     * @throws \Exception
     * @desc 初始化，自动创建实例,需要放在workerstart中执行
     */
    public function __construct($config){
        if (empty($this->pool)) {
            $this->config = $config;
            $this->pool = new swoole_channel($config['pool_size']);
            for ($i = 0; $i<$config['pool_size']; $i++) {
                $mysql = new DB();
                $res = $mysql->connect($config);
                if ($res === false) {
                    throw new \Exception('failed to connect mysql server');
                } else {
                    $this->put($mysql);
                }
            }
        }
    }

    /**
     * @param $mysql
     * @desc 放入一个mysql连接入池
     */
    public function put($mysql) {
        $this->pool->push($mysql);
    }

    /**
     * @return mixed
     * @throws \Exception
     * @desc 获取一个连接，当超时，返回一个异常
     */
    public function get()
    {
        $mysql = $this->pool->pop($this->config['pool_get_timeout']);
        var_dump($mysql);
        if (false === $mysql) {
            throw new \Exception("get mysql timeout, all mysql connection is used");
        }
        return $mysql;
    }

    /**
     * @return mixed
     * @desc 获取当时连接池可用对象
     */
    public function getLength()
    {
        return $this->pool->length();
    }

}