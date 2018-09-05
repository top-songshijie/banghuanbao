<?php
/*
 * 返佣记录
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/10 13:29
 */
namespace app\portal\controller;

use app\portal\model\ReturnRecordModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminRrecordController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        if(!empty($keyword)){
            $where['u.user_nickname | e.user_nickname'] = ['like',"$keyword"];
        }
        $return_record = new ReturnRecordModel();
        $list = $return_record
            ->alias('r')
            ->field('r.*,u.user_nickname as u_name,e.user_nickname as e_name')
            ->join('__USERS__ u','u.id = r.user_id')
            ->join('__USERS__ e','e.id = r.touid')
            ->where($where)
            ->order('id DESC')
            ->paginate(30);
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        return $this->fetch();
    }

}