<?php
    namespace controllers;

    use models\User;
    
    class UserController {
        public function login()
        {
            view('users.login');
        }

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

            // 生成激活码(随机字符串)
            $code = md5(rand(1,99999));

            // 保存到redis中
            $redis = \libs\Redis::getInstance();
            // 序列化 (数组转成json字符串)
            $value = json_encode([
                'email' => $email,
                'password' => $password,
            ]);
            // 键名
            $key = "temp_user:{$code}";
            $redis->setex($key,300,$value);
            // 把激活码发到用户的邮箱中
            // 从邮箱地址中取出姓名
            $name = explode('@',$email);
            // 构造收件人地址
            $from = [$email,$name[0]];

            $message = [
                'title' => '智聊系统-账号激活',
                'content' => "点击以下链接进行激活：<br> 点击激活：
                    <a href='http://localhost:9999/user/active_user?code={$code}'>
                    http://localhost:9999/user/activeEmail?code={$code}</a><p>
                    如果按钮不能点击，请复制上面链接地址，在浏览器中访问来激活账号！</p>",
                'from' => $from,
            ];

            // 把消息转成json字符串(序列化)
            $message = json_encode($message);
            // 放到队列中
            $redis = \libs\Redis::getInstance();
            $redis->lpush('email',$message);
            
            echo 'ok';
        }
        public function activeEmail()
        {
            // 接收激活码
            $code = $_GET['code'];

            // 到redis取出账号
            $redis = \libs\Redis::getInstance();
            // 拼出名字
            $key = 'temp_user:'.$code;
            // 取出数据
            $data = $redis->get($key);
            // 判断是否存在激活码
            if($data)
            {
                // 从redis删除激活码
                $redis->del($key);
                // 反序列化(转回数组)
                $data = json_decode($data,true);
                // 插入到数据库中
                $user = new \models\User;
                $user->add($data['email'],$data['password']);
                // 跳转到登录页面
                header('Location:/user/login');
            }
            else
            {
                die('激活码无效');
            }

        }
    }
?>