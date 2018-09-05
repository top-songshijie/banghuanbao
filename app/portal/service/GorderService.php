<?php
/*
 * 商城订单相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/11 10:30
 */
namespace app\portal\service;

use app\portal\model\AddressModel;
use app\portal\model\GoodsCommentModel;
use app\portal\model\RubbishOrderModel;
use app\portal\model\GoodsOrderDetailModel;
use app\portal\model\GoodsOrderModel;
use app\portal\model\UsersModel;

class GorderService
{
    public static function  goods_order_as_two($goods_order_id)
    {
        $goods_order = new GoodsOrderModel();
        $goods_order_detail = new GoodsOrderDetailModel();
        $info = $goods_order->find($goods_order_id)->toArray();
        $info['goods_order_detail'] = $goods_order_detail->where('goods_order_id',$info['id'])->select()->toArray();

        return $info;
    }

    /**
     * 普通用户用户个人中心
     */
    public static function user_index($user_id)
    {
        $users = new UsersModel();
        $goods_order = new GoodsOrderModel();
        $goods_order_detail = new GoodsOrderDetailModel();
        $goods_comment = new GoodsCommentModel();
        $data['user_nickname'] = $users->where('id',$user_id)->value('user_nickname');
        $data['avatar'] = $users->where('id',$user_id)->value('avatar');
        //待付款数量
        $data['order_status1'] = $goods_order->where(['order_status' => 0,'user_id' => $user_id])->count();
        //待发货数量
        $data['order_status2'] = $goods_order->where(['order_status'=>['in',[1,4]],'user_id'=>$user_id])->count();
        //配送中数量,已发货
        $data['order_status3'] = $goods_order->where(['order_status' => 2,'user_id' => $user_id])->count();
        //已完成数量
        $list = $goods_order->where(['order_status' => 3,'user_id' => $user_id])->column('id');
        foreach ($list as $k=>$v){
            $goods_ids = $goods_order_detail->where('goods_order_id',$v)->column('goods_id');
            foreach ($goods_ids as $kk=>$vv){
                $res = $goods_comment->where(['goods_id'=>$vv,'user_id'=>$user_id])->find();
                if($res){
                    unset($list[$k]);
                }
            }
        }
        $data['order_status4'] = count($list);
        return $data;
    }

    /**
     * 员工个人中心
     */
    public static function employee_index($user_id)
    {
        $users = new UsersModel();
        $goods_order = new GoodsOrderModel();
        $rubbish_order = new RubbishOrderModel();
        $data['user_nickname'] = $users->where('id',$user_id)->value('user_nickname');
        $data['avatar'] = $users->where('id',$user_id)->value('avatar');
        //商城订单
        //待接受数量
        $villages_name = UseraddressService::villagesid_to_villagesname($user_id);
        $where['sh_village'] = ['in',$villages_name];
        $where['eid'] = 0;
        $where['order_status'] = 4;
        $data['order_status1'] = $goods_order->where($where)->count();
        //待配送数量
        $data['order_status2'] = $goods_order->where(['order_status' => 1,'eid' => $user_id])->count();
        //配送中数量
        $data['order_status3'] = $goods_order->where(['order_status' => 2,'eid' => $user_id])->count();
        //已完成数量
        $data['order_status4'] = $goods_order->where(['order_status' => 3,'eid' => $user_id])->count();
        //垃圾订单
        //待接受数量
        $where2['village'] = ['in',$villages_name];
        $where2['eid'] = 0;
        $where2['status'] = 0;
        $data['rubbish_order1'] = $rubbish_order->where($where2)->count();
        //未完成数量
        $data['rubbish_order2'] = $rubbish_order->where(['eid' => $user_id,'status' => 1])->count();
        //已完成数量
        $data['rubbish_order3'] = $rubbish_order->where(['eid' => $user_id,'status' => 2])->count();
        return $data;
    }

    /**
     * 用户订单列表
     */
    public  static function user_order_list($user_id,$order_type)
    {
        $goods_order = new GoodsOrderModel();
        $goods_order_detail = new GoodsOrderDetailModel();
        $where['user_id'] = $user_id;
        if($order_type == 1){
            $where['order_status'] = ['in',[1,4]];
        }elseif($order_type === 'all'){
            //查询全部无条件
        }else{
            $where['order_status'] = $order_type;
        }
        $where['if_del'] = 0;
        $data = $goods_order
            ->field('id,order_sn,order_status,total_price')
            ->order('id DESC')
            ->where($where)
            ->select()
            ->toArray();
        foreach ($data as $k=>$v){
            $data[$k]['detail'] = $goods_order_detail
                ->field('id,goods_thumb,attvalue_names,substr(goods_name,1,12) as goods_name,goods_num,one_price,small_price')
                ->where('goods_order_id',$v['id'])
                ->find()
                ->toArray();
            $data[$k]['detail']['goods_thumb'] = cmf_get_image_preview_url($data[$k]['detail']['goods_thumb']);
        }
        return $data;
    }

    /**
     * 用户订单详情
     */
    public static function user_order_detail($order_id)
    {
        $data = self::goods_order_as_two($order_id);
        return $data;
    }

    /**
     * 员工订单列表
     */
    public static function employee_order_list($user_id,$order_type)
    {
        $goods_order = new GoodsOrderModel();
        $goods_order_detail = new GoodsOrderDetailModel();
        $villages_name = UseraddressService::villagesid_to_villagesname($user_id);
        $where['sh_village'] = ['in',$villages_name];
        if($order_type == 4){
            $where['order_status'] = $order_type;
        }else{
            $where['eid'] = $user_id;
            $where['order_status'] = $order_type;
        }
        $where['if_del'] = 0;
        $data = $goods_order
            ->field('id,order_sn,order_status,total_price,sh_village,sh_detail_address,sh_name,sh_mobile')
            ->order('id DESC')
            ->where($where)
            ->select()
            ->toArray();
        foreach ($data as $k=>$v){
            $data[$k]['detail'] = $goods_order_detail
                ->field('id,goods_thumb,attvalue_names,substr(goods_name,1,10) as goods_name,goods_num,one_price,small_price')
                ->where('goods_order_id',$v['id'])
                ->find()
                ->toArray();
        }
        return $data;
    }

}
