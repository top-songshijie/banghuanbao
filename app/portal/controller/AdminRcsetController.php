<?php
/*
 * 返佣比例设置
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/8 15:52
 */
namespace app\portal\controller;

use app\portal\model\RcsetModel;
use cmf\controller\AdminBaseController;

class AdminRcsetController extends AdminBaseController
{
    public function index()
    {
        $rcset = new RcsetModel();
        $shop = $rcset->where('id',1)->value('content');
        $rubbish = $rcset->where('id',2)->value('content');

        $this->assign('shop',$shop);
        $this->assign('rubbish',$rubbish);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $post = $this->request->param();
        $rcset = new RcsetModel();
        $res = $rcset->update([
            'id' => 1,
            'content' => $post['shop'],
            'update_time' => date('Y-m-d H:i:s')
        ]);
        $res2 = $rcset->update([
            'id' => 2,
            'content' => $post['rubbish'],
            'update_time' => date('Y-m-d H:i:s')
        ]);
        if($res and $res2){
            $this->success('重置成功！');
        }
    }

}