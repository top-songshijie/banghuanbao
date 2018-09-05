<?php
/*
 * 温馨提示
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/10 14:58
 */
namespace app\portal\controller;

use app\portal\model\WxtsModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminWxtsController extends AdminBaseController
{

    public function index()
    {
        $wxts = new WxtsModel();
        $info = $wxts->find(1);

        $this->assign('info',$info);
        return $this->fetch();
    }

    public function index_post()
    {
        $wxts = new WxtsModel();
        $post = $this->request->param();
        $res = $wxts->where('id',$post['id'])->data([
            'update_time' => date('Y-m-d H:i:s'),
            'content' => $post['content'],
        ])->update();
        if($res){
            $this->success('更新成功！');
        }
    }


}