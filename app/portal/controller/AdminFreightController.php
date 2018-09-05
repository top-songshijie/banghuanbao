<?php
/*
 * 邮费相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/8 16:22
 */
namespace app\portal\controller;

use app\portal\model\FreightModel;
use cmf\controller\AdminBaseController;

class AdminFreightController extends AdminBaseController
{
    public function index()
    {
        $freight = new FreightModel();
        $duihuan = $freight->where('id',1)->value('freight_price');
        $putong = $freight->where('id',2)->value('freight_price');

        $this->assign('duihuan',$duihuan);
        $this->assign('putong',$putong);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $post = $this->request->param();
        $freight = new FreightModel();
        $res = $freight->update([
            'id' => 1,
            'freight_price' => $post['duihuan'],
            'update_time' => date('Y-m-d H:i:s')
        ]);
        $res2 = $freight->update([
            'id' => 2,
            'freight_price' => $post['putong'],
            'update_time' => date('Y-m-d H:i:s')
        ]);
        if($res and $res2){
            $this->success('重置成功！');
        }
    }

}