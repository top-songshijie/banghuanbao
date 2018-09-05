<?php
/*
 * 废品价格相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/9 13:03
 */
namespace app\portal\controller;

use app\portal\model\RubbishModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminRpriceController extends AdminBaseController
{
    public function index()
    {
        $rubbish = new RubbishModel();
        $list = $rubbish->select();

        $this->assign('list',$list);
        return $this->fetch();
    }

    //编辑
    public function edit()
    {
        $id = $this->request->param('id','','intval');
        $rubbish = new RubbishModel();

        $info = $rubbish->find($id);
        $this->assign('info',$info);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $post = $this->request->param();
        $post['update_time'] = date('Y-m-d H:i:s');

        $rubbish = new RubbishModel();
        $res = $rubbish->update($post);
        if(!$res){
            $this->error('修改失败！');
        }
        $this->success('更新成功！');
    }

}