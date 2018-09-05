<?php
/*
 * 上门时间相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/9 14:25
 */
namespace app\portal\controller;

use app\portal\model\CometimeModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminCometimeController extends AdminBaseController
{
    public function index()
    {
        $cometime = new CometimeModel();
        $info = $cometime->find(1);

        $this->assign('info',$info);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $id = $this->request->param('id','','intval');
        $start_time = $this->request->param('start_time','','intval');
        $end_time = $this->request->param('end_time','','intval');
        $cometime = new CometimeModel();
        if($start_time > 24 or $end_time > 24){
            $this->error('时间格式不正确！');
        }
        if($start_time > $end_time){
            $this->error('开始时间必须小于结束时间！');
        }

        $post['update_time'] = date('Y-m-d H:i:s');
        $res = $cometime->where('id',$id)->data([
            'start_time' => $start_time,
            'end_time' => $end_time,
            'update_time' => date('Y-m-d H:i:s')
        ])->update();
        if(!$res){
            $this->error('编辑失败！');
        }
        $this->success('更新成功！');
    }

}