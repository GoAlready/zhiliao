<?php
    // 定义常量
    define('ROOT', dirname(__FILE__) . '/../');
    function autoload($class){
        $path = str_replace('\\', '/', $class);
    
        require(ROOT . $path . '.php');
    }

    spl_autoload_register('autoload');

    $userController = new controllers\UserController;
    $userController->hello();

    // 加载视图
    function view($viewFileName,$data=[]){
        // 解压书组成变量
        extract($data);

        $path = str_replace('.', '/', $viewFileName) . '.html';

        require(ROOT . 'views/' . $path);
    }

?>