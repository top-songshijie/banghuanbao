<?php
/*
 * 换宝订单相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/14 13:34
 */
namespace app\portal\service;

use app\portal\model\RubbishOrderModel;

class RorderService
{
    /**
     * 用户订单列表
     */
    public static function user_order_list($user_id,$order_type)
    {
        $rubbish_order = new RubbishOrderModel();
        $where['uid'] = $user_id;
        if($order_type == 1){
            $where['status'] = ['in',[0,1]];
        }else{
            $where['status'] = $order_type;
        }
        $data = $rubbish_order
            ->field('id,rubbish_name,cometime,status')
            ->where($where)
            ->order('id DESC')
            ->select()
            ->toArray();
        return $data;
    }

    /**
     * 订单详情
     */
    public static function order_detail($rubbish_order_id)
    {
        $rubbish_order = new RubbishOrderModel();
        $data = $rubbish_order->where('id',$rubbish_order_id)->find()->toArray();
        $data['detail']['rubbish_name'] = explode(',',$data['rubbish_name']);
        $data['detail']['rubbish_price'] = explode(',',$data['rubbish_price']);
        if(!empty($data['rubbish_weight'])){
            $data['detail']['rubbish_weight'] = explode(',',$data['rubbish_weight']);
        }
        return $data;
    }

    /**
     * 员工订单列表
     */
    public static function employee_order_list($user_id,$order_type)
    {
        $rubbish_order = new RubbishOrderModel();
        $villages_name = UseraddressService::villagesid_to_villagesname($user_id);
        $where['village'] = ['in',$villages_name];
        if($order_type == 0){
            $where['status'] = $order_type;
        }else{
            $where['eid'] = $user_id;
            $where['status'] = $order_type;
        }
        $where['if_del'] = 0;

        $data = $rubbish_order
            ->field('id,rubbish_name,cometime,name,mobile,village,detail_address')
            ->where($where)
            ->order('id DESC')
            ->select()
            ->toArray();
        return $data;
    }

}
