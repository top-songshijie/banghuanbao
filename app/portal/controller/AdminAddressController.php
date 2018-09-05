<?php
/*
 * 收货地址相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/9 9:15
 */
namespace app\portal\controller;

use app\portal\model\AddressModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Loader;

class AdminAddressController extends AdminBaseController
{
    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword','');
        if(!empty($keyword)){
            $where['village'] = ['like',"%$keyword%"];
        }

        $address = new AddressModel();
        $list = $address->where($where)->order('id DESC')->paginate(20);
        $page = $list->render();

        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        return $this->fetch();
    }

    //删除
    public function delete()
    {
        $id = $this->request->param('id','','intval');
        $address = new AddressModel();
        $res = $address->delete($id);
        if(!res){
            $this->error('删除失败！');
        }
        $this->success('删除成功！');
    }

    //添加
    public function add()
    {
        return $this->fetch();
    }

    //添加提交
    public function addPost()
    {
        $village = $this->request->param('village','');
        $data = [
            'village' => $village,
            'update_time' => date('Y-m-d H:i:s'),
        ];
        $validate = Loader::validate('Address');
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $address = new AddressModel();
        $res = $address->insert($data);
        if(!$res){
            $this->error('添加失败！');
        }
        $this->success('添加成功！');
    }

    //编辑（颜色）
    public function edit()
    {
        $id = $this->request->param('id','','intval');
        $address = new AddressModel();
        $info = $address->find();

        $this->assign('info',$info);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $post = $this->request->param();
        $post['update_time'] = date('Y-m-d');
        $validate = Loader::validate('Address');
        if(!$validate->check($post)){
            $this->error($validate->getError());
        }
        $address = new AddressModel();
        $res = $address->update($post);
        if(!$res){
            $this->error('编辑失败!');
        }
        $this->success('保存成功！');
    }

}