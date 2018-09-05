<?php

    // 所有其他的父模型
    namespace models;

    use PDO;

    class Base
    {
        public static $pdo = null;

        public function __construct()
        {
            if(self::$pdo === null)
            {
                // 从配置文件中读取账号
                $config = config('db');
                 // 取日志数据
                self::$pdo = new \PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'],$config['user'],$config['pass']);
                self::$pdo->exec('set names '.$config['charset']);
            }           
        }
    }
?>