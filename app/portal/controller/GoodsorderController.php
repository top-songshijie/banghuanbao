<?php
/*
 * 商城订单相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/10 17:02
 */
namespace app\portal\controller;

use app\portal\model\AddressModel;
use app\portal\model\CarModel;
use app\portal\model\FreightModel;
use app\portal\model\GoodsModel;
use app\portal\model\GoodsOrderDetailModel;
use app\portal\model\GoodsOrderModel;
use app\portal\model\RcsetModel;
use app\portal\model\ReturnRecordModel;
use app\portal\model\UserAddressModel;
use app\portal\model\UseRecordModel;
use app\portal\model\UsersModel;
use app\portal\model\XsdataModel;
use app\portal\service\GoodsService;
use app\portal\service\UseraddressService;
use cmf\controller\HomeBaseController;
use think\Db;
use think\Loader;
use think\Session;

class GoodsorderController extends HomeBaseController
{
    public function _initialize()
    {
        $this->user_id = Session::get('user_id');
    }

    /**
     * 直接下订单
     */
    public function put_order()
    {
        $this->check_staff();
        $goods = new GoodsModel();
        $goods_order = new GoodsOrderModel();
        $goods_order_detail = new GoodsOrderDetailModel();
        $user_address = new UserAddressModel();
        $user = new UsersModel();
        $notice = new NoticeController();
        $goods_base = Session::get('shop.goods_base');
        $address_id = $this->request->param('address_id','','intval');
        $use_coin = $this->request->param('use_coin');
        if(empty($goods_base) or empty($address_id)){
            $this->apiResponse(0,'此订单已过期','');
        }
        $coin = $user->where('id',$this->user_id)->value('coin');
        if($use_coin == 'true' and $coin != 0){
            $use_coin = 1;
        }else{
            $coin = 0;
            $use_coin = 0;
        }
        $address_info = $user_address->find($address_id);
        $sh_village = $address_info['village'];
        $sh_detail_address = $address_info['detail_address'];
        $sh_name = $address_info['name'];
        $sh_mobile = $address_info['mobile'];
        $goods_id = $goods_base['goods_id'];
        $goods_info = $goods->field('goods_thumb,goods_name,if_tejia,if_only_exchange,if_recommend')->where('id',$goods_id)->find();
        $goods_thumb = $goods_info['goods_thumb'];
        $attvalue_names = GoodsService::attvalueid_to_attvaluename($goods_base['goods_attvalue_id']);
        $one_price = GoodsService::get_oneprice($goods_id,$goods_base['goods_attvalue_id']);
        $small_price = $one_price*$goods_base['num'];
        $total_price = $one_price*$goods_base['num'];
        $goods_name = $goods_info['goods_name'];
        $goods_num = $goods_base['num'];
        $order_sn = cmf_get_order_sn();
        if($goods_info['if_only_exchange'] == 1){
            //兑换的运费
            $freight = new FreightModel();
            $freight_price = $freight->where('id',1)->value('freight_price');
            //兑换
            $use_coin = 3;
            $user_coin = $user->where('id',$this->user_id)->value('coin');
            $true_price = $total_price + $freight_price;
            //判断环保币是否足够
            if($user_coin < $true_price){
                $this->apiResponse(0,'环保币不足','');
            }
            $pay_way = 1;
            $order_status = 4;
            $pay_time = date('Y-m-d H:i:s');
        }else{
            //现金支付
            //现金支付的运费
            $freight = new FreightModel();
            $freight_price = $freight->where('id',2)->value('freight_price');
            if($goods_info['if_tejia'] == 1){
                //判断是否到时
                $xsdata = new XsdataModel();
                $xsinfo = $xsdata->find(1)->toArray();
                if($xsinfo['end_time'] < time()){
                    $this->apiResponse(0,'倒计时结束，下次再购买吧','');
                }
            }
            $true_price = $total_price - $coin + $freight_price;
            if($true_price < 0){
                $true_price = 0;
            }
            $pay_way = 0;
            $order_status = 0;
            $pay_time = '';
        }
        Db::startTrans();
        $data_order = [
            'user_id' => $this->user_id,
            'order_sn' => $order_sn,
            'create_time' => date('Y-m-d H:i:s'),
            'order_status' => $order_status,
            'if_del' => 0,
            'sh_village' => $sh_village,
            'sh_detail_address' => $sh_detail_address,
            'sh_name' => $sh_name,
            'sh_mobile' => $sh_mobile,
            'total_price' => $total_price,
            'true_price' => $true_price,
            'pay_way' => $pay_way,
            'use_coin' => $use_coin,
            'pay_time' => $pay_time,
            'freight_price' => $freight_price
        ];
        $validate = Loader::validate('GoodsOrder');
        if (!$validate->check($data_order)) {
            $error_data = $validate->getError();
        }
        $goods_order_id = $goods_order->insertGetId($data_order);
        $data_order_detail = [
            'goods_order_id' => $goods_order_id,
            'order_sn' => $order_sn,
            'goods_id' => $goods_id,
            'goods_thumb' => $goods_thumb,
            'attvalue_names' => $attvalue_names,
            'one_price' => $one_price,
            'small_price' => $small_price,
            'goods_name' => $goods_name,
            'goods_num' => $goods_num
        ];
        if($goods_info['if_only_exchange'] == 1){
            $res_coin = $user->where('id',$this->user_id)->setDec('coin',$true_price);
            if(!$res_coin){
                $error_data = 1;
            }
            //计入使用记录表
            $use_record = new UseRecordModel();
            $res_record = $use_record->insert([
                'user_id' => $this->user_id,
                'coin' => $true_price,
                'fuhao' => '-',
                'create_time' => date('Y-m-d H:i:s'),
                'content' => '购买商品'
            ]);
            if(!$res_record){
                $error_data = 1;
            }
            //返利
            $pid = $user->where('id',$this->user_id)->value('pid');
            if(!empty($pid)){
                //比例
                $reset = new RcsetModel();
                $return_record = new ReturnRecordModel();
                $bili = $reset->where('id',1)->value('content');
                $return_price = ($total_price + $freight_price)*$bili;
                $user_nickname = $user->where('id',$this->user_id)->value('user_nickname');
                $res_add_user = $user->where('id',$pid)->setInc('coin',$return_price);
                $res_add2_user = $user->where('id',$pid)->setInc('total_coin',$return_price);
                $res_add_record = $return_record->insert([
                    'user_id' => $this->user_id,
                    'coin' => $return_price,
                    'create_time' => date('Y-m-d H:i:s'),
                    'content' => $user_nickname."在商城购买商品",
                    'touid' => $pid
                ]);
                if(!$res_add_user or !$res_add_record or !$res_add2_user){
                    $error_data = 1;
                }
            }
        }else{
            //使用环保币抵现,在回调中减少
//            if($use_coin == 1){
//                //环保币不为0将用户环保币归0
//                if($coin != 0){
//                    $res_coin = $user->where('id',$this->user_id)->setDec('coin',$use_coin_num);
//                    if(!$res_coin){
//                        $error_data = 1;
//                    }
//                }
//            }
        }
        $res = $goods_order_detail->insertGetId($data_order_detail);
        if (!$goods_order_id or !$res or isset($error_data)) {
            Db::rollback();
            $this->apiResponse(0,'网络延迟,请稍后重试','');
        }
        Session::delete('shop.goods_base');
        Session::delete('shop');
        Db::commit();
        if($goods_info['if_only_exchange'] == 1){
            $notice->userSend($order_sn);
            $this->apiResponse(1,'环保币','');
        }else{
            if($true_price == 0){
                $res = $this->pay_notify($order_sn);
                if($res){
                    $notice->userSend($order_sn);
                    $this->apiResponse(1,'微信支付，但已用环保币全部抵现','');
                }
            }
            $pay = new WxpayController();
            $openid = $user->where('id',$this->user_id)->value('openid');
            $wxpaydata = $pay->pay($openid,$true_price,$order_sn,url('Portal/Wxpay/pay_notify','','',true));
            $this->apiResponse(1,'微信',$wxpaydata);
        }

    }

    /**
     * 通过购物车下订单
     */
    public function car_put_order()
    {
        $this->check_staff();
        $car = new CarModel();
        $goods_order = new GoodsOrderModel();
        $goods_order_detail = new GoodsOrderDetailModel();
        $goods = new GoodsModel();
        $user_address = new UserAddressModel();
        $user = new UsersModel();
        $freight = new FreightModel();
        $notice = new NoticeController();
        $address_id = $this->request->param('address_id','','intval');
        $use_coin = $this->request->param('use_coin');
        $coin = $user->where('id',$this->user_id)->value('coin');
        if($use_coin == 'true' and $coin != 0){
            $use_coin = 1;
        }else{
            $coin = 0;
            $use_coin = 0;
        }
        $car_id = Session::get('shop.car_id');
        if(empty($car_id) or empty($address_id)){
            $this->apiResponse(0,'此订单已过期','');
        }
        $address_info = $user_address->find($address_id);
        $sh_village = $address_info['village'];
        $sh_detail_address = $address_info['detail_address'];
        $sh_name = $address_info['name'];
        $sh_mobile = $address_info['mobile'];
        $arr_car_id = explode(',',$car_id);
        $order_sn = cmf_get_order_sn();
        $freight_price = $freight->where('id',2)->value('freight_price');
        Db::startTrans();
        //插入订单表，得到$res_order就是订单id
        $res_order = $goods_order->insertGetId([
            'user_id' => $this->user_id,
            'order_sn' => $order_sn,
            'create_time' => date('Y-m-d H:i:s'),
            'order_status' => 0,
            'if_del' => 0,
            'sh_village' => $sh_village,
            'sh_detail_address' => $sh_detail_address,
            'sh_name' => $sh_name,
            'sh_mobile' => $sh_mobile,
            'total_price' => 0,
            'true_price' => 0,
            'pay_way' => 0,
            'use_coin' => $use_coin,
            'freight_price' => $freight_price
        ]);
        $total_price = 0;
        $error_data = false;
        foreach ($arr_car_id as $k=>$v){
            $goods_id = $car->where('id',$v)->value('goods_id');
            $goods_thumb = $goods->where('id',$goods_id)->value('goods_thumb');
            $goods_attvalue_id = $car->where('id',$v)->value('goods_attvalue_id');
            $attvalue_name = GoodsService::attvalueid_to_attvaluename($goods_attvalue_id);
            $one_price =GoodsService::get_oneprice($goods_id,$goods_attvalue_id);
            $num = $car->where('id',$v)->value('num');
            $small_price = $one_price * $num;
            $total_price += $small_price;
            $goods_name = $goods->where('id',$goods_id)->value('goods_name');
            $res_order_detail = $goods_order_detail->insertGetId([
                'goods_order_id' => $res_order,
                'order_sn' => $order_sn,
                'goods_id' => $goods_id,
                'goods_thumb' => $goods_thumb,
                'attvalue_names' => $attvalue_name,
                'one_price' => $one_price,
                'small_price' => $small_price,
                'goods_name' => $goods_name,
                'goods_num' => $num
            ]);
            $res_car = $car->where('id',$v)->delete();
            if(!$res_order_detail or !$res_car){
                $error_data = true;
                break;
            }
        }
        $true_price = $total_price + $freight_price - $coin;
        if($true_price < 0){
            $true_price = 0;
        }
        $res_order2 = $goods_order->where('id',$res_order)->data([
            'total_price' => $total_price,
            'true_price' => $true_price
        ])->update();
        if(!$res_order or $error_data or !$res_order2 or !$res_car){
            Db::rollback();
            $this->apiResponse(0,'网络环境不佳','');
        }
        Db::commit();
        Session::delete('shop.car_id');
        Session::delete('shop');
        if($true_price == 0){
            $res = $this->pay_notify($order_sn);
            if($res){
                $notice->userSend($order_sn);
                $this->apiResponse(1,'微信支付，但已用环保币全部抵现','');
            }
        }
        $pay = new WxpayController();
        $openid = $user->where('id',$this->user_id)->value('openid');
        $wxpaydata = $pay->pay($openid,$true_price,$order_sn,url('Portal/Wxpay/pay_notify','','',true));
        $this->apiResponse(1,'微信',$wxpaydata);

    }

    /**
     * 现金支付，环保币全部抵现回调
     */
    private function pay_notify($order_sn)
    {
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
        $use_coin_num = $orderinfo['total_price'] + $orderinfo['freight_price'];
        $user->where('id',$orderinfo['user_id'])->setDec('coin',$use_coin_num);
        //计入使用记录表(使用环保币)
        $use_record = new UseRecordModel();
        $res_record = $use_record->insert([
            'user_id' => $orderinfo['user_id'],
            'coin' => $orderinfo['total_price'] + $orderinfo['freight_price'],
            'fuhao' => '-',
            'create_time' => date('Y-m-d H:i:s'),
            'content' => '购买商品'
        ]);
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
            return true;
        }
    }

    /**
     * 放入购物车
     */
    public function put_car()
    {
        $this->check_staff();
        $goods_id = $this->request->param('goods_id','','intval');
        $goods_attvalue_id = $this->request->param('goods_attvalue_id');
        $num = $this->request->param('num',1,'intval');
        if(empty($goods_id) or empty($goods_attvalue_id)){
            $this->apiResponse(0,'请选择规格属性','');
        }
        $goods_attvalue_id = ltrim($goods_attvalue_id,',');
        $car = new CarModel();
        $if_exist = $car->where([
            'user_id'  => $this->user_id,
            'goods_id' => $goods_id,
            'goods_attvalue_id' => $goods_attvalue_id
        ])->find();
        if($if_exist){
            $res = $car->where('id',$if_exist['id'])->setInc('num',$num);
        }else{
            $data = [
                'user_id' => $this->user_id,
                'goods_id' => $goods_id,
                'goods_attvalue_id' => $goods_attvalue_id,
                'num' => $num,
                'create_time' => date('Y-m-d H:i:s')
            ];
            $res = $car->insert($data);
        }
        if(!$res){
            $this->apiResponse(0,'网络延迟','');
        }
        $car = new CarModel();
        $car_num = $car->where('user_id',$this->user_id)->sum('num');
        $this->apiResponse(1,'加入购物车成功!',$car_num);
    }

    /**
     * 购物车增加
     */
    public function add_car()
    {
        $this->check_staff();
        $car_id = $this->request->param('car_id','');
        if(empty($car_id)){
            $this->apiResponse(0,'参数异常','');
        }
        $car = new CarModel();
        $res = $car->where('id',$car_id)->setInc('num',1);
        if(!$res){
            $this->apiResponse(0,'网络异常','');
        }
        $this->apiResponse(1,'成功','');
    }

    /**
     * 购物车减少
     */
    public function dec_car()
    {
        $this->check_staff();
        $car_id = $this->request->param('car_id');
        if(empty($car_id)){
            $this->apiResponse(0,'参数异常','');
        }
        $car = new CarModel();
        $num = $car->where('id',$car_id)->value('num');
        if($num == 1 or $num <1){
            $this->apiResponse(0,'不能再减少了','');
        }
        $res = $car->where('id',$car_id)->setDec('num',1);
        if(!$res){
            $this->apiResponse(0,'网络异常','');
        }
        $this->apiResponse(1,'成功','');
    }

    /**
     * 购物车删除
     */
    public function del_car()
    {
        $this->check_staff();
        $car_id = $this->request->param('car_id','');
        if(empty($car_id)){
            $this->apiResponse(0,'参数异常','');
        }
        $car = new CarModel();
        $res = $car->where('id',$car_id)->delete();
        if(!$res){
            $this->apiResponse(0,'网络异常','');
        }
        $this->apiResponse(1,'删除成功','');
    }


    /**
     * 购物车指定数量
     */
    public function car_make_num()
    {
<<<<<<< HEAD
        $this->check_staff();
=======
        $order_sn = cmf_get_order_sn();
        $sh_village = "鑫茂科技园";
        $sh_detail_address = "F座416";
        $sh_name = "小杰";
        $sh_mobile = "13102112093";
        $arr_car_id = array(1);//购物车id数组
        Db::startTrans();
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $car = new CarModel();
        $car_id = $this->request->param('car_id','','intval');
        $car_num = $this->request->param('car_num','','intval');
        if(empty($car_id) or empty($car_num)){
            $this->apiResponse(0,'参数异常','');
        }
        $res = $car->where('id',$car_id)->setField('num',$car_num);
        if(!$res){
            $this->apiResponse(0,'网络异常','');
        }
        $this->apiResponse(1,'删除成功','');
    }

    /**
     * 判断有无默认地址，有跳入订单详情，无跳入空地址页面(购物车流程)
     */
    public function if_default_address()
    {
        $this->check_staff();
        $car_id = $this->request->param('car_id','');
        if(empty($car_id)){
            $this->apiResponse(0,'请至少选择一样商品','');
        }
        $car_id = ltrim($car_id,',');

        $user_address = new UserAddressModel();
        $addressinfo = $user_address->where([
            'user_id' => $this->user_id,
            'is_default' => 1
        ])->find();
        Session::set('shop.car_id',$car_id);
        if(!$addressinfo){
            $this->apiResponse(1,'跳往空地址页面',url('Goodsorder/empty_address'));
        }else{
            $this->apiResponse(1,'跳往订单详情页面',url('Goodsorder/order_detail'));
        }

    }

    /**
     * 判断有无默认地址，有跳入订单详情，无跳入空地址页面(直接购买流程)
     */
    public function shop_if_default_address()
    {
        $this->check_staff();
        $post = $this->request->param();
        $post['goods_attvalue_id'] = ltrim($post['goods_attvalue_id'],',');
        if(empty($post['goods_attvalue_id'])){
            $this->apiResponse(0,'请选择规格属性','');
        }

        $user_address = new UserAddressModel();
        $addressinfo = $user_address->where([
            'user_id' => $this->user_id,
            'is_default' => 1
        ])->find();
        Session::set('shop.goods_base',$post);
        if(!$addressinfo){
            $this->apiResponse(1,'跳往空地址页面',url('Goodsorder/empty_address'));
        }else{
            $this->apiResponse(1,'跳往订单详情页面',url('Goodsorder/shop_order_detail'));
        }

    }

    /**
     * 空地址页面(购物车流程)
     */
    public function empty_address()
    {
        $this->check_staff();
        return $this->fetch();
    }

    /**
     * 订单详情页面(通过购物车)
     */
    public function order_detail()
    {
        $this->check_staff();
        $goods = new GoodsModel();
        $car = new CarModel();
        $user_address = new UserAddressModel();
        $user = new UsersModel();
        $car_id = Session::get('shop.car_id');
        $address_id = $this->request->param('address_id','');
        $arr_car_id = explode(',',$car_id);
        foreach ($arr_car_id as $k=>$v){
            $arr_goods_info[] = $car
                ->field('goods_id,goods_attvalue_id,num')
                ->where('id',$v)
                ->find()->toArray();
        }
        $total_price = 0;
        foreach ($arr_goods_info as $k=>$v){
            $arr_goods_info[$k]['attvaluename'] = GoodsService::attvalueid_to_attvaluename($v['goods_attvalue_id']);
            $arr_goods_info[$k]['one_price'] = GoodsService::get_oneprice($v['goods_id'],$v['goods_attvalue_id']);
            $total_price += $arr_goods_info[$k]['one_price']*$v['num'];
            $arr_goods_info[$k]['goods_info'] = $goods->field('goods_name,goods_thumb')->find($v['goods_id'])->toArray();
        }
        if(empty($address_id)){
            $where = ['user_id' => $this->user_id, 'is_default' => 1];
        }else{
            $where['id'] = $address_id;
        }
        $address_info = $user_address->where($where)->find()->toArray();
        //环保币
        $coin = $user->where('id',$this->user_id)->value('coin');
        $freight = new FreightModel();
        $freight_price = $freight->where('id',2)->value('freight_price');
        //现金支付实付款
        $true_price = $total_price - $coin + $freight_price;
        if($true_price < 0){
            $true_price = 0;
        }
        $signPackage = $this->getSignPackage();

        $this->assign('total_price',$total_price);
        $this->assign('address_info',$address_info);
        $this->assign('list',$arr_goods_info);
        $this->assign('car_id',$car_id);
        $this->assign('true_price',$true_price);
        $this->assign('coin',$coin);
        $this->assign('freight_price',$freight_price);
        $this->assign('signPackage',$signPackage);
        return $this->fetch();
    }

    /**
     * 订单详情页面（直接购买）
     */
    public function shop_order_detail()
    {
        $this->check_staff();
        $goods = new GoodsModel();
        $car = new CarModel();
        $user_address = new UserAddressModel();
        $user = new UsersModel();
        $address_id = $this->request->param('address_id','');
        $goods_base = Session::get('shop.goods_base');
        //查询地址
        if(empty($address_id)){
            $where = ['user_id' => $this->user_id, 'is_default' => 1];
        }else{
            $where['id'] = $address_id;
        }
        $address_info = $user_address->where($where)->find()->toArray();
        //商品信息
        $goods_info = $goods
            ->field('id,goods_thumb,goods_name')
            ->where('id',$goods_base['goods_id'])
            ->find()->toArray();
        $goods_info['num'] = $goods_base['num'];
        $goods_info['one_price'] = GoodsService::get_oneprice($goods_base['goods_id'],$goods_base['goods_attvalue_id']);
        $goods_info['attvaluename'] = GoodsService::attvalueid_to_attvaluename($goods_base['goods_attvalue_id']);
        $total_price = $goods_info['one_price']*$goods_info['num'];
        //环保币
        $coin = $user->where('id',$this->user_id)->value('coin');
        //是否只是环保币兑换
        $if_only_exchange = $goods->where('id',$goods_info['id'])->value('if_only_exchange');
        $signPackage = $this->getSignPackage();

        $freight = new FreightModel();
        if($if_only_exchange == 1){
            //兑换商品实付款
            $freight_price = $freight->where('id',1)->value('freight_price');
            $true_price = $total_price + $freight_price;
        }else{
            $freight_price = $freight->where('id',2)->value('freight_price');
            //现金支付实付款
            $true_price = $total_price - $coin + $freight_price;
            if($true_price < 0){
                $true_price = 0;
            }
        }
        $this->assign('address_info',$address_info);
        $this->assign('coin',$coin);
        $this->assign('true_price',$true_price);
        $this->assign('goods_info',$goods_info);
        $this->assign('total_price',$total_price);
        $this->assign('if_only_exchange',$if_only_exchange);
        $this->assign('signPackage',$signPackage);
        $this->assign('freight_price',$freight_price);
        return $this->fetch();
    }

    /**
     * 添加地址页面
     */
    public function add_address()
    {
        $this->check_staff();
        $address = new AddressModel();
        $addresslist = $address
            ->field('village as label,id as value')
            ->select()->toArray();
        $addresslist = json_encode($addresslist);

        $this->assign('addresslist',$addresslist);

        $this->assign('url',url('Goodsorder/address_list'));
        return $this->fetch();
    }

    /**
     * 地址列表
     */
    public function address_list()
    {
        $this->check_staff();
        $user_address = new UserAddressModel();
        $list = $user_address->where('user_id',$this->user_id)->select()->toArray();

        $car_id = Session::get('shop.car_id');
        $goods_base = Session::get('shop.goods_base');
        if(!empty($car_id)){
            $this->assign('type','car');
        }elseif(!empty($goods_base)){
            $this->assign('type','shop');
        }else{
            //普通编辑
            $this->assign('type','');
        }
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 添加地址提交
     */
    public function add_address_post()
    {
        $this->check_staff();
        $village = $this->request->param('village','');
        $detail_address = $this->request->param('detail_address','');
        $name = $this->request->param('name','');
        $mobile = $this->request->param('mobile','');
        $user_address = new UserAddressModel();
        $res2 = $user_address->where('user_id',$this->user_id)->setField('is_default',0);
        $res = $user_address->insert([
            'user_id' => $this->user_id,
            'village' => $village,
            'detail_address' => $detail_address,
            'name' => $name,
            'mobile' => $mobile,
            'is_default' => 1,
            'create_time' => date('Y-m-d H:i:s')
        ]);
<<<<<<< HEAD
        if(!$res){
           $this->apiResponse(0,'网络延迟','');
        }
        $this->apiResponse(1,'添加成功','');
    }

    /**
     * 设为默认地址
     */
    public function set_default_address()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            //user_address表id
            $user_addrsss_id = $this->request->param('user_addrsss_id','','intval');
            $res = UseraddressService::set_default($this->user_id,$user_addrsss_id);
            if($res){
                $this->apiResponse(1,'设置成功','');
            }
        }
    }

    /**
     * 删除地址
     */
    public function del_address()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            //user_address表id
            $user_addrsss_id = $this->request->param('id','','intval');
            $user_address = new UserAddressModel();
            $is_default = $user_address->where('id',$user_addrsss_id)->value('is_default');
            if($is_default == 1){
                $this->apiResponse(0,'默认地址不能删除','');
            }
            $res = $user_address->where('id',$user_addrsss_id)->delete();
            if($res){
                $this->apiResponse(1,'删除成功','');
            }
        }
    }

    /**
     * 编辑地址
     */
    public function edit_address()
    {
        $this->check_staff();
        $car_id = $this->request->param('car_id','','intval');
        $user_address_id = $this->request->param('user_address_id','','intval');
        $user_address = new UserAddressModel();
        $info = $user_address->find($user_address_id);
        $address = new AddressModel();
        $addresslist = $address
            ->field('village as label,id as value')
            ->select()->toArray();
        $addresslist = json_encode($addresslist);

        $this->assign('addresslist',$addresslist);
        $this->assign('car_id',$car_id);
        $this->assign('info',$info);
        $this->assign('url',url('Goodsorder/address_list',array('car_id'=>$car_id)));
        return $this->fetch();
    }

    /**
     * 编辑地址提交
     */
    public function edit_address_post()
    {
        $this->check_staff();
        $post = $this->request->param();
        $post['create_time'] = date('Y-m-d H:i:s');
        $post['user_id'] = $this->user_id;
        $user_address = new UserAddressModel();
        $res = $user_address->update($post);
        if($res){
            $this->apiResponse(1,'修改成功','');
        }
    }

    /**
     * 确认收货
     */
    public function confirm_shouhuo()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            $order_id = $this->request->param('order_id','','intval');
            $goods_order = new GoodsOrderModel();
            $order_status = $goods_order->where('id',$order_id)->value('order_status');
            if($order_status == 2){
                $res = $goods_order->where('id',$order_id)->setField('order_status',3);
                if($res){
                    $this->apiResponse(1,'ok','');
                }
            }
        }
    }

    /**
     * 取消订单
     */
    public function cancel_order()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            $order_id = $this->request->param('order_id','','intval');
            $goods_order = new GoodsOrderModel();
            $order_status = $goods_order->where('id',$order_id)->value('order_status');
            if($order_status == 0){
                $res = $goods_order->where('id',$order_id)->delete();
                if($res){
                    $this->apiResponse(1,'ok','');
                }
            }
        }
    }

    /**
     * 已下订单去支付
     */
    public function topay()
    {
        if($this->request->isAjax()){
            //goods_order表id
            $id = $this->request->param('id','','intval');
            $goods_order = new GoodsOrderModel();
            $user = new UsersModel();
            $goods = new GoodsModel();
            $goods_order_detail = new GoodsOrderDetailModel();
            $info = $goods_order
                ->field('id,user_id,order_sn,order_status,if_del,true_price,pay_way,use_coin,total_price,freight_price')
                ->where('id',$id)
                ->find()->toArray();
            if($info['order_status'] != 0 or $info['if_del'] !=0 or $info['pay_way'] != 0){
                $this->apiResponse(0,'订单已过期','');
            }
            //验证现在的coin是否足够
            $coin = $user->where('id',$this->user_id)->value('coin');
            if($info['use_coin'] == 1){
                if($coin < ($info['total_price']+$info['freight_price'])-$info['true_price']){
                    $this->apiResponse(0,'您的环保币不足，请重新下单');
                }
            }
            //验证买的商品是否存在抢购商品
            $arr_goods_id = $goods_order_detail->where('goods_order_id',$id)->column('goods_id');
            $where['if_tejia'] = 1;
            $where['id'] = ['in',$arr_goods_id];
            $xs = $goods->where($where)->find();
            if(!empty($xs)){
                //判断是否到时
                $xsdata = new XsdataModel();
                $xsinfo = $xsdata->find(1)->toArray();
                if($xsinfo['end_time'] < time()){
                    $this->apiResponse(0,'您购买的商品存在已到时的，请重新下单','');
                }
            }

            $openid = $user->where('id',$info['user_id'])->value('openid');
            $pay = new WxpayController();

            $wxpaydata = $pay->pay($openid,$info['true_price'],$info['order_sn'],url('Portal/Wxpay/pay_notify','','',true));
            $this->apiResponse(1,'获取成功',$wxpaydata);
=======
        $total_price = 0;
        $error_data = false;
        foreach ($arr_car_id as $k=>$v){
            //获取商品id
            $goods_id = $car->where('id',$v)->value('goods_id');
            //获取商品缩略图
            $goods_thumb = $goods->where('id',$goods_id)->value('goods_thumb');
            //获取用户选定的商品属性值
            $goods_attvalue_id = $car->where('id',$v)->value('goods_attvalue_id');
            //通过属性值ID 获取属性值名称
            $attvalue_name = GoodsService::attvalueid_to_attvaluename($goods_attvalue_id);
            //通过商品ID 和商品属性ID 返回用户所选商品的价格
            $one_price =GoodsService::get_oneprice($goods_id,$goods_attvalue_id);
            //获取此商品的数量
            $num = $car->where('id',$v)->value('num');
            //一件商品的总价
            $small_price = $one_price * $num;
            //所有商品的总价
            $total_price += $small_price;
            //获取商品名称
            $goods_name = $goods->where('id',$goods_id)->value('goods_name');

            $res_order_detail = $goods_order_detail->insertGetId([
                'goods_order_id' => $res_order,
                'order_sn' => $order_sn,
                'goods_id' => $goods_id,
                'goods_thumb' => $goods_thumb,
                'attvalue_names' => $attvalue_name,
                'one_price' => $one_price,
                'small_price' => $small_price,
                'goods_name' => $goods_name,
                'goods_num' => $num
            ]);
            //删除购物车
            $res_car = $car->where('id',$v)->delete();
            if(!$res_order_detail or !$res_car){
                $error_data = true;
                break;
            }
        }
        //插入总价
        $res_order2 = $goods_order->where('id',$res_order)->setField('total_price',$total_price);

        if(!$res_order or $error_data or !$res_order2 or !$res_car){
            Db::rollback();
            echo "has error";die();
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        }
    }

}
