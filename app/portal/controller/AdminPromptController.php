<?php
/*
 * 作弊提示相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/9 13:47
 */
namespace app\portal\controller;

use app\portal\model\PromptModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminPromptController extends AdminBaseController
{
    public function index()
    {
        $prompt = new PromptModel();
        $list = $prompt->order('id DESC')->select();

        $this->assign('list',$list);
        return $this->fetch();
    }

    //删除
    public function delete()
    {
        $id = $this->request->param('id','','intval');
        $prompt = new PromptModel();
        $res = $prompt->where('id',$id)->delete();
        if(!$res){
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
        $data = [
            'content' => $this->request->param('content'),
            'update_time' => date('Y-m-d H:i:s')
        ];
        $prompt = new PromptModel();
        $res = $prompt->insert($data);
        if(!$res){
            $this->error('添加失败！');
        }
        $this->success('恭喜您，作弊成功！');
    }

}