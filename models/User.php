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

        public function login($email,$password)
        {
            $stmt = self::$pdo->prepare('select * from users where email=? and password=?');
            $stmt->execute([
                $email,
                $password
            ]);

            $user = $stmt->fetch();
            if($user)
            {
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                return true;
            }
            else
            {
                return false;
            }
        }
    }
?>