<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/23
 * Time: 20:34
 */

namespace controller;

use Family\Pool\Context;

class Index {

    public function index() {
        $context = Context::getContext();
        $request = $context->getRequest();
        return 'i am family by route!' . json_encode($request->get);
    }

    public function tong() {
        return 'i am tong ge';
    }
}