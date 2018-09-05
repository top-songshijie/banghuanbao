<?php
/*
 * 垃圾评价相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/10 13:21
 */
namespace app\portal\controller;

use app\portal\model\RubbishCommentModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminRcommentController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        $start_time = $this->request->param('start_time','');
        $end_time = $this->request->param('end_time','');
        $rubbish_comment = new RubbishCommentModel();
        if(!empty($start_time) and !empty($end_time)){
            $where['c.create_time'] = ['between',[$start_time,$end_time]];
        }
        if(!empty($keyword)){
            $where['e.user_login'] = ['like',"$keyword"];
        }
        $list = $rubbish_comment
            ->alias('c')
            ->field('c.*,u.user_nickname as u_name,e.user_login as e_name,o.order_sn')
            ->join('__USERS__ u','c.uid = u.id')
            ->join('__USERS__ e','c.eid = e.id')
            ->join('__RUBBISH_ORDER__ o','c.rubbish_order_id = o.id')
            ->where($where)
            ->order('c.id DESC')
            ->paginate(30);
        $page = $list->render();
        //五星
        $five = $rubbish_comment
            ->alias('c')
            ->field('c.*,u.user_nickname as u_name,e.user_login as e_name,o.order_sn')
            ->join('__USERS__ u','c.uid = u.id')
            ->join('__USERS__ e','c.eid = e.id')
            ->join('__RUBBISH_ORDER__ o','c.rubbish_order_id = o.id')
            ->where($where)
            ->where('c.score',5)
            ->count();
        //四星
        $four = $rubbish_comment
            ->alias('c')
            ->field('c.*,u.user_nickname as u_name,e.user_login as e_name,o.order_sn')
            ->join('__USERS__ u','c.uid = u.id')
            ->join('__USERS__ e','c.eid = e.id')
            ->join('__RUBBISH_ORDER__ o','c.rubbish_order_id = o.id')
            ->where($where)
            ->where('c.score',4)
            ->count();
        //三星
        $three = $rubbish_comment
            ->alias('c')
            ->field('c.*,u.user_nickname as u_name,e.user_login as e_name,o.order_sn')
            ->join('__USERS__ u','c.uid = u.id')
            ->join('__USERS__ e','c.eid = e.id')
            ->join('__RUBBISH_ORDER__ o','c.rubbish_order_id = o.id')
            ->where($where)
            ->where('c.score',3)
            ->count();
        //二星
        $two = $rubbish_comment
            ->alias('c')
            ->field('c.*,u.user_nickname as u_name,e.user_login as e_name,o.order_sn')
            ->join('__USERS__ u','c.uid = u.id')
            ->join('__USERS__ e','c.eid = e.id')
            ->join('__RUBBISH_ORDER__ o','c.rubbish_order_id = o.id')
            ->where($where)
            ->where('c.score',2)
            ->count();
        //一星
        $one = $rubbish_comment
            ->alias('c')
            ->field('c.*,u.user_nickname as u_name,e.user_login as e_name,o.order_sn')
            ->join('__USERS__ u','c.uid = u.id')
            ->join('__USERS__ e','c.eid = e.id')
            ->join('__RUBBISH_ORDER__ o','c.rubbish_order_id = o.id')
            ->where($where)
            ->where('c.score',1)
            ->count();
        //0星
        $zero = $rubbish_comment
            ->alias('c')
            ->field('c.*,u.user_nickname as u_name,e.user_login as e_name,o.order_sn')
            ->join('__USERS__ u','c.uid = u.id')
            ->join('__USERS__ e','c.eid = e.id')
            ->join('__RUBBISH_ORDER__ o','c.rubbish_order_id = o.id')
            ->where($where)
            ->where('c.score',0)
            ->count();


        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('start_time',empty($start_time)?'':$start_time);
        $this->assign('end_time',empty($end_time)?'':$end_time);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        $this->assign('five',$five);
        $this->assign('four',$four);
        $this->assign('three',$three);
        $this->assign('two',$two);
        $this->assign('one',$one);
        $this->assign('zero',$zero);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id = $this->request->param('id','','intval');
        $rubbish_comment = new RubbishCommentModel();
        $res = $rubbish_comment->where('id',$id)->delete();
        if($res){
            $this->success('删除成功');
        }
    }

}