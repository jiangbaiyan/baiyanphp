<?php
/**
 * Created by PhpStorm.
 * User: baiyanzzZ
 * Date: 2019/02/24
 * Time: 10:13
 */

namespace Family\Coroutine;

use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Class Context
 * @package Family\Coroutine
 * @desc context pool，请求之间隔离，请求之内任何地方可以存取
 */
class Context {

     private $request;

     private $response;

     private $map = [];

     public function __construct(Request $request, Response $response)
     {
         $this->request = $request;
         $this->response = $response;
     }

     public function getRequest() {
         return $this->request;
     }

     public function getResponse() {
         return $this->response;
     }

     public function set($key, $val) {
         $this->map[$key] = $val;
     }

     public function get($key) {
         if (isset($this->map[$key])) {
             return $this->map[$key];
         }
         return null;
     }


}