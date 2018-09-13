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
                $_SESSION['money'] = $user['money'];
                return true;
            }
            else
            {
                return false;
            }
        }

        // 为用户增加余额
        public function addMoney($money,$userId)
        {
            $stmt = self::$pdo->prepare("update users set money=money+? where id = ?");
            return $stmt->execute([
                $money,
                $userId
            ]);
            // 更新session中的余额
            $_SESSION['money'] += $money;
        }

        // 获取余额
        public function getMoney()
        {
            $id = $_SESSION['id'];
            // 查询数据库
            $stmt = self::$pdo->prepare('select money from users where id = ?');
            $stmt->execute([$id]);
            $money = $stmt->fetch(PDO::FETCH_COLUMN);
            // 更新到SESSION中
            $_SESSION['money'] = $money;
            return $money;
        }
    }
?>