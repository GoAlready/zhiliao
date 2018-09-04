<?php
    namespace controllers;

    use models\User;
    
    class UserController {
        public function regist()
        {
            // 显示试图
            view('users.add');
        }
        public function hello()
        {
            // 取模型里的数据
            $user = new User;
            $name = $user->getName();

            echo 11111;
            
            // 加载视图
            view('users.hello',[
                'name' => $name
                ]);

        }
        public function store()
        {
            // 接收表单
            $email = $_POST['email'];
            $password = md5($_POST['password']);

            // 插入到数据库
            $user = new User;
            $ret = $user->add($email,$password);
            if(!$ret)
            {
                die('注册失败');
            }

            // 从邮箱地址中取出姓名
            $name = explode('@',$email);
            // 构造收件人地址
            $from = [$email,$name[0]];

            $message = [
                'title' => '欢迎加入全栈',
                'content' => "点击以下链接进行激活:<br><a href=''>点击激活</a>.",
                'from' => $from,
            ];

            // 放到队列中
            $redis = new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => '127.0.0.1',
                'port'   => 6379,
            ]);
            // 把消息转成json字符串(序列化)
            $message = json_encode($message);

            $redis->lpush('email',$message);
            
            echo 'ok';
        }
    }
?>