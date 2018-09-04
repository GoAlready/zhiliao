<?php
    namespace models;

    use PDO;

    class User {

        public $pdo;

        public function __construct()
        {
             // 取日志数据
             $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=blog','root','123456');
             $this->pdo->exec('set names utf8');
        }

       public function getName()
        {
            return 'tom';
        }

        public function add($email,$password)
        {
            $stmt = $this->pdo->prepare("insert into users (email,password) values(?,?)");
            return $stmt->execute([
                        $email,
                        $password,
                    ]);
        }
    }
?>