<?php
    // 定义常量
    define('ROOT', dirname(__FILE__) . '/../');
    function autoload($class){
        $path = str_replace('\\', '/', $class);
       
        require(ROOT . $path . '.php');
    }

    spl_autoload_register('autoload');

    // 添加路由
    if(isset($_SERVER['PATH_INFO']))
    {
        $pathInfo = $_SERVER['PATH_INFO'];
        // 根据 / 转成数组
        $pathInfo = explode('/',$pathInfo);
        // 得到控制名和方法名
        $controller = ucfirst($pathInfo[1]).'Controller';
        $action = $pathInfo[2];
    }
    else {
        // 默认控制器和方法
        $controller = 'IndexController';
        $action = 'index';
    }
    // 为控制器添加命名空间
    $fullController = 'controllers\\'.$controller;

    $C = new $fullController;
    $C ->$action();

    // 加载视图
    function view($viewFileName,$data=[]){
        // 解压书组成变量
        extract($data);

        $path = str_replace('.', '/', $viewFileName) . '.html';

        require(ROOT . 'views/' . $path);
    }

?>