<?php
    namespace controllers;

    use models\Blog;

    class BlogController {
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

            echo $blog->getDisplay($id);
        }

        public function displayToDb()
        {
            $blog = new Blog;
            $blog->displayToDb();
        }
    }
?>