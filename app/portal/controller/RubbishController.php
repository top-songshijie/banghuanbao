<?php
/*
 * 换垃圾相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/11 16:35
 */
namespace app\portal\controller;

use app\portal\model\PromptModel;
use app\portal\model\RcsetModel;
use app\portal\model\ReturnRecordModel;
use app\portal\model\RubbishCommentModel;
use app\portal\model\RubbishOrderModel;
use app\portal\model\UseRecordModel;
use app\portal\model\UsersModel;
use app\portal\model\WxtsModel;
use app\portal\service\CtimeService;
use app\portal\service\UseraddressService;
use cmf\controller\HomeBaseController;
use think\Session;
use think\Db;

class RubbishController extends HomeBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        $this->check_staff();
        //获取banner
        $banner = Db::name('slide_item')->field('image,url')->where('slide_id',1)->select()->toArray();
        foreach ($banner as $k=>$v){
            $banner[$k]['image'] = cmf_get_image_url($v['image']);
        }
        //是否第一次进入
        $users = new UsersModel();
        $is_first = $users->where('id',$this->user_id)->value('is_first');
        //作弊提示
        $prompt = new PromptModel();
        $prompt_list = $prompt->order('id DESC')->column('content');
        //dump($is_first);
        $this->assign('banner',$banner);
        $this->assign('is_first',$is_first);
        $this->assign('prompt_list',$prompt_list);
        return $this->fetch();
    }
<<<<<<< HEAD

    //生成订单
    public function order(){
        $this->check_staff();
        //user_address表id
        $id = $this->request->param('id','');
        //温馨提示
        $wxts = new WxtsModel();
        $wxticontent = $wxts->where('id',1)->value('content');
        $session = Session::get('data');
=======
    //生成订单？
    public function order(){
        $where['user_id']=1;
        $session = Session::get('data');
        //Session::delete('data');
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $data = json_decode($session,true);
        $ids = isset($data['ids'])?$data['ids']:[];
        $select = json_encode($ids);
        $time = isset($data['time'])?$data['time']:'';
<<<<<<< HEAD
        if(!empty($id)){
            $where['id'] = $id;
        }else{
            $where = [
                'user_id' => $this->user_id,
                'is_default' => 1
            ];
        }
        $rubbish = Db::name('rubbish')->field('id,rubbish_name,rubbish_price')->select();
        $address = Db::name('user_address')->where($where)->find();
        $all_date = CtimeService::all_cate();

        $this->assign('all_date',json_encode($all_date));
=======
        $id = $this->request->param('id','');
        if(!empty($id)){//选择地址
            $where['id'] = $id;
        }else{//选择默认地址
            $where['is_default'] = 1;
        }
        $rubbish = Db::name('rubbish')->field('id,rubbish_name,rubbish_price')->select();
        $address = Db::name('user_address')->where($where)->find();
        $cometime = Db::name('cometime')->field('start_time,end_time')->find();
        //开始时间
        $start = strtotime($cometime['start_time']);
        //结束时间
        $end = strtotime($cometime['end_time']);
        //今天剩余的时间段
        if(time() < $end && time() > $start){
            for ($date = strtotime(date('H:00:00',strtotime('+1 hours')));$date < $end;$date+=3600){
                $interval[] = date('H:i:s',$date)."-".date('H:i:s',$date+3600);
            }
        }elseif(time() < $start){
            for ($date = $start;$date < $end;$date+=3600){
                $interval[] = date('H:i:s',$date)."-".date('H:i:s',$date+3600);
            }
        }else{
            $interval[] = "今天已经不能下单了";
        }
        //全天所有的时间段
        for ($f_data = $start;$f_data < $end;$f_data+=3600){
            $futureData[] = date('H:i:s',$f_data)."-".date('H:i:s',$f_data+3600);
        }
        $this->assign('interval',json_encode($interval));
        $this->assign('futurData',json_encode($futureData));
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $this->assign('address',$address);
        $this->assign('rubbish',$rubbish);
        $this->assign('ids',isset($data['ids'])?$data['ids']:[]);
        $this->assign('select',$select);
<<<<<<< HEAD
        $this->assign('wxticontent',$wxticontent);
        $this->assign('time',isset($time)?$time:'');
=======
        $this->assign('time',isset($time)?$time:'');
        //dump($time);
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        return $this->fetch();
    }
    /**
     * 订单确认
     */
    public function put_order()
    {
<<<<<<< HEAD
        $this->check_staff();
        $user_id=$this->user_id;
        $post=$this->request->param();
        $ids=isset($post['ids'])?$post['ids']:'';
=======
        $user_id=1;
        $post=$this->request->param();
        $ids=$post['ids'];
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $cometime=$post['time'];
        $village=$post['village'];
        $detail_address=$post['detail_address'];
        $name=$post['name'];
        $mobile=$post['tel'];
<<<<<<< HEAD
        if(empty($ids)){
            $this->apiResponse(0,'请选择垃圾种类','');
        }
        if(empty($cometime)){
            $this->apiResponse(0,'请选择上门时间','');
        }
        if(empty($village) or empty($detail_address) or empty($name) or empty($mobile)){
            $this->apiResponse(0,'请选择收货地址','');
        }
=======
        //$this->apiResponse(1,'ok',$data);
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        foreach ($ids as $k=>$v){
            $rubbish=Db::name('rubbish')
                ->field('rubbish_name,rubbish_price')
                ->where('id',$v)
                ->find();
           $r_name["$k"]=$rubbish['rubbish_name'];
           $r_price["$k"]=$rubbish['rubbish_price'];
        }
        $rubbish_name=implode(',',$r_name);
        $rubbish_price=implode(',',$r_price);
<<<<<<< HEAD
        $order_sn = cmf_get_order_sn();
        $data = [
            'uid' => $user_id,
            'order_sn' => $order_sn,
            'village' => ltrim($village,' '),
=======
        $data = [
            'uid' => $user_id,
            'order_sn' => cmf_get_order_sn(),
            'village' => $village,
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
            'detail_address' => $detail_address,
            'name' => $name,
            'mobile' => $mobile,
            'cometime' => $cometime,
            'rubbish_name' => $rubbish_name,
            'rubbish_price' => $rubbish_price,
            'status' => 0,
            'create_time' => date('Y-m-d H:i:s')
        ];
        $rubbish_order = new RubbishOrderModel();
        $res = $rubbish_order->insert($data);
        if($res){
            Session::delete('data');
<<<<<<< HEAD
            $notice = new NoticeController();
            $notice->userSend2($order_sn);
=======
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
            $this->apiResponse(1,'ok',1);
        }else{
            $this->apiResponse(0,'no',0);
        }
    }
    //获取用户地址列表
    public function get_address(){
<<<<<<< HEAD
        $this->check_staff();
        $id = $this->user_id;//用户id
=======
        $id=1;//用户id
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $address=Db::name('user_address')->where("user_id",$id)->select()->toArray();
        if (empty($address)){
            return $this->fetch('no_address');
        }
        $this->assign('address',$address);
        return $this->fetch();
    }
    //添加地址
    public function add_address(){
<<<<<<< HEAD
        $this->check_staff();
=======
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $address=Db::name('address')->column('village');
//        $this->apiResponse(1,'ok',$address);
        $this->assign('village',json_encode($address));
        return $this->fetch();
    }
    /**
     * 提交添加地址并设为默认
     */
    public function put_add_address()
    {
<<<<<<< HEAD
        $this->check_staff();
=======
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $village = $this->request->param('village');
        $detail_address =$this->request->param('detail_address');
        $name = $this->request->param('name');
        $mobile = $this->request->param('mobile');
        $res = UseraddressService::add($this->user_id,$village,$detail_address,$name,$mobile);
<<<<<<< HEAD
        if($res){
=======
       if($res){
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
           $this->apiResponse(1,'ok',1);
       }else{
           $this->apiResponse(0,'no',0);
       }
    }

    /**
     * 设为默认地址
     */
    public function set_default_address()
    {
<<<<<<< HEAD
        $this->check_staff();
=======
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $user_address_id = $this->request->param('id');//选中的地址id
        $res = UseraddressService::set_default($this->user_id,$user_address_id);
       if($res){
           $this->apiResponse(1,'ok',1);exit();
       }else{
           $this->apiResponse(0,'no',0);exit();
       }
    }
    //编辑地址
    public function edit_address(){
<<<<<<< HEAD
        $this->check_staff();
        $address=Db::name('address')->column('village');
        $id=$this->request->param('id');
        $user_address=Db::name('user_address')->where('id',$id)->find();

        $this->assign('address',$user_address);
=======
        $address=Db::name('address')->column('village');
        $id=$this->request->param('id');
        $address=Db::name('user_address')->where('id',$id)->find();
        $this->assign('address',$address);
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $this->assign('village',json_encode($address));
        return $this->fetch();
    }

    /**
     * 提交编辑地址
     */
    public function put_edit_address()
    {
<<<<<<< HEAD
        $this->check_staff();
=======
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $user_address_id = $this->request->param('id');
        $village =$this->request->param('village');
        $detail_address =$this->request->param('detail_address');
        $name = $this->request->param('name');
        $mobile =$this->request->param('mobile');
        $res = UseraddressService::edit($user_address_id,$this->user_id,$village,$detail_address,$name,$mobile);
        if($res){
            $this->apiResponse(1,'ok',1);
        }else{
            $this->apiResponse(0,'no',0);
        }
    }

    /**
     * 评价
     */
    public function comment()
    {
        $this->check_staff();
        $eid = 2;
        $rubbish_order_id = 1;
        $score = 3;
        $content = "第二次来了！";
        $rubbish_comment = new RubbishCommentModel();
        $res = $rubbish_comment->insert([
            'uid' => $this->user_id,
            'eid' => $eid,
            'rubbish_order_id' => $rubbish_order_id,
            'score' => $score,
            'content' => $content,
            'create_time' => date('Y-m-d H:i:s')
        ]);
        echo "<pre/>";
        print_r($res);
        die;
    }
    //删除地址
    public function delete(){
        $id=$this->request->param('id');
        $res=Db::name('user_address')->where('id',$id)->delete();
        if($res){
            $this->apiResponse(1,'ok',1);
        }else{
            $this->apiResponse(0,'no',0);
        }
    }

    public function session(){
        $post=$this->request->param();
        $data=json_encode($post);
//        Session::delete('data');
//        $this->apiResponse(1,"ok",Session::get());
        Session::set('data',$data);
        $this->apiResponse(1,"ok",1);exit();
    }

    //删除地址
    public function delete(){
        $this->check_staff();
        $id=$this->request->param('id');
        $res=Db::name('user_address')->where('id',$id)->delete();
        if($res){
            $this->apiResponse(1,'ok',1);
        }else{
            $this->apiResponse(0,'no',0);
        }
    }

    public function session()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            $post=$this->request->param();
            $data=json_encode($post);
            Session::set('data',$data);
            $this->apiResponse(1,"ok",1);
        }
    }

    /**
     * 改变is_first
     */
    public function update_first()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            $user = new UsersModel();
            $res = $user->where('id',$this->user_id)->setField('is_first',0);
            if($res){
                $this->apiResponse(1,'ok','');
            }
        }
    }

    /**
     * 接单操作
     */
    public function jiedan()
    {
        if($this->request->isAjax()){
            //rubbish_order表id
            $id = $this->request->param('id','','');
            $rubbish_order = new RubbishOrderModel();
            $res = $rubbish_order->where('id',$id)->data([
                'eid' => $this->user_id,
                'status' => 1,
                'jiedan_time' => date('Y-m-d H:i:s')
            ])->update();
            if($res){
                $notice = new NoticeController();
                $notice->WxSend32($id);
                $this->apiResponse(1,'ok','');
            }
        }
    }

    /**
     * 确认订单
     */
    public function confirm_rubbish_order()
    {
        if($this->request->isAjax()){
            $data = $this->request->param();
            $user = new UsersModel();
            $use_record = new UseRecordModel();
            $rubbish_order = new RubbishOrderModel();
            $status = $rubbish_order->where('id',$data['id'])->value('status');
            if($status != 1){
                $this->apiResponse(0,'订单已过期','');
            }
            //此订单用户
            $uid = $rubbish_order->where('id',$data['id'])->value('uid');
            $rubbish_price = $rubbish_order->where('id',$data['id'])->value('rubbish_price');
            $rubbish_price = explode(',',$rubbish_price);
            $total_price = 0;
            foreach ($data['data'] as $k=>$v){
                if(empty($v['value'])){
                    $this->apiResponse(0,'请将订单填充完整','');
                }
                $total_price += $v['value']*$rubbish_price[$k];
                $weight[] = $v['value'];
            }
            $rubbish_weight = implode(',',$weight);
            Db::startTrans();
            $data_error = false;
            $res = $rubbish_order->where('id',$data['id'])->data([
                'rubbish_weight' => $rubbish_weight,
                'status' => 2,
                'total_price' => $total_price
            ])->update();
            //加环保币
            $res_add1 = $user->where('id',$uid)->setInc('total_coin',$total_price);
            $res_add2 = $user->where('id',$uid)->setInc('coin',$total_price);
            //添加使用记录
            $res_use = $use_record->insert([
                'user_id' => $uid,
                'coin' => $total_price,
                'fuhao' => '+',
                'create_time' => date('Y-m-d H:i:s'),
                'content' => '换宝'
            ]);
            if(!$res_add1 or !$res_add2 or !$res or !$res_use){
                $data_error = true;
            }
            //返利
            $pid = $user->where('id',$uid)->value('pid');
            if(!empty($pid)){
                //比例
                $reset = new RcsetModel();
                $return_record = new ReturnRecordModel();
                $bili = $reset->where('id',2)->value('content');
                $return_price = $total_price*$bili;
                $user_nickname = $user->where('id',$uid)->value('user_nickname');
                $res_add_user = $user->where('id',$pid)->setInc('coin',$return_price);
                $res_add_user2 = $user->where('id',$pid)->setInc('total_coin',$return_price);
                $res_add_record = $return_record->insert([
                    'user_id' => $uid,
                    'coin' => $return_price,
                    'create_time' => date('Y-m-d H:i:s'),
                    'content' => $user_nickname."完成换宝订单",
                    'touid' => $pid
                ]);
                if(!$res_add_user or !$res_add_record or !$res_add_user2){
                    $data_error = true;
                }
            }
            if($data_error){
                Db::rollback();
                $this->apiResponse(0,'no','');
            }
            Db::commit();
            $this->apiResponse(1,'ok','');
        }
    }

}
