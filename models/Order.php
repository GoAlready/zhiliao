<?php
    namespace models;

    use PDO;

    class Order extends Base
    {
        // 下订单
        public function create($money)
        {
            $flake = new \libs\Snowflake(1023);
            $stmt = self::$pdo->prepare('INSERT INTO orders(user_id,money,sn) VALUES(?,?,?)');
            $stmt -> execute([
                $_SESSION['id'],
                $money,
                $flake->nextId()
            ]);
        }

        // 搜索订单
        public function search()
        {
            // 设置$where
            @$where = 'user_id='.$_SESSION['id'];
            // 放预处理对应的值
            $value = [];

            // 搜索功能


            // 排序
            $odby = 'created_at';
            $odway = 'desc';

            // 分页
            $perpage = 15;  // 每页的条数

            // 当前页数
            $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;

            $offset = ($page-1) * $perpage;
            
            // 制作按钮,取出总记录数
            $stmt = self::$pdo->prepare("select count(*) from orders where $where");
            $stmt->execute();
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
            $stmt = self::$pdo->prepare("select * from orders where $where order by $odby $odway limit $offset,$perpage");
            // 执行sql
            $stmt->execute();
            // 获取数据
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data'=>$data,
                'btns'=>$btns,
            ];
        }

        public function findBySn($sn)
        {
            $stmt = self::$pdo->prepare('select * from orders where sn=?');
            $stmt->execute([
                $sn
            ]);
            // 取数据
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 设置订单为已支付的方法
        public function setPaid($sn)
        {
            $stmt = self::$pdo->prepare("update orders set status=1,pay_time=now() where sn=?");
            return $stmt->execute([
                $sn
            ]);
        }
    }
?>