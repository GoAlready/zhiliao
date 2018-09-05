<?php

    namespace libs;

    class Redis
    {
        private static $redis = null;

        private function __clone(){}
        
        private function __construct(){}

        public static function getInstance()
        {
            // 从配置文件中读取账号
            $config = config('redis');

            if(self::$redis === null)
            {
                // 连接 redis
                self::$redis = new \Predis\Client($config);
            }  
            return self::$redis;          
        }

    }
?>