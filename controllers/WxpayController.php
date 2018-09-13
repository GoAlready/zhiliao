<?php
namespace controllers;

use Yansongda\Pay\Pay;

class WxpayController
{
    protected $config = [
        'app_id' => 'wx426b3015555a46be', // 公众号 APPID
        'mch_id' => '1900009851',
        'key' => '8934e7d15453e97507ef794cf7b0519d',
        'notify_url' => 'http://http.tunnel.echomod.cn/wxpay/notify',
    ];

    public function pay()
    {
        // 接收订单编号
        $sn = $_POST['sn'];
        // 取出订单信息
        $order = new \models\Order;
        // 根据订单取出订单信息
        $data = $order->findBySn($sn);

        if($data['status'] == 0)
        {
            // 调用微信接口
            $ret = Pay::wechat($this->config)->scan([
                'out_trade_no' => $data['sn'],
                'total_fee' => $data['money'] * 100, // **单位：分**
                'body' => '智聊系统用户充值:'.$data['money'].'元',
            ]);
            if($ret->return_code == 'SUCCESS' && $ret->result_code == 'SUCCESS')
            {
                // 加载视图,把支付码的字符串发到页面中
                view('users.wxpay',[
                    'code' => $ret->code_url,
                    'sn' => $sn,
                ]);

            }
        }
        else
        {
            die('订单状态不允许支付~');
        }
    }

    public function notify()
    {

        $log = new \libs\log('wxpay');

        // 记录日志
        $log->log('接收到微信的消息');

        $pay = Pay::wechat($this->config);

        try{

            $data = $pay->verify(); // 是的，验签就这么简单！

            // 记录日志
            $log->log('验证成功,接收的数据是:'.file_get_contents('php://input'));

            if($data->result_code == 'SUCCESS' && $data->return_code == 'SUCCESS')
            {

                // 记录日志
                $log->log('支付成功');

                // 更新订单状态
                $order = new \models\Order;
                // 获取订单信息
                $orderInfo = $order->findBySn($data->out_trade_no);

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

                    // echo '共支付了：'.$data->total_fee.'分';
                    // echo '订单ID：'.$data->out_trade_no;
                }
            }

        } catch (Exception $e) {
            
            $log->log('验证失败!'.$e->getMessang());

            var_dump( $e->getMessage() );
        }
        
        $pay->success()->send();
    }
}