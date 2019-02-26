<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/26
 * Time: 09:13
 */

namespace Family\Db;

use Swoole\Coroutine\MySQL as SwMySQL;

class Mysql {

    private $master;

    private $slave;

    private $config;

    /**
     * 连接数据库
     * @param $config
     * @throws \Exception
     */
    public function connect($config) {
        //创建主数据库连接
        $master = new SwMySQL();
        $res = $master->connect($config['master']);
        if ($res === false) {
            throw new \Exception($master->connect_error, $master->errno);
        } else {
            $this->master = $master;
        }

        if (!empty($config['slave'])) {
            foreach ($config['slave'] as $conf) {
                $slave = new SwMySQL();
                $res = $slave->connect($conf);
                if ($res === false) {
                    throw new \Exception($slave->connect_error, $slave->errno);
                } else {
                    $this->slave[] = $slave;
                }
            }
        }

        $this->config = $config;
        return $res;
    }

    /**
     * 重连
     * @param $type
     * @param $index
     * @return SwMySQL
     * @throws \Exception
     */
    public function reconnect($type, $index)
    {
        //通过type判断是主还是从
        if ('master' == $type) {
            //创建主数据连接
            $master = new SwMySql();
            $res = $master->connect($this->config['master']);
            if ($res === false) {
                //连接失败，抛弃常
                throw new \Exception($master->connect_error, $master->errno);
            } else {
                //更新主库连接
                $this->master = $master;
            }
            return $this->master;
        }

        if (!empty($this->config['slave'])) {
            //创建从数据连接
            $slave = new SwMySql();
            $res = $slave->connect($this->config['slave'][$index]);
            if ($res === false) {
                //连接失败，抛弃常
                throw new \Exception($slave->connect_error, $slave->errno);
            } else {
                //更新对应的重库连接
                $this->slave[$index] = $slave;
            }
            return $slave;
        }
    }

    /**
     * 代理swoole的mysql操作类,在上层完成数据组装
     * @param $name
     * @param $arguments
     * @return array
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $sql = $arguments[0];
        $res = $this->chooseDb($sql);
        $db = $res['db'];
        $result = $db->$name($sql);
        //记录日志
        if (false === $result) {
            //记录日志
            if (!$db->connected) {
                $db = $this->reconnect($res['type'], $res['index']);
                //记录日志
                $result = $db->$name($sql);
                return $this->parseResult($result, $db);
            }
            if (!empty($db->errno)) {  //有错误码，则抛出弃常
                throw new \Exception($db->error, $db->errno);
            }
        }
        return $this->parseResult($result, $db);
    }

    /**
     * @param $result
     * @param $db
     * @return array
     * @desc 格式化返回结果：查询：返回结果集，插入：返回新增id, 更新删除等操作：返回影响行数
     */
    public function parseResult($result, $db) {
        if ($result === true) {
            return [
                'affected_rows' => $db->affected_rows,
                'insert_id' => $db->insert_id,
            ];
        }
        return $result;
    }

    /**
     * 根据sql选择主还是从库
     * @desc 判断有select 则选择从库， insert, update, delete等选择主库
     * @param $sql
     * @return array
     */
    protected function chooseDb($sql) {
        if (!empty($this->slave)) {
            if ('select' == strtolower(substr($sql, 0, 6))) {
                if (1 == count($this->slave)) {
                    $index = 0;
                } else {
                    $index = array_rand($this->slave);
                }
                return [
                    'type' => 'slave',
                    'index' => $index,
                    'db' => $this->slave[$index]
                ];
            }
        }
        return [
            'type' => 'master',
            'index' => 0,
            'db' => $this->master
        ];
    }
}