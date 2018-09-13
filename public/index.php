<?php
    
    // 使用 redis 保存 SESSION
    ini_set('session.save_handler', 'redis'); 
    // 设置 redis 服务器的地址、端口、使用的数据库      
    ini_set('session.save_path', 'tcp://127.0.0.1:6379?database=3');  
    // 设置 SESSION 10分钟过期
    ini_set('session.gc_maxlifetime', 600);   
    // 开启session
    session_start();

    // 如果用户以post方式访问网站,需要验证令牌(防止外站提交)
    // if($_SERVER['REQUEST_METHOD'] == 'POST')
    // {
    //     if(!isset($_POST['_token']))
    //         die('违法操作');
    //     if($_POST['_token'] != $_SESSION['token'])
    //         die('违法操作!');
    // }
    // 定义常量
    define('ROOT', dirname(__FILE__) . '/../');

    // 引入 composer 自动加载文件
    require(ROOT.'vendor/autoload.php');

    function autoload($class){
        $path = str_replace('\\', '/', $class);
       
        require(ROOT . $path . '.php');
    }

    spl_autoload_register('autoload');

    // 添加路由(判断在浏览器执行还是在命令行执行)
    if(php_sapi_name() == 'cli'){
        $controller = ucfirst($argv[1]).'Controller';
        $action = $argv[2];
    }
    else {

        if(isset($_SERVER['PATH_INFO']))
        {
            $pathInfo = $_SERVER['PATH_INFO'];
            // 根据 / 转成数组
            $pathInfo = explode('/',$pathInfo);
            // 得到控制名和方法名
            $controller = ucfirst($pathInfo[1]).'Controller';
            $action = $pathInfo[2];
        }
        else 
        {
            // 默认控制器和方法
            $controller = 'IndexController';
            $action = 'index';
        }

    }

    // 为控制器添加命名空间
    $fullController = 'controllers\\'.$controller;

    $C = new $fullController;
    $C ->$action();

    // 获取url上的所有参数,并且还能排除某些参数
    function getUrlParams($except = [])
    {
        // 循环删除已有变量
        foreach($except as $v)
        {
            unset($_GET[$v]);
        }
        // 拼出字符串，循环出地址栏所有变量
        $str = '' ;
        foreach($_GET as $k => $v)
        {
            $str .= "$k=$v&"; 
        }
        return $str;
    }

    // 加载视图
    function view($viewFileName,$data=[]){
        // 解压书组成变量
        extract($data);

        $path = str_replace('.', '/', $viewFileName) . '.html';

        require(ROOT . 'views/' . $path);
    }

    // 获取配置文件(无论调用多少次,只包含一次配置文件)
    // 静态局部变量:函数执行结束也不会销毁,直到脚本结束
    // 普通局部变量:函数执行完就销毁了
    function config($name)
    {
        static $config = null;
        if($config === null)
        {
            // 引入配置文件
            $config = require(ROOT.'config.php');
        }
        

        return $config[$name];
    }

    // 跳转页面
    function redirect($url)
    {
        header('Location:'.$url);
        exit;
    }
    // 返回上一个页面
    function back()
    {
        redirect($_SERVER['HTTP_REFERER']);
    }

    // 提示消息的函数
    // type: 0 : alert  1:显示单独页面  2：在下一个页面显示
    // $seconds只有在type=1的时候有效  代码几秒自动跳转
    function message($message,$type,$url,$seconds=5)
    {
        if($type == 0)
        {
            echo "<script>alert('{$message}');location.href='{$url}';</script>";
            exit;
        }
        else if($type == 1)
        {
            view('common.success',[
                'message' => $message,
                'url' => $url,
                'seconds' => $seconds
            ]);
        }
        else if($type == 2)
        {
            // 把消息保存到session
            $_SESSION['_MESS_'] = $message;
            // 跳转到下一个页面
            redirect($url);
        }
    }

    // 过滤XSS
    function e($content)
    {
        return htmlspecialchars($content);
    }

    // 在线编辑器的过滤
    function hpe($content)
    {
        // 一直保存在内存中(直到脚本结束)
        static $purifier = null;
        // 只有第一次调用才会创建新的对象
        if($purifier === null)
        {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'utf-8');
            $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
            $config->set('Cache.SerializerPath', ROOT.'cache');
            $config->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,ol[start],li,p[style],br,span[style],img[width|height|alt|src],*[style|class],pre,hr,code,h2,h3,h4,h5,h6,blockquote,del,table,thead,tbody,tr,th,td');
            $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,margin,width,height,font-family,text-decoration,padding-left,color,background-color,text-align');
            $config->set('AutoFormat.AutoParagraph', TRUE);
            $config->set('AutoFormat.RemoveEmpty', TRUE);
            $purifier = new \HTMLPurifier($config);
        }
        return $purifier->purify($content);
    }
    
    // csrf防御
    function csrf()
    {
        if(!($_SESSION['token']))
        {
            // 生成一个随机的字符串
            $token = md5(rand(1,99999).microtime());
            $_SESSION['token'] = $token;
        }
        return $_SESSION['token'];
    }

    // 生成令牌隐藏域
    function csrf_field()
    {
        $csrf = isset($_SESSION['token']) ? $_SESSION['token'] : csrf();
        echo "<input type='hidden' name='_token' value='{$csrf}'>";
    }
?>