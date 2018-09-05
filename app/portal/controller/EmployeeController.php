<?php
/**
 * Created by PhpStorm.
 * User: ruidiudiu
 * Date: 2018/7/23
 * Time: 17:08
 */

namespace app\portal\controller;


use cmf\controller\HomeBaseController;
use think\Db;

class EmployeeController extends HomeBaseController
{
    //员工登录
    public function login(){
        return $this->fetch();
    }
    //个人中心
    public function my(){
        $hbcount=Db::name('rubbish_order')->where('status',0)->count();
        $uncount=Db::name('rubbish_order')->where('status',1)->count();
        $donecount=Db::name('rubbish_order')->where('status',2)->count();
        $this->assign('hb',$hbcount);
        $this->assign('un',$uncount);
        $this->assign('done',$donecount);
        return $this->fetch();
    }
    //待接收
    public function hbOrder(){
        $hbOrder=Db::name('rubbish_order')
            ->where('status',0)
            ->order('cometime','asc')
            ->select();
        $this->assign('order',$hbOrder);
        return $this->fetch();
    }
    //未完成
    public function unOrder(){
        $eid=3;
        $unOrder=Db::name('rubbish_order')
            ->where(['status'=>1,'eid'=>$eid])
            ->order('cometime','asc')
            ->select();
        $this->assign('unorder',$unOrder);
        return $this->fetch();
    }
    //未完成详情
    public function unOrder_details(){
        $id=$this->request->param('id');
        $unOrder=Db::name('rubbish_order')
            ->where(['id'=>$id,'status'=>1])
            ->find();
        $rubbish=array();
        $name=explode(',',$unOrder['rubbish_name']);
        $price=explode(',',$unOrder['rubbish_price']);
        foreach ($name as $k=>$v){
            $rubbish[$k]['name']=$v;
            $rubbish[$k]['price']=$price[$k];
        }
        //dump($rubbish);
        //$rubbish=Db::name('rubbish')->field('id,rubbish_name,rubbish_price')->select();
        $this->assign('rubbish',$rubbish);
        $this->assign('unorder',$unOrder);
        return $this->fetch();
    }
    //已完成


    public function doneOrder(){
        $eid=3;
        $doneOrder=Db::name('rubbish_order')
            ->where(['status'=>2,'eid'=>$eid])
            ->order('confirm_time','desc')
            ->select();
        $this->assign('doneorder',$doneOrder);
        return $this->fetch();
    }
    //已完成订单详情
    public function doneorder_details(){
        $id=$this->request->param('id');
        $rubbish_order=Db::name('rubbish_order')->where(['id'=>$id,'status'=>2])->find();
        //dump($rubbish_order);
        $rubbish=array();
        $name=explode(',',$rubbish_order['rubbish_name']);
        $price=explode(',',$rubbish_order['rubbish_price']);
        $weight=explode(',',$rubbish_order['rubbish_weight']);
        foreach ($name as $k=>$v){
            $rubbish[$k]['name']=$v;
            $rubbish[$k]['price']=$price[$k];
            $rubbish[$k]['weight']= $weight[$k];
        }
        //dump($rubbish);
        $this->assign('rubbish',$rubbish);
        $this->assign('order',$rubbish_order);
        return $this->fetch();
    }
    //我的钱包
    public function wallet(){

        return $this->fetch();
    }
    //我的推荐
    public function myRecommend(){

        return $this->fetch();
    }
    //接单
    public function acceptOrder(){
        $id=$this->request->param('id');
        $eid=3;
        $res=Db::name('rubbish_order')->where('id',$id)->update(['status'=>1,'eid'=>$eid]);
        if($res){
            $this->apiResponse(1,'ok',1);
        }else{
            $this->apiResponse(2,'no',0);
        }
    }
    //确认订单
    public function confirmOrder(){
        $id=$this->request->param('id');
        $data=$this->request->param('weight');
        $price=$this->request->param('price');
        $weight=ltrim($data,',');
        $total_price=null;
        $rubbish_price=explode(',',$price);
        $rubbish_weight=explode(',',$weight);
        for ($i=0;$i<count($rubbish_price);$i++){
            if (!empty($rubbish_weight[$i])){
                $total_price+=$rubbish_price[$i]*$rubbish_weight[$i];
            }else{
                $this->apiResponse(2,'no',2);
            }
        }
        $order=Db::name('rubbish_order')->where('id',$id)->find();
        $user_id=$order['uid'];
        Db::startTrans();
        $res=Db::name('rubbish_order')
            ->where('id',$id)
            ->update([
                'rubbish_weight'=>$weight,
                'total_price'=>$total_price,
                'status'=>2,
                'confirm_time'=>date('Y:m:d H:i:s')
            ]);
        $res1=Db::name('users')->where('id',$user_id)->setInc('coin',$total_price);
        if ($res && $res1){
            Db::commit();
            $this->apiResponse(1,'ok',1);
        }else{
            Db::rollback();
            $this->apiResponse(0,'no',0);
        }

    }

    //待接收
    public function staff_order(){

        return $this->fetch();
    }
    //带配送
    public function staff_nusentout(){

        return $this->fetch();
    }
    //配送中
    public function staff_sentout(){

        return $this->fetch();
    }
    //已完成
    public function staff_done_order(){

        return $this->fetch();
    }
}