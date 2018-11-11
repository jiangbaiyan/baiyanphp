<?php
/**
 * 框架核心文件
 * Created by PhpStorm.
 * User: baiyan
 * Date: 2018-11-10
 * Time: 11:24
 */

namespace byphp;

defined('CORE_PATH') or define('CORE_PATH',__DIR__);
define('CONTROLLER_NAMESPACE','app\\controller\\');

class byphp{

    protected $config = [];

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * 入口方法
     */
    public function run(){
        spl_autoload_register([$this,'loadClass']);
        $this->setReporting();
        $this->setConfig();
        $this->route();
    }

    /*
     * 路由
     */
    public function route(){
        $url = $_SERVER['REQUEST_URI'];
        if (strpos($url,'?')){
            $arr = explode('?',$url);
            $url = $arr[0];
        }
        $url = trim($url,'/');
        $arr = explode('/',$url);
        $arr = array_map('strtolower',$arr);
        $action = strtolower(end($arr));
        $len = strlen($url) - strlen($action);
        $controllerPath = substr($url,0,--$len);
        $filePath = APP_PATH . 'app/controller/' . $controllerPath . '.php';
        if (!file_exists($filePath)){
            die('文件不存在');
        }
        include_once $filePath;
        $controllerPath = str_replace('/','\\',$controllerPath);
        $controller = CONTROLLER_NAMESPACE . $controllerPath;
        if (!class_exists($controller,false)){
            die('控制器类不存在');
        }
        $controller = new $controller;
        if (!method_exists($controller,$action)){
            die('方法不存在');
        }
        $controller->$action();
    }

    /**
     * 自动加载处理类
     * @param $class
     */
    public function loadClass($class){
        $classMap = $this->autoloadMap();
        if (isset($classMap[$class])){
            $file = $classMap[$class];
         } else if (strpos($class,'\\') !== false){
            $file = APP_PATH . str_replace('\\','/',$class) . '.php';
            if (!is_file($file)){
                return;
            }
        } else{
            return;
        }
        include_once $file;
    }

    /**
     * 自动加载基础类映射
     * @return array
     */
    private function autoloadMap(){
        return [
            'byphp\base\Controller' => CORE_PATH . '/base/Controller.php',
            'byphp\base\Model' => CORE_PATH . '/base/Model.php',
            'byphp\base\View' => CORE_PATH . '/base/View.php',
            'byphp\db\Db' => CORE_PATH . '/db/Db.php',
            'byphp\db\Sql' => CORE_PATH . '/db/Sql.php',
        ];
    }

    private function setConfig(){
        if ($this->config['db']){
            define('DB_HOST',$this->config['db']['host']);
            define('DB_NAME',$this->config['db']['dbname']);
            define('DB_USER',$this->config['db']['username']);
            define('DB_PASS',$this->config['db']['password']);
        }
    }

    /**
     * 开发环境检测
     */
    private function setReporting(){
        if (APP_DEBUG){
            ini_set('display_errors','On');
            error_reporting(E_ALL);
        } else{
            ini_set('display_errors','false');
            ini_set('log_errors','On');
            error_reporting(E_ALL);
        }
    }
}