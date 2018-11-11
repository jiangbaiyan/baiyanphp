<?php
/**
 * Created by PhpStorm.
 * User: 获取个人信息
 * Date: 2018-11-11
 * Time: 10:20
 */

namespace app\controller\profile;

class profile{

    public function get(){
        echo $_GET['id'];
    }

}