<?php
/*
 * 商城订单相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/11 10:23
 */
namespace app\portal\controller;

use app\portal\model\GoodsOrderDetailModel;
use app\portal\model\GoodsOrderModel;
use app\portal\service\GorderService;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminGorderController extends AdminBaseController
{
    public function index()
    {
        $start_time = $this->request->param('start_time','');
        $end_time = $this->request->param('end_time','');
        $keyword = $this->request->param('keyword','');
        $order_status = $this->request->param('order_status','all');
        if(!empty($start_time) and !empty($end_time)){
            $where['o.pay_time'] = array('between',[$start_time,$end_time]);
        }
        if(!empty($keyword)){
            $keyword = trim($keyword);
            $where['o.order_sn | o.sh_name | o.sh_village | u.user_login'] = array('like',"%$keyword%");
        }
        if($order_status != 'all'){
            $where['o.order_status'] = [['eq',$order_status],['neq',0]];
        }else{
            $where['o.order_status'] =['neq',0];
        }
        $goods_order = new GoodsOrderModel();
        $where['o.if_del'] = 0;
        $list = $goods_order
            ->alias('o')
            ->field('o.*,u.user_login')
            ->join('__USERS__ u','u.id = o.eid','left')
            ->where($where)
            ->order('o.id DESC')
            ->paginate(30);
        $page = $list->render();
        $count = $goods_order
            ->alias('o')
            ->field('o.*,u.user_login')
            ->join('__USERS__ u','u.id = o.eid','left')
            ->where($where)
            ->count();
        $price_type = $this->checkprice($where,$goods_order);

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('start_time',empty($start_time)?'':$start_time);
        $this->assign('end_time',empty($end_time)?'':$end_time);
        $this->assign('page',$page);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        $this->assign('order_status',$order_status);
        $this->assign('count',$count);
        $this->assign('price_type',$price_type);
        return $this->fetch();
    }

    //详情
    public function edit()
    {
        $id = $this->request->param('id','','intval');//goods_order表id
        $info = GorderService::goods_order_as_two($id);

        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 统计金额
     */
    public function checkprice($where,$goods_order)
    {
        $list = $goods_order
            ->alias('o')
            ->field('o.*,u.user_login')
            ->join('__USERS__ u','u.id = o.eid','left')
            ->where($where)
            ->select()->toArray();
        $true_price = 0;
        $coin = 0;
        foreach ($list as $k=>$v){
            $true_price += $v['true_price'];
            $tem_coin = $v['total_price'] + $v['freight_price'] - $v['true_price'];
            $coin +=  $tem_coin;
        }
        $data['true_price'] = $true_price;
        $data['coin'] = $coin;
        return $data;
    }

}