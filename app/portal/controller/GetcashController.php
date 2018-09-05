<?php
/*
 * 提现
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/8 09:30
 */
namespace app\portal\controller;

use app\portal\model\CashRecordModel;
use app\portal\model\GetcashSetModel;
use app\portal\model\UsersModel;
use cmf\controller\HomeBaseController;
use think\Db;
use think\Session;

class GetcashController extends HomeBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->user_id = Session::get('user_id');
    }

    public function getcash()
    {
//        require_once EXTEND_PATH . 'WxminipayAPI/WxPay.Merch.php';
//        $MerchPay = new \MerchPay();
//        $result = $MerchPay->pay('o3nBT0bdWA40ZR-QfDRXINxstT6s', '2018081454495555', 1, '余额提现');
//        echo "<pre/>";
//        print_r($result);
//        die;
        if(!$this->request->isAjax()){
            $this->apiResponse(0,'非法调用','');
        }
//        require_once EXTEND_PATH . 'WxminipayAPI/WxPay.Merch.php';
//        $MerchPay = new \MerchPay();
        $price = $this->request->param('price','');
        $user = new UsersModel();
        $getcash = new GetcashSetModel();
        $cash_record = new CashRecordModel();
        //逻辑判断
        $userinfo = $user->where('id',$this->user_id)->find()->toArray();
        $low_price = $getcash->where('id',2)->value('content');//最低金额
        $bili = $getcash->where('id',1)->value('content');//提现比例
        $del_coin = 1/$bili*$price;//应扣除的环保币
        if($userinfo['user_status'] == 1){
            $this->apiResponse(0,'您的提现功能被锁定，请联系管理员','');
        }
        if($price < $low_price){
            $this->apiResponse(0,'金额需大于'.$low_price.'元','');
        }
        if($price < 0.3 or $price > 20000){
            $this->apiResponse(0,'提现金额大于0.3小于20000','');
        }
        if($userinfo['coin'] == 0 or $userinfo['coin'] < 0 or $userinfo['coin'] < $del_coin){
            $this->apiResponse(0,'您的余额不足','');
        }
        $order_sn = cmf_get_order_sn();
//        $result = $MerchPay->pay($userinfo['openid'], $order_sn, $price, '余额提现');
//        $result['result_code'] = 'SUCCESS';
//        if ($result['result_code'] != 'SUCCESS') {
//            $this->apiResponse(0,'商户余额不足','');
//        }
        Db::startTrans();
        $res = $user->where('id',$this->user_id)->setDec('coin',$del_coin);
        $res2 = $cash_record->insert([
            'user_id' => $this->user_id,
            'coin' => $del_coin,
            'cash' => $price,
            'create_time' => date('Y-m-d H:i:s'),
            'order_sn' => $order_sn,
            'status' => 0
        ]);
        if(!$res or !$res2){
            Db::rollback();
            $user->where('id',$this->user_id)->setField('user_status',1);
            $this->apiResponse(1,'状态异常','');
        }
        Db::commit();
        $this->apiResponse(1,'等待管理员审核后即可到账','');
    }

}
