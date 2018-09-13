<?php
namespace controllers;

use Yansongda\Pay\Pay;

class AlipayController
{
    
    public $config = [

        // fhjusb7492@sandbox.com

        'app_id' => '2016091700531580',
        // 通知地址
        'notify_url' => 'http://http.tunnel.echomod.cn/alipay/notify',
        // 跳回地址=
        'return_url' => 'http://http.tunnel.echomod.cn/alipay/return',
        // 支付宝公钥
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzjvuCWjJiKvU04EA9fIS3/LzqzdpdMzx7Eo4EQHUX2OCC6cRMoySd9PlEfs8jOq+Sf3eKuTcG9NswFYieytJorRtp9c6ze5sUdqRcctgDSkrl8mEYtu9Q8HuJQ6I+yd65nf0Edf6A+rwYSG2qsoxmJmJHMTbwRBBFOy1BkQ4l81zrM72o+Ib39DFbW2iXpybU6chPdOWS8akaJB/kcfvkIGqKHCBazanbTVpZBOWenpQzBRqSuM+uX6AcFGMbBNXp3BUaIz0KZ4WCzG0sbOWsNnQeli+7OSPbbaosvYuqQ7lqwlQ6HEQwXTyQ2ab+r0+NoF6NoVuUDGtbS/cI+hT2wIDAQAB',
        // 商户应用密钥
        'private_key' => 'MIIEogIBAAKCAQEA8cqiJf7zL1dZ98YdM3KoEeTy3TrLiQFGxb2d4C2reAWp0BlSXUslCpylvaCOjBLvXX/4GzrLIsACKGVUIZKC9zjISwKi91bsfsNvAXO1iWUXCf1YTYaB6M1QANJvXEkNao1/S0jBvCCKOJTwCeK3x932Im14c6r1OXyRXC3K9gmbU9qo2bj/LD6a8UC7+F7je3JPSSwakiG/Cv1IHqO399LB+ZLQgl7v4N3fxQtv5JVaq2w3N0zFO8uJRda6btKvZsmrFJ1F70Ix8r6xPRQjq4przSq9wlRidB8/wHEqGq/EUScX6PWJTU4WKepaloFsSpjxAosdPRGKBNtFkNm02QIDAQABAoIBADDNoaThrNwlWXd1eXdk8R+Lgqm8sFBa/Zn0B+Wz0iRLoFbshX9nJ3bY32tC+QK037OTnNSCLXY6IzVcHnsYQ276+xKI2bPqy5lagRFd9Yph6SCPDj+3ookGx9sinm+WHysenE7pxJDgUIXTKhAUvtuMpZ2VUvalzxFnXz+nzouSSFwldFsbgWQokFVai8XULj9Mrfz3+G7NDmEAApszD51ZjDHk+sV90Dx7IpLM9GyWQUkmBQXYYROMX7K2gifKhFIpkXtv56jmXlLNWpveKiSbKYJvgryM0YZbxz3GRTjvNH9UKjsjlMpITjJXo4IW8beQ/+3gp6yHH5ZcHLJSJ8ECgYEA/zHkIF7pnNe5NJSSUC4LA7uCOA4lAnvZhKwjO1Xy/37XJTwTdn3e2jBV2gNyyLiJIubpOH8fuUvVGMU0HTbO4nHzu7YaZMCq08SRzixMWu/SHb6vLajldZJJHhdyWIUEFLzEidDDi3Ez6Y45ci/mVaGdhTGZ606frKpJxpSD3e0CgYEA8o3qwcDex4N42YNopsVL+gCY5oG+dHZ503NloW3uhJlRglqK81JxRAPCy9CfjsHxKjtt5aQFlm2S00wbg6Dk/Rfd1oAojszvodC+722BJVdei0IYr4b6sL/T2wffaLUXaj9L/4QSaLqjpdxfIBqKS0BYtjr8FH39NAao+FHltR0CgYAWzn2lyvWz16+snE6LLeI8HijgG5uBIlJpQf0QE3lZDhvqLvlKUKt28nTpu/8pIxzWaq6TJwFNzRIpWY5zwe8xW5+9ueOX94QM0vd45oqYYfB0ShNb0ElZfY5dSxkkAhdARzKFYovnTWGnUNefddpu8reiLLGlzuwEQV/AiItUbQKBgBnVS59/LzsIoaJpAIhikwj5b099mg9FXwxKShS7ZJ/yxF+hzRLFQRMBY4nZmmTV+x2IXtgKdB3lZbHjdTq7tCfQluv1klxDL3KIjQ1rGEol2Af07jQjqCeTBrlZSU4Zm3jRbQK1ylAlMLDFm8wLh5ckL06ZUafNE16rJuvrrQ9xAoGAP9JPJkERPDp0RWqtk9p5tLlgkfg2KgFHqLD6pbxauhvOhCEz9JUvOX3twwbP/ItBGWjPnx+nq9+0nNsab9Je5rjwJG2GKhbYOKZaosflU9t5VXc1IJ3d13VTIOgTJ97jctfCbCXwac92mKyvy9cA57XWvXxj2/5kmL4pTv3JAVk=',
        // 沙箱模式（可选）
        'mode' => 'dev',
    ];
    // 发起支付
    public function pay()
    {
        //  接收订单编号
        $sn = $_POST['sn'];
        // 取出订单信息
        $order = new \models\Order;
        // 根据订单编号取出订单信息
        $data = $order->findBySn($sn);

        // 判断订单是否支付
        if($data['status'] == 0)
        {

            $alipay = Pay::alipay($this->config)->web([
                'out_trade_no' => $sn,    // 本地订单ID
                'total_amount' => $data['money'],    // 支付金额
                'subject' => '智聊系统用户充值:'.$data['money'].'元', // 支付标题
            ]);
            $alipay->send();
        }
        else
        {
            die('订单状态不允许支付');
        }
    }
    // 支付完成跳回
    public function return()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！
        echo '<h1>支付成功！</h1> <hr>';
        var_dump( $data->all() );
    }
    
    // 接收支付完成的通知
    public function notify()
    {
        // 生成支付类的对象
        $alipay = Pay::alipay($this->config);
        
        $fp = fopen(ROOT.'logs/pay.log','a');
        fwrite($fp,'支付宝发了通知'."\r\n");

        try{

            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 这里需要对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            
            if($data->trade_status == 'TRADE_SUCCESS' || $data->trade_status == 'TRADE_FINISHED')
            {
                // 更新订单状态
                $order = new \models\order;
                // 获取订单状态
                $orderInfo = $order->findBySn($data->out_trade_no);

                // 判断订单状态,是否是第一次收到消息
                if($orderInfo['status'] == 0)
                {
                    // 开启事务
                    $order->startTrans();

                    // 设置为已支付状态
                    $ret1 = $order->setPaid($data->out_trade_no);

                    // 更新账户余额
                    $user = new \models\User;
                    $ret2 = $user->addMoney($orderInfo['money'],$orderInfo['user_id']);

                    // 判断
                    if($ret1 && $ret2)
                    {
                        // 提交事务
                        $order->commit();
                    }
                    else
                    {
                        // 回滚事务
                        $order->rollback();
                    }

                    $fp = fopen(ROOT.'logs/pay.log','a');
                    fwrite($fp,'成功'."\r\n");
                }                
            }
            // echo '订单ID：'.$data->out_trade_no ."\r\n";
            // echo '支付总金额：'.$data->total_amount ."\r\n";
            // echo '支付状态：'.$data->trade_status ."\r\n";
            // echo '商户ID：'.$data->seller_id ."\r\n";
            // echo 'app_id：'.$data->app_id ."\r\n";
        } catch (\Exception $e) {
            $fp = fopen(ROOT.'logs/pay.log','a');
            fwrite($fp,'错误'."\r\n");

        }
        // 返回响应
        $alipay->success()->send();
    }
    // 退款
    public function refund()
    {
        // 生成唯一退款订单号
        $refundNo = md5( rand(1,99999) . microtime() );
        try{
            // 退款
            $ret = Pay::alipay($this->config)->refund([
                'out_trade_no' => '258653373002149888',    // 之前的订单流水号
                'refund_amount' => 0.01,              // 退款金额，单位元
                'out_request_no' => $refundNo,     // 退款订单号
            ]);
            if($ret->code == 10000)
            {
                echo '退款成功！';
            }
            else
            {
                echo '失败';
                var_dump($ret);
            }
        }
        catch(\Exception $e)
        {
            var_dump( $e->getMessage() );
        }
    }
}