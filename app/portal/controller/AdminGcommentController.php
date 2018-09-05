<?php
/*
 * 商品评论相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/11 13:27
 */
namespace app\portal\controller;

use app\portal\model\GoodsCommentModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminGcommentController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        $start_time = $this->request->param('start_time','');
        $end_time = $this->request->param('end_time','');
        if(!empty($start_time) and !empty($end_time)){
            $where['gc.create_time'] = ['between',[$start_time,$end_time]];
        }
        if(!empty($keyword)){
            $where['g.goods_name'] = ['like',"%$keyword%"];
        }
        $goods_comment = new GoodsCommentModel();
        $list = $goods_comment
            ->field('gc.*,g.goods_name,gc.content as gc_content,u.user_nickname as u_name')
            ->alias('gc')
            ->join('__GOODS__ g','g.id = gc.goods_id')
            ->join('__USERS__ u','u.id = gc.user_id')
            ->where($where)
            ->order('gc.id DESC')
            ->paginate(30);
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('start_time',empty($start_time)?'':$start_time);
        $this->assign('end_time',empty($end_time)?'':$end_time);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id = $this->request->param('id','','intval');
        $goods_comment = new GoodsCommentModel();
        $res = $goods_comment->where('id',$id)->delete();
        if($res){
            $this->success('删除成功');
        }
    }

}