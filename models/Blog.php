<?php
    namespace models;

    use PDO;

    class Blog extends Base
    {
        // 在修改删除时日志生成静态页
        public function makeHtml($id)
        {
            // 取出日志信息
            $blog = $this->find($id);
            // 打开缓冲区,并且加载视图到缓冲区
            ob_start();

            view('blogs.content',[
                'blog'=>$blog,
            ]);
            // 从缓冲区取出视图并写到静态页中
            $str = ob_get_clean();
            file_put_contents(ROOT.'public/contents/'.$id.'.html',$str);
        }
        // 删除静态页
        public function deleteHtml($id)
        {
            // @防止报错 有这个文件就删除 没有就不删除 不用报错
            @unlink(ROOT.'public/contents/'.$id.'.html');
        }
        // 修改日志
        public function find($id)
        {
            $stmt = self::$pdo->prepare('select * from blogs where id = ?');
            $stmt -> execute([
                $id
            ]);
            // 取出数据
            return $stmt->fetch();
        }

        public function update($title,$content,$is_show,$id)
        {
            $stmt = self::$pdo->prepare("update blogs set title = ?,content = ?,is_show = ? where id = ?");
            $ret = $stmt->execute([
                $title,
                $content,
                $is_show,
                $id,
            ]);
        }

        // 获取最新20条
        public function getNew()
        {
            $stmt = self::$pdo->query('SELECT * FROM blogs WHERE is_show=1 ORDER BY id DESC LIMIT 20');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function delete($id)
        {
            // 只能删除自己的日志
            $stmt = self::$pdo->prepare('delete from blogs where id=? and user_id=?');
            $stmt->execute([
                $id,
                $_SESSION['id'],
            ]);
        }
        public function add($title,$content,$is_show)
        {
            $stmt = self::$pdo->prepare("insert into blogs(title,content,is_show,user_id) values(?,?,?,?)");
            $ret = $stmt->execute([
                $title,
                $content,
                $is_show,
                $_SESSION['id'],
            ]);
            if(!$ret)
            {
                echo '失败';
                // 获取失败信息
                $error = $stmt->errorInfo();
                echo '<pre>';
                var_dump($error);
                exit;
            }
            // 返回新插入的记录的ID
            return self::$pdo->lastInsertId();
        }
        // 搜索日志
        public function search()
        {
            // 设置$where
            @$where = 'user_id='.$_SESSION['id'];
            // 放预处理对应的值
            $value = [];

            // 搜索功能
            if(isset($_GET['keyword']) && $_GET['keyword'])
            {
                $where .= " and (title like ? or content like ?) ";
                $value[] = '%'.$_GET['keyword'].'%';
                $value[] = '%'.$_GET['keyword'].'%';
            }
            if(isset($_GET['start_date']) && $_GET['start_date'])
            {
                $where .= " and created_at >= ? ";
                $value[] = $_GET['start_date'];
            }
            if(isset($_GET['end_date']) && $_GET['end_date'])
            {
                $where .= " and created_at <= ? ";
                $value[] = $_GET['end_date'];
            }
            if(isset($_GET['is_show']) && ($_GET['is_show'] == 1 || $_GET['is_show'] === '0'))
            {
                $where .= " and is_show = ?";
                $value[] = $_GET['is_show'];
            }

            // 排序
            $odby = 'created_at';
            $odway = 'desc';

            if(isset($_GET['odby']) && $_GET['odby'] == 'display')
            {
                $odby = 'display';
            }
            if(isset($_GET['odway']) && $_GET['odway'] == 'asc')
            {
                $odway = 'asc';
            } 

            // 分页
            $perpage = 15;  // 每页的条数

            // 当前页数
            $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;

            $offset = ($page-1) * $perpage;
            
            // 制作按钮,取出总记录数
            $stmt = self::$pdo->prepare("select count(*) from blogs where $where");
            $stmt->execute($value);
            $count = $stmt->fetch(PDO::FETCH_COLUMN);
            // 计算总页数
            $pageCount = ceil($count / $perpage);

            $btns = '';
            for($i=1;$i<=$pageCount;$i++)
            {
                // 先获取之前的参数
                $params = getUrlparams(['page']);
                $class = $page == $i ? 'active' :'';
                $btns .= "<a class='$class' href='?{$params}page=$i' > $i </a>";
            }

            // 预处理
            $stmt = self::$pdo->prepare("select * from blogs where $where order by $odby $odway limit $offset,$perpage");
            // 执行sql
            $stmt->execute($value);
            // 获取数据
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data'=>$data,
                'btns'=>$btns,
            ];
        }

        public function content2html(){
           
            $stmt = self::$pdo->query("select * from blogs");
            $blogs = $stmt -> fetchAll(PDO::FETCH_ASSOC);
                
            // 开启缓冲区
            ob_start();
        
            foreach($blogs as $v)
            {
                view('blogs.content',[
                    'blog' => $v,
                ]);
                
                // 取出缓存区的内容
                $str = ob_get_contents();
                // 生成静态页
                file_put_contents(ROOT.'public/contents/'.$v['id'].'.html',$str);
                // 清空缓冲区
                ob_clean();
            }
        }
        public function index2html()
        {
            // 取前20条数据
            $stmt = self::$pdo->query("select * from blogs where is_show = 1 order by id desc limit 20");
            $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 开启缓冲区
            ob_start();

            // 加载视图文件到缓冲区
            view('index.index',[
                'blogs'=> $blogs,
            ]);

            // 从缓冲区中取出页面
            $str = ob_get_contents();

            // 把页面的内容生成一个静态页
            file_put_contents(ROOT.'public/index.html',$str);
        }
        // 从数据库取出日志的浏览量

        public function getDisplay($id)
        {   
            // 接收日志id
            $id = (int)$_GET['id'];
            // 连接redis
            $redis = \libs\Redis::getInstance();

            // 判断redis中是否有这个日志的浏览量
            $key = "blog-{$id}";  //拼出日志的键
            if($redis->hexists('blog_displays',$key))
            {
                $newNum = $redis->hincrby('blog_displays',$key,1);
                return $newNum;
            }
            else
            {
                $stmt = self::$pdo->prepare('select display from blogs where id=?');
                $stmt->execute([$id]);
                $display =  $stmt->fetch(PDO::FETCH_COLUMN);
                $display ++;
                // 保存到redis
                $redis->hset('blog_displays',$key,$display);
                return $display;
            }
        }

        public function displayToDb()
        {
            // 1.先取出内存中的所有的浏览量
            // 连接 redis
            $redis = \libs\Redis::getInstance();

            $data = $redis->hgetall('blog_displays');
            // 更新回数据库
            foreach($data as $k => $v)
            {
                $id = str_replace('blog-','',$k);
                $sql = "update blogs set display={$v} where id = {$id}";
                self::$pdo->exec($sql);
            }
        }
    }
?>