<?php
/*
 * 会员相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/9 15:42
 */
namespace app\portal\controller;

use app\portal\model\UserAddressModel;
use app\portal\model\UsersModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminUsersController extends AdminBaseController
{
    public function index()
    {
        $keyword = $this->request->param('keyword','');
        $start_time = $this->request->param('start_time','');
        $end_time = $this->request->param('end_time','');
        if(!empty($keyword)){
            $where['user_nickname'] = ['like',"%$keyword%"];
        }
        if(!empty($start_time) and !empty($end_time)){
            $where['create_time'] = [['>= time', $start_time], ['<= time', $end_time]];
        }
        $users = new UsersModel();
        $where['type'] = 1;
        $list = $users->where($where)->order('id DESC')->paginate(30);
        $page = $list->render();
        $count = $users->where($where)->count();

        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('keyword',isset($keyword)?$keyword:'');
        $this->assign('start_time',isset($start_time)?$start_time:'');
        $this->assign('end_time',isset($end_time)?$end_time:'');
        $this->assign('count', $count);
        return $this->fetch();
    }

    /**
     * 下线
     */
    public function last()
    {
        $uid = $this->request->param('id','','intval');
        $users = new UsersModel();
        $list = $users->where('pid',$uid)->paginate(30);
        $page = $list->render();

        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();

    }

    /**
     *  上线
     */
    public function up()
    {
        $uid = $this->request->param('id','','intval');
        $users = new UsersModel();
        $pid = $users->where('id',$uid)->value('pid');
        $info = $users->where('id',$pid)->find();
        $this->assign('info', $info);
        return $this->fetch();

    }

    /**
     * 地址信息
     */
    public function address()
    {
        $uid = $this->request->param('id','','intval');
        $user_address = new UserAddressModel();
        $list = $user_address->where('user_id',$uid)->select();

        $this->assign('list',$list);
        return $this->fetch();
    }

}