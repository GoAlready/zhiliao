<?php
    namespace controllers;

    use models\User;
    use models\Order;
    
    class UserController 
    {
        public function setActiveUsers()
        {
            $user = new User;
            $user->computeActiveUsers(); 
        }
        public function uploadbig()
        {
            // 接收提交的数据
            $count = $_POST['count'];   //总的数量
            $i = $_POST['i'];   //当前是第几块
            $size = $_POST['size']; //每块的大小
            $name = 'big_img_'.$_POST['img_name'];  // 所有分块的名字
            $img = $_FILES['img'];  //图片
            // 保存每个分块,防止两人同时上传文件名冲突
            $dir = ROOT.'tmp/'.$name;
            if(!is_dir($dir)){
                mkdir($dir,true,0777);
            }
            move_uploaded_file($img['tmp_name'],ROOT.'tmp/'.$name."/".$i);
            // 难点：因为每个分块到达服务器的顺序不固定，所以我们不能根据顺序来判断是否都上传成功。
            // 实现思路：每上传一个就+1，直到上传的数量等于总的数量
            $redis = \libs\Redis::getInstance();
            // 每上传一张就加1
            $uploadedCount = $redis->incr($name);
            // 如果是最后一个分支就合并
            if($uploadedCount == $count)
            {
                // 以追回的方式创建并打开最终的大文件
                $fp = fopen(ROOT.'public/uploads/big/'.$name.'.png','a');
                // 循环所有的分块
                for($i=0;$i<$count;$i++)
                {
                    // 读取第i号文件并写到大文件中
                    fwrite($fp,file_get_contents(ROOT.'tmp/'.$name."/".$i));
                    // 删除第i号临时文件
                    unlink(ROOT.'tmp/'.$name."/".$i);
                }
                // 关闭文件
                fclose($fp);
                // 从redis删除这个文件对应的编号这个变量
                $redis->del($name);
            }
        }

        public function uploadall()
        {
            // 保存图片的路径
            $uploadDir = ROOT.'public/uploads/';
            $date = date('Ymd');
            // 如果没有这个目录就创建目录
            if(!is_dir($uploadDir.$date))
            {
                // 创建目录（第二个参数：有写的权限（只对 Linux 系统)）
                mkdir($uploadDir.$date, 0777);
            }

            // 循环五张图片的name
            foreach($_FILES['images']['name'] as $k =>$v)
            {
                $name = md5( time() . rand(1,9999) );
                $ext = strrchr($v,'.');
                $name = $name . $ext;
                // 根据下标找到对应文件的临时路径
                move_uploaded_file($_FILES['images']['tmp_name'][$k],$uploadDir.$date.'/'.$name);
                // echo $uploadDir.$date.'/'.$name.'<hr>';
            }
        }

        public function album()
        {
            view('users.album');
        }

        public function setavatar()
        {
            $upload = \libs\Uploader::make();
            $path = $upload->upload('avatar','avatar');

            // 保存到user表中
            $model = new \models\User;
            $model->setAvatar('/uploads/'.$path);

            // 删除原头像
            @unlink(ROOT .'public' .  $_SESSION['avatar']);

            // 设置新头像
            $_SESSION['avatar'] = '/uploads/'.$path;

            message('设置成功',2,'/blog/index');
        }

        public function avatar()
        {
            view('users.avatar');
        }

        public function money()
        {
            $user = new User;
            echo $user->getMoney();

        }

        public function docharge()
        {
            // 生成订单
            $money = $_POST['money'];
            $model = new Order;
            $model->create($money);
            message('充值订单已生成,请立即支付!',2,'/user/orders');

        }

        public function orders()
        {
            $order = new Order;
            // 调用搜索方法
            $data = $order->search();


            // 加载视图
            view('users.order',$data);
        }

        public function charge()
        {
            view('users.charge');
        }

        public function doLogin()
        {
            $email = $_POST['email'];
            $password = md5($_POST['password']);

            $user = new \models\User;
            if($user->login($email,$password))
            {
                message('登陆成功!',1,'/blog/index');
            }
            else
            {
                message('账号或者密码错误',1,'/user/login');
            }
        }

        public function logout()
        {
            // 清空session
            $_SESSION = [];

            // 跳转
            message('退出成功',2,'/');
        }

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

        public function orderStatus()
        {
            $sn = $_GET['sn'];

            // 获取的次数
            $try = 10;
            $model = new Order;

            do{
                // 查询订单信息
                $info = $model->findBySn($sn);
                // 如果订单未支付就等待一秒,并减少尝试的次数,如果已经支付就退出循环
                if($info['status'] == 0)
                {
                    sleep(1);
                    $try--;
                }
                else
                    break;
            }while($try>0);  // 如果尝试的次数到达指定的次数就退出循环

            echo $info['status'];
        }
    }
?>