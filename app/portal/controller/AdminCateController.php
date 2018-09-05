<?php
/*
 * 分类相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/6 9:35
 */
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\CateModel;
use think\Db;

class AdminCateController extends AdminBaseController
{
    //列表
    public function index()
    {
        $cate = new CateModel();
        $list = $cate->order('sort ASC')->select();

        $this->assign('list',$list);
        return $this->fetch();
    }

    //删除
    public function delete()
    {
        $id = $this->request->param('id','','intval');//cate表id
        $cate = new CateModel();
        $res = $cate->delete($id);
        if(!$res){
            $this->error('删除失败！');
        }
        $this->success('删除成功！');
    }

    //编辑
    public function edit(){
        $id = $this->request->param('id','','intval');//cate表id
        $cate = new CateModel();
        $info = $cate->find($id)->toArray();

        $this->assign('info',$info);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $post = $this->request->param();
        $cate = new CateModel();
        $res = $cate->update($post);
        if(!$res){
            $this->error('修改失败！');
        }
        $this->success('更新成功！');
    }

    //添加
    public function add()
    {
        return $this->fetch();
    }

    //添加提交
    public function addPost()
    {
        $post = $this->request->param();
        $cate = new CateModel();
        $res = $cate->insert($post);
        if(!$res){
            $this->error('添加失败！');
        }
        $this->success('添加成功！');
    }

    //排序
    public function listorder()
    {
        $post = $this->request->param();
        $cate = new CateModel();
        $res = $cate->where('id',$post['id'])->setField('sort',$post['sort']);
    }

}
