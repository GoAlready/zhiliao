<?php
    namespace controllers;

    use models\Blog;

    class BlogController 
    {

        public function content()
        {
            // 接收id,并取出日志信息
            $id = $_GET['id'];
            $model = new Blog;
            $blog = $model->find($id);

            // 判断这个日志是不是我的日志
            if($_SESSION['id'] != $blog['user_id'])
                die('无权访问!');
            // 加载视图
            view('blogs.content',[
                'blog'=>$blog,
            ]);
        }

        public function edit()
        {
            $id = $_GET['id'];
            // 根据id取出日志信息
            $blog = new Blog;
            $data = $blog->find($id);

            view('blogs.edit',[
                'data' => $data,
            ]);
        }

        public function update()
        {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $is_show = $_POST['is_show'];
            $id = $_POST['id'];

            $blog = new Blog;
            $blog -> update($title,$content,$is_show,$id);

            // 如果日志是公开的就生成静态页
            if($is_show == 1)
            {
                $blog->makeHtml($id);
            }
            else
            {
                // 如果改为私有,就要将原来的静态页删除
                $blog->deleteHtml($id);
            }

            message('修改成功!',2,'/blog/index');

        }
        
        public function delete()
        {
            $id = $_POST['id'];

            $blog = new Blog;
            $blog->delete($id);

            // 静态页删除掉
            $blog->deleteHtml($id);

            message('删除成功',2,'/blog/index');
        }
        public function create()
        {
            view('blogs.create');
        }

        public function store()
        {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $is_show = $_POST['is_show'];

            $blog = new Blog;
            $id = $blog->add($title,$content,$is_show);

            // 如果日志是公开的就生成静态页
            if($is_show == 1)
            {
                $blog->makeHtml($id);
            }


            // 跳转
            message('发表成功',2,'/blog/index');
        }
        // 日志列表
        public function index(){
            
            $blog = new Blog;
            // 调用搜索方法
            $data = $blog->search();

            // 加载视图
            view('blogs.index',$data);

        }
        // 为所有日志生成详情页
        public function content_to_html()
        {
            $blog = new Blog;
            $blog->content2html();
        }

        public function index2html(){
            $blog = new Blog;
            $blog->index2html();
        }

        public function display()
        {
            // 接收日志id
            $id = (int)$_GET['id'];
            $blog = new Blog;

            $display =  $blog->getDisplay($id);

            // 返回多个数据必须用JSON
            echo json_encode([
                'display' => $display,
                'email' => isset($_SESSION['email']) ? $_SESSION['email'] : ''
            ]);
        }

        public function displayToDb()
        {
            $blog = new Blog;
            $blog->displayToDb();
        }
    }
?>