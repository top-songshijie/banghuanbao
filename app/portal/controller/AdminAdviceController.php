<?php
/*
 * 投诉建议
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/10 14:15
 */
namespace app\portal\controller;

use app\portal\model\AdviceModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminAdviceController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        if(!empty($keyword)){
            $where['u.user_nickname'] = ['like',"$keyword"];
        }
        $advice = new AdviceModel();
        $list = $advice
            ->alias('a')
            ->field('a.*,u.user_nickname')
            ->join('__USERS__ u','u.id = a.user_id')
            ->where($where)
            ->order('a.id DESC')
            ->paginate(30);
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        return $this->fetch();
    }

}