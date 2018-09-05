<?php
/*
 * 废品订单部分
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/9 15:31
 */
namespace app\portal\controller;

use app\portal\model\RubbishOrderModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminRorderController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        $start_time = $this->request->param('start_time','');
        $end_time = $this->request->param('end_time','');
        $status = $this->request->param('status','all');
        if(!empty($start_time) and !empty($end_time)){
            $where['r.create_time'] = ['between time',[$start_time,$end_time]];
        }
        if(!empty($keyword)){
            $keyword = trim($keyword);
            $where['r.order_sn | r.village | u.user_nickname | e.user_login | r.rubbish_name'] = ['like',"%$keyword%"];
        }
        if($status != 'all'){
            $where['r.status'] = $status;
        }
        $rubbish_order = new RubbishOrderModel();
        $list = $rubbish_order
            ->alias('r')
            ->field('r.*,u.user_nickname as u_name,e.user_login as e_name')
            ->join('__USERS__ u','u.id = r.uid')
            ->join('__USERS__ e','e.id = r.eid','left')
            ->where($where)
            ->order('r.id DESC')
            ->paginate(30);
        $page = $list->render();
        $count = $rubbish_order
            ->alias('r')
            ->field('r.*,u.user_nickname as u_name,e.user_login as e_name')
            ->join('__USERS__ u','u.id = r.uid')
            ->join('__USERS__ e','e.id = r.eid','left')
            ->where($where)
            ->count();
        $rubbish_weight = $this->checknum($where);
        $js_price = $this->checkprice($where,$rubbish_order,$keyword);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        $this->assign('start_time',empty($start_time)?'':$start_time);
        $this->assign('end_time',empty($end_time)?'':$end_time);
        $this->assign('status',$status);
        $this->assign('count',$count);
        $this->assign('rubbish_weight',$rubbish_weight);
        $this->assign('js_price',$js_price);
        return $this->fetch();
    }

    //详情
    public function edit()
    {
        $id = $this->request->param('id','','intval');
        $rubbish_order = new RubbishOrderModel();
        $info = $rubbish_order
            ->alias('r')
            ->field('r.*,u.user_nickname as u_name,g.user_nickname as g_name')
            ->join('__USERS__ u','u.id = r.uid','left')
            ->join('__USERS__ g','g.id = r.eid','left')
            ->where('r.id',$id)
            ->find();

        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 检测各种垃圾数量
     */
    private function checknum($where)
    {
        $rubbish_order = new RubbishOrderModel();
        $list = $rubbish_order
            ->alias('r')
            ->field('r.*,u.user_nickname as u_name,e.user_login as e_name')
            ->join('__USERS__ u','u.id = r.uid')
            ->join('__USERS__ e','e.id = r.eid','left')
            ->where($where)
            ->select()->toArray();
        $yifu = 0;
        $suliao = 0;
        $boli = 0;
        $zhizhang = 0;
        $jinshu = 0;
        $dianqi = 0;
        foreach ($list as $k=>$v){
            $rubbish_name = explode(',',$v['rubbish_name']);
            if(!empty($v['rubbish_weight'])){
                $rubbish_weight = explode(',',$v['rubbish_weight']);
                foreach ($rubbish_name as $kk=>$vv){
                    if($vv == '衣服'){
                        $yifu += $rubbish_weight[$kk];
                    }elseif($vv == '塑料'){
                        $suliao += $rubbish_weight[$kk];
                    }elseif($vv == '玻璃'){
                        $boli += $rubbish_weight[$kk];
                    }elseif($vv == '纸张'){
                        $zhizhang += $rubbish_weight[$kk];
                    }elseif($vv == '金属'){
                        $jinshu += $rubbish_weight[$kk];
                    }elseif($vv == '电器'){
                        $dianqi += $rubbish_weight[$kk];
                    }else{
                        //错误
                    }
                }
            }

        }
        $data['yifu'] = $yifu;
        $data['suliao'] = $suliao;
        $data['boli'] = $boli;
        $data['zhizhang'] = $zhizhang;
        $data['jinshu'] = $jinshu;
        $data['dianqi'] = $dianqi;
        return $data;
    }

    /**
     * 检测价格
     */
    private function checkprice($where,$rubbish_order,$keyword)
    {
        if(!in_array($keyword,['衣服','塑料','玻璃','纸张','金属','电器'])){
            $price = $rubbish_order
                ->alias('r')
                ->field('r.total_price')
                ->join('__USERS__ u','u.id = r.uid')
                ->join('__USERS__ e','e.id = r.eid','left')
                ->where($where)
                ->sum('total_price');
        }else{
            $list = $rubbish_order
                ->alias('r')
                ->field('r.*,u.user_nickname as u_name,e.user_login as e_name')
                ->join('__USERS__ u','u.id = r.uid')
                ->join('__USERS__ e','e.id = r.eid','left')
                ->where($where)
                ->select()->toArray();
            $price = 0;
            foreach ($list as $k=>$v){
                $rubbish_name = explode(',',$v['rubbish_name']);
                $rubbish_weight = explode(',',$v['rubbish_weight']);
                $rubbish_price = explode(',',$v['rubbish_price']);
                foreach ($rubbish_name as $kk=>$vv){
                    if($vv == $keyword){
                        $price += $rubbish_price[$kk]*$rubbish_weight[$kk];
                    }
                }
            }
        }
        return $price;
    }

}
