<?php
/*
 * 使用记录
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/10 14:00
 */
namespace app\portal\controller;

use app\portal\model\UseRecordModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminUrecordController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        if(!empty($keyword)){
            $where['u.user_nickname'] = ['like',"%$keyword%"];
        }
        $use_record = new UseRecordModel();
        $list = $use_record
            ->alias('r')
            ->field('r.*,u.user_nickname')
            ->join('__USERS__ u','u.id = r.user_id')
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