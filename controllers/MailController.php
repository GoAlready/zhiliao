<?php
    namespace controllers;

    use libs\Mail;

    class MailController
    {
        public function send()
        {
            $redis = new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => '127.0.0.1',
                'port'   => 6379,
            ]);

            $mailer = new Mail;

            // 设置 php 永不超时
            ini_set('default_socket_timeout', -1); 
            
            echo "消息队列启动成功..\r\n";

            // 循环队列中消息并发邮件
            while(true)
            {
                // 先从队列中取消息
                // 第一个参数:从email中取消息,第二个参数:如果没有消息就堵塞在这里，直到有消息才向后执行代码
                $data = $redis->brpop('email',0);

                // 发邮件 
                // 反序列化(转回数组)
                // json_decode默认转成对象，加参数true转成数组
                $message = json_decode($data[1],true);
                $mailer->send($message['title'],$message['content'],$message['from']);

                echo "发送邮件成功!继续等待下一个消息../r/n";
            }
        }
    }
?>