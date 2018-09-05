<?php
/*
 * 限时时间
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/1 17:56
 */
namespace app\portal\controller;

use app\portal\model\XsdataModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminXsdateController extends AdminBaseController
{
    public function index()
    {
        $xsdate = new XsdataModel();
        $info = $xsdate->find(1);

        $this->assign('info',$info);
        return $this->fetch();
    }

    public function index_post()
    {
        $xsdate = new XsdataModel();
        $post = $this->request->param();

        $end_time = strtotime($post['end_date']);
        $show_date = date('m.d H:i',$end_time);
        $res = $xsdate->where('id',$post['id'])->data([
            'update_time' => date('Y-m-d H:i:s'),
            'end_time' => $end_time,
            'end_date' => $post['end_date'],
            'show_date' => $show_date
        ])->update();
        if($res){
            $this->success('更新成功！');
        }
    }

}