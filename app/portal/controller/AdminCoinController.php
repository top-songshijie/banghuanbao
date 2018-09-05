<?php
/*
 * 环保币相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/11 14:15
 */
namespace app\portal\controller;

use app\portal\model\CashRecordModel;
use app\portal\model\UsersModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminCoinController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        $start_time = $this->request->param('start_time','');
        $end_time = $this->request->param('end_time','');
        if(!empty($start_time) and !empty($end_time)){
            $where['c.create_time'] = ['between time',[$start_time,$end_time]];
        }
        if(!empty($keyword)){
            $where['u.user_nickname'] = trim($keyword);
        }

        $cash_record = new CashRecordModel();
        $list = $cash_record
            ->field('u.user_nickname,c.*')
            ->alias('c')
            ->join('__USERS__ u','c.user_id = u.id')
            ->where($where)
            ->order('c.id DESC')
            ->paginate(20);
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        $this->assign('start_time',empty($start_time)?'':$start_time);
        $this->assign('end_time',empty($end_time)?'':$end_time);
        return $this->fetch();
    }

    /**
     *  同意提现
     */
    public function agree()
    {
        $id = $this->request->param('id','','intval');//cash_record表id
        require_once EXTEND_PATH . 'WxminipayAPI/WxPay.Merch.php';
        $MerchPay = new \MerchPay();
        $cash_record = new CashRecordModel();
        $user = new UsersModel();
        $cash_record_info = $cash_record->find($id)->toArray();
        if($cash_record_info['status'] != 0){
            $this->error('非法操作');
        }
        $userinfo = $user->find($cash_record_info['user_id'])->toArray();
        $result = $MerchPay->pay($userinfo['openid'], $cash_record_info['order_sn'], $cash_record_info['coin'], '余额提现');
        if ($result['result_code'] != 'SUCCESS') {
            $this->error('商户余额不足');
        }
        $res = $cash_record->where('id',$id)->data([
            'status' => 1,
            'pay_time' => date('Y-m-d H:i:s')
        ])->update();
        if($res){
            $this->success('已提现到用户的微信钱包');
        }
    }

    /**
     *  拒绝提现
     */
    public function refuse()
    {
        $id = $this->request->param('id','','intval');//cash_record表id
        $cash_record = new CashRecordModel();
        $user = new UsersModel();
        $cash_record_info = $cash_record->find($id)->toArray();
        if($cash_record_info['status'] != 0){
            $this->error('非法操作');
        }
        Db::startTrans();
        $res = $user->where('id',$cash_record_info['user_id'])->setInc('coin',$cash_record_info['coin']);
        $res2 = $cash_record->where('id',$id)->data([
            'status' => 2,
            'pay_time' => date('Y-m-d H:i:s')
        ])->update();
        if(!$res or !$res2){
            Db::rollback();
            $this->error('网络延迟，请稍后重试');
        }
        Db::commit();
        $this->success('操作成功');
    }

}