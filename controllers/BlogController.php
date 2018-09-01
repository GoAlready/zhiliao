<?php
    namespace controllers;

    use PDO;

    class BlogController {
        public function index(){
            
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog','root','123');
            $pdo->exec("set names utf8");

            // 设置$where
            $where = 1;
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
            $stmt = $pdo->prepare("select count(*) from blogs where $where");
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

            $stmt = $pdo->prepare("select * from blogs where $where order by $odby $odway limit $offset,$perpage");
            $stmt->execute($value);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 加载视图
            view('blogs.index',[
                'data' => $data,
                'btns' => $btns,
            ]);

        }
    }
?>