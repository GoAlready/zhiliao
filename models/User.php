<?php
    namespace models;

    use PDO;

    class User extends Base
    {

        public function add($email,$password)
        {
            $stmt = self::$pdo->prepare("insert into users (email,password) values(?,?)");
            return $stmt->execute([
                        $email,
                        $password,
                    ]);
        }
    }
?>