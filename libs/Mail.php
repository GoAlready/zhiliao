<?php
    namespace libs;

    class Mail
    {
        public $mailer;
        public function __construct()
        {
            // 设置邮箱服务器账号
            $transport = (new \Swift_SmtpTransport('smtp.126.com', 25))  // 邮件服务器IP地址和端口号
            ->setUsername('hei_time@126.com')       // 发邮件账号
            ->setPassword('asdfghjkl123');      // 授权码
    
            // 创建发邮件对象
            $this->mailer = new \Swift_Mailer($transport);
        }
        public function send($title,$content,$to)
        {
            $message = new \Swift_Message();
    
            $message->setSubject($title)   // 标题
                    ->setFrom(['hei_time@126.com' => '全栈1班'])   // 发件人
                    ->setTo([
                        $to[0],
                        $to[0]=>$to[1]
                    ])   // 收件人
                    ->setBody($content, 'text/html');     // 邮件内容及邮件内容类型
    
            // 发送邮件
           $this->mailer->send($message);
    
        }
    }

?>