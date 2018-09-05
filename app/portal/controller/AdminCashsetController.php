<?php
/*
 * 提现相关设置
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/11 15:06
 */
namespace app\portal\controller;

use app\portal\model\GetcashSetModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminCashsetController extends AdminBaseController
{
    public function index()
    {
        $getcash_set = new GetcashSetModel();
        $bili = $getcash_set->find(1)->toArray();
        $low = $getcash_set->find(2)->toArray();

        $this->assign('bili',$bili);
        $this->assign('low',$low);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $post = $this->request->param();
        $getcash_set = new GetcashSetModel();
        $res = $getcash_set->update([
            'id' => 1,
            'content' => $post['bili'],
            'update_time' => date('Y-m-d H:i:s')
        ]);
        $res2 = $getcash_set->update([
            'id' => 2,
            'content' => $post['low'],
            'update_time' => date('Y-m-d H:i:s')
        ]);
        if($res and $res2){
            $this->success('重置成功！');
        }
    }

}