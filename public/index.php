<?php
    // 定义常量
    define('ROOT', dirname(__FILE__) . '/../');

    // 引入 composer 自动加载文件
    require(ROOT.'vendor/autoload.php');

    function autoload($class){
        $path = str_replace('\\', '/', $class);
       
        require(ROOT . $path . '.php');
    }

    spl_autoload_register('autoload');

    // 添加路由(判断在浏览器执行还是在命令行执行)
    if(php_sapi_name() == 'cli'){
        $controller = ucfirst($argv[1]).'Controller';
        $action = $argv[2];
    }
    else {

        if(isset($_SERVER['PATH_INFO']))
        {
            $pathInfo = $_SERVER['PATH_INFO'];
            // 根据 / 转成数组
            $pathInfo = explode('/',$pathInfo);
            // 得到控制名和方法名
            $controller = ucfirst($pathInfo[1]).'Controller';
            $action = $pathInfo[2];
        }
        else 
        {
            // 默认控制器和方法
            $controller = 'IndexController';
            $action = 'index';
        }

    }

    // 为控制器添加命名空间
    $fullController = 'controllers\\'.$controller;

    $C = new $fullController;
    $C ->$action();

    // 获取url上的所有参数,并且还能排除某些参数
    function getUrlParams($except = [])
    {
        // 循环删除已有变量
        foreach($except as $v)
        {
            unset($_GET[$v]);
        }
        // 拼出字符串，循环出地址栏所有变量
        $str = '' ;
        foreach($_GET as $k => $v)
        {
            $str .= "$k=$v&"; 
        }
        return $str;
    }

    // 加载视图
    function view($viewFileName,$data=[]){
        // 解压书组成变量
        extract($data);

        $path = str_replace('.', '/', $viewFileName) . '.html';

        require(ROOT . 'views/' . $path);
    }

?>