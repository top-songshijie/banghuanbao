<?php
/*
 * 员工相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/9 15:55
 */
namespace app\portal\controller;

use app\portal\model\AddressModel;
use app\portal\model\UsersModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminEmployeeController extends AdminBaseController
{
    public function index()
    {
        $keyword = $this->request->param('keyword','');
        $start_time = $this->request->param('start_time','');
        $end_time = $this->request->param('end_time','');
        if(!empty($keyword)){
            $where['user_login'] = ['like',"%$keyword%"];
        }
        if(!empty($start_time) and !empty($end_time)){
            $where['create_time'] = [['>= time', $start_time], ['<= time', $end_time]];
        }
        $users = new UsersModel();
        $where['type'] = 2;
        $list = $users->where($where)->order('id DESC')->paginate(30);
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('keyword',isset($keyword)?$keyword:'');
        $this->assign('start_time',isset($start_time)?$start_time:'');
        $this->assign('end_time',isset($end_time)?$end_time:'');
        return $this->fetch();
    }
    //添加
    public function add()
    {
        $address = new AddressModel();
        $list = $address->select();

        $this->assign('list',$list);
        return $this->fetch();
    }

    //添加提交
    public function addPost()
    {
        $post = $this->request->param();
        $users = new UsersModel();
        if(cmf_password($post['password']) !== cmf_password($post['repassword'])){
            $this->error('两次输入密码不一致！');
        }
        $if_exist = $users->where('user_login',$post['user_login'])->find();
        if($if_exist){
            $this->error('此账号已存在！');
        }
        $res = $users->insert([
            'user_login' => $post['user_login'],
            'password' => cmf_password($post['password']),
            'create_time' => date('Y-m-d H:i:s'),
            'type' => 2,
            'villages' => implode(',',$post['village']),
            'mobile' => $post['mobile']
        ]);
        if(!$res){
            $this->error('添加失败');
        }
        $this->success('添加成功！');
    }

    //修改密码
    public function editpassword()
    {
        $id = $this->request->param('id','','intval');
        $users = new UsersModel();
        $info = $users->find($id);

        $this->assign('info',$info);
        return $this->fetch();
    }

    //修改密码提交
    public function editpasswordPost()
    {
        $post = $this->request->param();
        if(cmf_password($post['password']) !== cmf_password($post['repassword'])){
            $this->error('两次输入密码不一致！');
        }
        $users = new UsersModel();
        $res = $users->update([
            'id' => $post['id'],
            'password' => cmf_password($post['password']),
            'update_time' => date('Y-m-d H:i:s')
        ]);
        if(!$res){
            $this->error('修改失败！');
        }
        $this->success('密码已更新！');
    }

    //修改负责小区
    public function editvillages()
    {
        $id = $this->request->param('id','','intval');
        $users = new UsersModel();
        $villages = $users->where('id',$id)->value('villages');
        $village_ids = explode(',',$villages);
        $info = $users->find($id);
        $village = new AddressModel();
        $villages = $village->select()->toArray();
        $this->assign('info',$info);
        $this->assign('villages',$villages);
        $this->assign('village_ids',$village_ids);
        return $this->fetch();
    }
    //修改负责小区提交
    public function editvillagesPost()
    {
        $post = $this->request->param();
        $users = new UsersModel();
        $post['villages'] = implode(',',$post['villages']);
        $post['update_time'] = date('Y-m-d H:i:s');
        $res = $users->update($post);
        if(!$res){
            $this->error('修改失败！');
        }
        $this->success('已更新！');
    }
<<<<<<< HEAD

    /**
     * 下线
     */
    public function last()
    {
        $uid = $this->request->param('id','','intval');
        $users = new UsersModel();
        $list = $users->where('pid',$uid)->paginate(30);
        $page = $list->render();

        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();

    }

    /**
     *  上线
     */
    public function up()
    {
        $uid = $this->request->param('id','','intval');
        $users = new UsersModel();
        $pid = $users->where('id',$uid)->value('pid');
        $info = $users->where('id',$pid)->find();
        $this->assign('info', $info);
        return $this->fetch();

    }

=======
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
}