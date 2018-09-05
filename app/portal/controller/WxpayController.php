<?php
/*
 * 微信支付相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/6 18:20
 */
namespace app\portal\controller;

use app\portal\model\GoodsOrderModel;
use app\portal\model\RcsetModel;
use app\portal\model\ReturnRecordModel;
use app\portal\model\UseRecordModel;
use app\portal\model\UsersModel;
use cmf\controller\NolimitController;
use think\Cache;

class WxpayController extends NolimitController
{
    public function _initialize()
    {
        parent::_initialize();
        require_once EXTEND_PATH."WxpayAPI/lib/WxPay.Api.php";
        require_once EXTEND_PATH."WxpayAPI/example/WxPay.JsApiPay.php";
        require_once EXTEND_PATH."WxpayAPI/lib/WxPay.Notify.php";
        require_once EXTEND_PATH.'WxpayAPI/example/log.php';
    }


    public function pay($openId,$price,$order_sn,$url)
    {
        /**
         * 可以删除开始
         */
        $price = 0.01;
        /**
         * 可以删除结束
         */
        $price = intval($price*100);
        $tools = new \JsApiPay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("帮换宝商城");
        $input->SetAttach("帮换宝");
        $input->SetOut_trade_no($order_sn);
        $input->SetTotal_fee($price);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("帮换宝商城");
        $input->SetNotify_url($url);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $config = new \WxPayConfig();
        $order = \WxPayApi::unifiedOrder($config, $input);

        $jsApiParameters = $tools->GetJsApiParameters($order);
        return $jsApiParameters;
    }

    //回调
    public function pay_notify()
    {
        $config = new \WxPayConfig();
        $notify = new \WxPayNotify();
        $notify->Handle($config,false);
        $xml = file_get_contents("php://input");
        $base = new \WxPayResults();
        $data = $base->FromXml($xml);
        //验签
        if($base->CheckSign($config)){
            $notice = new NoticeController();
            $order_sn = $data['out_trade_no'];
            $goods_order = new GoodsOrderModel();
            $user = new UsersModel();
            $orderinfo = $goods_order->where('order_sn',$order_sn)->find()->toArray();
            if($orderinfo['order_status'] != 0){
                return false;
            }
            $res = $goods_order->where('order_sn',$order_sn)->data([
                'order_status' => 4,
                'pay_time' => date('Y-m-d H:i:s')
            ])->update();
            //计入使用记录表(使用环保币)
            if($orderinfo['use_coin'] == 1){
                $use_record = new UseRecordModel();
                $coin = ($orderinfo['total_price'] + $orderinfo['freight_price']) - $orderinfo['true_price'];
                $user->where('id',$orderinfo['user_id'])->setDec('coin',$coin);
                $res_record = $use_record->insert([
                    'user_id' => $orderinfo['user_id'],
                    'coin' => $coin,
                    'fuhao' => '-',
                    'create_time' => date('Y-m-d H:i:s'),
                    'content' => '购买商品'
                ]);
            }
            //返利
            $pid = $user->where('id',$orderinfo['user_id'])->value('pid');
            if(!empty($pid)){
                //比例
                $reset = new RcsetModel();
                $return_record = new ReturnRecordModel();
                $bili = $reset->where('id',1)->value('content');
                $return_price = ($orderinfo['total_price'] + $orderinfo['freight_price'])*$bili;
                $user_nickname = $user->where('id',$orderinfo['user_id'])->value('user_nickname');
                $res_add_user = $user->where('id',$pid)->setInc('coin',$return_price);
                $res_add2_user = $user->where('id',$pid)->setInc('total_coin',$return_price);
                $res_add_record = $return_record->insert([
                    'user_id' => $orderinfo['user_id'],
                    'coin' => $return_price,
                    'create_time' => date('Y-m-d H:i:s'),
                    'content' => $user_nickname."在商城购买商品",
                    'touid' => $pid
                ]);
            }
            if($res){
                $notice->userSend($order_sn);
                return true;
            }
        }
    }


}