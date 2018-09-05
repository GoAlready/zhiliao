<?php
    namespace libs;

    class Mail
    {
        public $mailer;
        public function __construct()
        {
            // 从配置文件中读取配置
            $config = config('email');
            // 设置邮箱服务器账号
            $transport = (new \Swift_SmtpTransport($config['host'], $config['port']))  // 邮件服务器IP地址和端口号
            ->setUsername($config['name'])       // 发邮件账号
            ->setPassword($config['pass']);      // 授权码
    
            // 创建发邮件对象
            $this->mailer = new \Swift_Mailer($transport);
        }
        public function send($title,$content,$to)
        {
            // 从配置文件中读取配置
            $config = config('email');

            $message = new \Swift_Message();
    
            $message->setSubject($title)   // 标题
                    ->setFrom([$config['from_email'] => $config['from_name']])   // 发件人
                    ->setTo([
                        $to[0],
                        $to[0]=>$to[1]
                    ])   // 收件人
                    ->setBody($content, 'text/html');     // 邮件内容及邮件内容类型
            // 如果是调试模式就写日志
            if($config['mode'] == 'debug')
            {
                // 获取邮件的所有信息
                $mess = $message -> toString();
                // 把邮件的内容记录到日志中
                $log = new Log('email');
                $log->log($mess);
            }
            else{
                // 发送邮件
                $this->mailer->send($message);
            }
            
    
        }
    }

?>