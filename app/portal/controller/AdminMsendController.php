<?php
/*
 * 手机短息提示
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/20 14:19
 */
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminMsendController extends AdminBaseController
{
    public function index()
    {
        $info = Db::name('mobile_send')->where('id',1)->find();

        $this->assign('info',$info);
        return $this->fetch();
    }

    public function index_post()
    {
        $is_send = $this->request->param('is_send','','intval');
        $res = Db::name('mobile_send')->data([
            'is_send' => $is_send,
            'update_time' => date('Y-m-d H:i:s')
        ])->where('id',1)->update();
        if($res){
           $this->success('设置成功');
        }
    }

}