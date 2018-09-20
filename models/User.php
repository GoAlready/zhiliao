<?php
    namespace models;

    use PDO;

    class User extends Base
    {
        public function getActiveUsers()
        {
            $redis = \libs\Redis::getInstance();
            $data = $redis->get('active_users');
            // 转回数组(第二个参数 true:数组  false:对象)
            return json_decode($data,true);
        }
        // 计算活跃用户
        public function computeActiveUsers()
        {
            // 取日志的分值
            $stmt = self::$pdo->query('select user_id,count(*)*5 fz from blogs where created_at >= date_sub(curdate(), interval 1 week) group by user_id');
            $data1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*
                $data1 = [
                    [
                        'user_id' => 3,
                        'fz' => 100,
                    ],
                    [
                        'user_id' => 2,
                        'fz' => 43,
                    ],
                ];
            */
            // 取评论的分值
            $stmt = self::$pdo->query('select user_id,count(*)*3 fz from comments where created_at >= date_sub(curdate(), interval 1 week) group by user_id');
            $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 取点赞的分值
            $stmt = self::$pdo->query('select user_id,count(*) fz from blog_agrees where created_at >= date_sub(curdate(), interval 1 week) group by user_id');
            $data3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 定义一个空数组合并数组
            $arr = [];

            // 循环第一个数组到空数组中
            foreach($data1 as $v)
            {
                $arr[$v['user_id']] = $v['fz'];
            }
            // 第二个数组
            foreach($data2 as $v)
            {
                if(isset($arr[$v['user_id']]) )

                    $arr[$v['user_id']] += $v['fz'];
                
                else

                    $arr[$v['user_id']] = $v['fz'];
            }
            // 第三个数组
            foreach($data3 as $v)
            {
                if(isset($arr[$v['user_id']]) )

                    $arr[$v['user_id']] += $v['fz'];
                
                else

                    $arr[$v['user_id']] = $v['fz'];
            }


            // 倒序排序
            arsort($arr);
            // 取出前20并保存(第四个参数保留键)
            $data = array_splice($arr,0,20,true);

            // 取出所有的键(用户id)
            $userIds = array_keys($data);
            // 转成字符串
            $userIds = implode(',',$userIds);

            // 取出用户头像和email
            $sql = "SELECT id,email,avatar FROM users WHERE id IN($userIds)";

            $stmt = self::$pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 把结果保存到redis中
            $redis = \libs\Redis::getInstance();
            $redis->set('active_users',json_encode($data));

        }

        public function setAvatar($path)
        {
            $stmt = self::$pdo->prepare('update users set avatar=? where id=?');
            $stmt->execute([
                $path,
                $_SESSION['id']
            ]);
        }
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
                $_SESSION['avatar'] = $user['avatar'];

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

        // 获取所有账号
        public function getAll()
        {
            $stmt = self::$pdo->query('select * from users');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
?>