<?php
/*
 * 个人中心相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/13 16:13
 */
namespace app\portal\controller;

use app\portal\model\AdviceModel;
use app\portal\model\CashRecordModel;
use app\portal\model\GetcashSetModel;
use app\portal\model\GoodsCommentModel;
use app\portal\model\GoodsOrderModel;
use app\portal\model\ReturnRecordModel;
use app\portal\model\RubbishCommentModel;
use app\portal\model\RubbishModel;
use app\portal\model\RubbishOrderModel;
use app\portal\model\UseRecordModel;
use app\portal\model\UsersModel;
use app\portal\service\GorderService;
use app\portal\service\RcommentService;
use app\portal\service\RorderService;
use cmf\controller\HomeBaseController;
use think\Db;
use think\Session;

class MineController extends HomeBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->users = new UsersModel();
        $this->user_id = Session::get('user_id');
        $this->type = $this->users->where('id',$this->user_id)->value('type');
    }

    public function index()
    {
        if($this->type == 1){
            //普通用户
            $data = GorderService::user_index($this->user_id);
            $this->assign('data',$data);
            return $this->fetch('user_index');
        }else{
            //员工
            //销毁登录前从微信获取的信息
            Session::delete('reg_data');
            $data = GorderService::employee_index($this->user_id);
            $this->assign('data',$data);
            return $this->fetch('staff_index');
        }
    }

    /**
     * 商城订单
     * $order_type = 0;待付款（会员）
     * $order_type = 1;待发货（会员）
     * $order_type = 2;配送中（会员）
     * $order_type = 3;已完成（会员）
     * $order_type = all;全部（会员）
     * $order_type = 1;待配送（员工）
     * $order_type = 2;配送中（员工）
     * $order_type = 3;已完成（员工）
     * $order_type = 4;待接受（员工）
     */
    public function shop_order_list()
    {
        $order_type = $this->request->param('order_type','1');
        $tab = $this->request->param('tab',0);
        if($this->type == 1){
            //普通用户
            //全部
            $data['quanbu'] = GorderService::user_order_list($this->user_id,'all');
            //待付款
            $data['daifukuan'] = GorderService::user_order_list($this->user_id,0);
            //待发货
            $data['daifahuo'] = GorderService::user_order_list($this->user_id,1);
            //配送中
            $data['peisongzhong'] = GorderService::user_order_list($this->user_id,2);
            //已完成
            $data['yiwancheng'] = GorderService::user_order_list($this->user_id,3);
            $signPackage = $this->getSignPackage();

            $this->assign('signPackage',$signPackage);
            $this->assign('data',$data);
            $this->assign('tab',$tab);
            return $this->fetch('user_shop_order_list');
        }else{
            //员工
            $data = GorderService::employee_order_list($this->user_id,$order_type);

            $this->assign('order_type',$order_type);
            $this->assign('data',$data);
            return $this->fetch('staff_shop_order_list');
        }
    }

    /**
     * 商城订单详情
     */
    public function shop_order_detail()
    {
        $order_id = $this->request->param('order_id','','intval');
        if($this->type == 1){
            //普通用户
            $goods_comment = new GoodsCommentModel();
            $data = GorderService::user_order_detail($order_id);
            foreach ($data['goods_order_detail'] as $k=>$v){
                $is_comment = $goods_comment->where(['user_id'=>$this->user_id,'goods_id'=>$v['goods_id']])->find();
                if($is_comment){
                    $data['goods_order_detail'][$k]['is_comment'] = 1;
                }else{
                    $data['goods_order_detail'][$k]['is_comment'] = 0;
                }
            }

            $this->assign('data',$data);
            return $this->fetch('user_shop_order_detail');
        }else{
            //员工
            //好像没有
        }
    }

    /**
     * 换宝订单列表
     * $order_type = 1;未完成（用户）
     * $order_type = 2;已完成（用户）
     * $order_type = 0;待接受（员工）
     * $order_type = 1;未完成（员工）
     * $order_type = 2;已完成（员工）
     */
    public function rubbish_order_list()
    {
        $order_type = $this->request->param('order_type','','intval');
        if($this->type == 1){
            //普通用户
            $data = RorderService::user_order_list($this->user_id,$order_type);
            $this->assign('order_type',$order_type);
            $this->assign('data',$data);
            return $this->fetch('user_rubbish_order_list');
        }else{
            //员工
            $data = RorderService::employee_order_list($this->user_id,$order_type);
            $this->assign('order_type',$order_type);
            $this->assign('data',$data);
            return $this->fetch('staff_rubbish_order_list');
        }
    }

    /**
     * 换宝订单详情
     */
    public function rubbish_order_detail()
    {
        $rubbish_order_id = $this->request->param('id','','intval');
        if($this->type == 1){
            //普通用户
            $data = RorderService::order_detail($rubbish_order_id);

            $this->assign('data',$data);
            return $this->fetch('rubbish_order_detail');
        }else{
            //员工
            $data = RorderService::order_detail($rubbish_order_id);

            $this->assign('data',$data);
            return $this->fetch('rubbish_order_detail');
        }
    }

    /**
     * 评价（只针对用户）
     */
    public function comment_list()
    {
        $this->check_staff();
        $list = RcommentService::rubbish_comment();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 评价订单（换宝）
     */
    public function huanbao_comment()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            $rubbish_comment = new RubbishCommentModel();
            $rubbish_order = new RubbishOrderModel();
            $post = $this->request->param();
            $eid = $rubbish_order->where('id',$post['order_id'])->value('eid');
            $rubbish_order_id = $post['order_id'];
            $score = $post['score'];
            $content = $post['content'];
            $res = $rubbish_comment->insert([
                'uid' => $this->user_id,
                'eid' => $eid,
                'rubbish_order_id' => $rubbish_order_id,
                'score' => $score,
                'content' => $content,
                'create_time' => date('Y-m-d H:i:s')
            ]);
            if($res){
                $this->apiResponse(1,'ok','');
            }
        }else{
            //rubbish_order表id
            $order_id = $this->request->param('id','','intval');
            $this->assign('order_id',$order_id);
            return $this->fetch();
        }
    }

    /**
     * 投诉建议
     */
    public function advice()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            $content = $this->request->param('content','');
            $advice = new AdviceModel();
            $res = $advice->insert([
                'user_id' => $this->user_id,
                'create_time' => date('Y-m-d H:i:s'),
                'content' => $content
            ]);
            if($res){
                $this->apiResponse(1,'ok','');
            }
        }else{
            return $this->fetch();
        }
    }

    /**
     * 环保钱包
     */
    public function wallet()
    {
        if($this->type == 1){
            //普通用户
            $user = new UsersModel();
            $getcash = new GetcashSetModel();
            $userinfo = $user->where('id',$this->user_id)->field('coin,total_coin')->find();
            $data['coin'] = $userinfo['coin'];
            $data['total_coin'] = $userinfo['total_coin'];
            $data['my_class_num'] = $user->where('pid',$this->user_id)->count();
            $data['bili'] = $getcash->where('id',1)->value('content');
            $data['low_price'] = $getcash->where('id',2)->value('content');

            $this->assign('data',$data);
        }else{
            //员工
            $user = new UsersModel();
            $getcash = new GetcashSetModel();
            $userinfo = $user->where('id',$this->user_id)->field('coin,total_coin')->find();
            $data['coin'] = $userinfo['coin'];
            $data['total_coin'] = $userinfo['total_coin'];
            $data['my_class_num'] = $user->where('pid',$this->user_id)->count();
            $data['bili'] = $getcash->where('id',1)->value('content');
            $data['low_price'] = $getcash->where('id',2)->value('content');

            $this->assign('data',$data);
        }
        return $this->fetch();
    }

    /**
     * 分享
     */
    public function share()
    {
        $signPackage = $this->getSignPackage();

        $this->assign('signPackage',$signPackage);
        $this->assign('user_id',$this->user_id);
        return $this->fetch('share');
    }

    /**
     * 返佣记录
     */
    public function return_record()
    {
        $return_record = new ReturnRecordModel();
        $list = $return_record
            ->field('coin,content')
            ->where('touid',$this->user_id)
            ->order('id DESC')
            ->select()
            ->toArray();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 提现记录
     */
    public function cash_record()
    {
        $cash_record = new CashRecordModel();
        $list = $cash_record
            ->where([
                'status' => 1,
                'user_id' => $this->user_id
            ])
            ->field('coin,substr(create_time,1,10) as create_time')
            ->select()
            ->toArray();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 使用记录
     */
    public function use_record()
    {
        $use_record = new UseRecordModel();
        $list = $use_record->where('user_id',$this->user_id)->field('coin,substr(create_time,1,10) as create_time,fuhao,content')->select()->toArray();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 我的推荐
     */
    public function myclass()
    {
        $user = new UsersModel();
        $list = $user->where('pid',$this->user_id)->field('avatar,user_nickname,create_time')->select()->toArray();
        foreach ($list as $k=>$v){
            $list[$k]['create_time'] = date('Y.m.d',strtotime($v['create_time']));
        }

        $this->assign('list',$list);
        return $this->fetch();
    }

}
