<?php
/*
 * 模版推送以及手机推送
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/17 17:34
 */
namespace app\portal\controller;

use app\portal\model\GoodsOrderModel;
use app\portal\model\RubbishOrderModel;
use app\portal\model\UsersModel;
use cmf\controller\NolimitController;
use think\Config;
use think\Db;

class NoticeController extends NolimitController
{
    /**
     * 新订单产生时给员工以及用户推送(商城)
     */
    public function userSend($order_sn)
    {
        $user = new UsersModel();
        $goods_order = new GoodsOrderModel();
        //给员工推送
        $is_send = Db::name('mobile_send')->where('id',1)->value('is_send');
        $address_id = $goods_order
            ->alias('o')
            ->join('__ADDRESS__ a','a.village = o.sh_village')
            ->where('o.order_sn',$order_sn)
            ->value('a.id');
        $list = $user->where('type',2)->select()->toArray();
        $uids = [];
        foreach ($list as $k=>$v){
            $villages = explode(',',$v['villages']);
            if(in_array($address_id,$villages)){
                array_push($uids,$v['id']);
            }
        }
        //给用户推送
        $this->WxSend2($order_sn,$uids);
        if(!empty($uids)){
            foreach ($uids as $k=>$v){
                $staffinfo = $user->find($v)->toArray();
                if($is_send == 1){
                    $this->mobileSend($staffinfo['mobile'],"【帮换宝】您负责地区有新订单生成，进入公众号马上抢单吧！");
                }
                $this->WxSend1($order_sn,$staffinfo['openid']);
            }
        }
    }

    /**
     * 新订单产生时给员工以及用户推送(换宝)
     */
    public function userSend2($order_sn)
    {
        $user = new UsersModel();
        $rubbish_order = new RubbishOrderModel();
        //给员工推送
        $is_send = Db::name('mobile_send')->where('id',1)->value('is_send');
        $address_id = $rubbish_order
            ->alias('o')
            ->join('__ADDRESS__ a','a.village = o.village')
            ->where('o.order_sn',$order_sn)
            ->value('a.id');
        $list = $user->where('type',2)->select()->toArray();
        $uids = [];
        foreach ($list as $k=>$v){
            $villages = explode(',',$v['villages']);
            if(in_array($address_id,$villages)){
                array_push($uids,$v['id']);
            }
        }
        //给用户推送
        $this->WxSend22($order_sn,$uids);
        if(!empty($uids)){
            foreach ($uids as $k=>$v){
                $staffinfo = $user->find($v)->toArray();
                if($is_send == 1){
                    $this->mobileSend($staffinfo['mobile'],"【帮换宝】您负责地区有新订单生成，进入公众号马上抢单吧！");
                }
                $this->WxSend12($order_sn,$staffinfo['openid']);
            }
        }
    }

    /**
     * 有新订单是给员工发送短信消息
     * @param $mobile
     * @param $code
     * @return mixed
     */
    public function mobileSend($mobile,$content){
        date_default_timezone_set('PRC');//设置时区
        $url 		= "http://www.ztsms.cn/sendNSms.do";//提交地址
        $username 	= Config::get('MB_USERNAME');//用户名
        $password 	= Config::get('MB_PASSWORD');//原密码
        $data = array(
            'content' 	=> $content,//短信内容
            'mobile' 	=> $mobile,//手机号码
            'productid' => '887361',//产品id
            'xh'		=> ''//小号
        );
        $isTranscoding = false;
        $timeout = 30;
        $data['content'] = $isTranscoding === true ? mb_convert_encoding($data['content'], "UTF-8") : $data['content'];
        $data['username']=$username;
        $data['tkey'] 	= date('YmdHis');
        $data['password'] = md5(md5($password) . $data['tkey']);
        $curl = curl_init();// 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS,  http_build_query($data)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        return $result;
    }

    /**
     * 有新订单时给员工推送消息(商城)
     * @param $openid
     * @return bool
     */
    public function WxSend1($order_sn,$openid)
    {
        $access_token = $this->getAccessToken();
        $data = array();
        $data['touser'] = $openid;
        $data['template_id'] = '5QglfTTvBoHiAb0xIoOfeVAlH-kUUgQtKvaBaVcWY8I';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
         $data['url'] = $protocol.$_SERVER['HTTP_HOST']."/index.php/Portal/Login/index";
        $data['data']['first']['value'] = '您负责的地区有新订单！';
        $data['data']['first']['color'] = '#06C';
        $data['data']['keyword1']['value'] = $order_sn;
        $data['data']['keyword1']['color'] = '#06C';
        $data['data']['keyword2']['value'] = date('Y-m-d H:i');
        $data['data']['keyword2']['color'] = '#06C';
        $data['data']['remark']['value'] = '请尽快前往公众号接单！';
        $data['data']['remark']['color'] = '#06C';
        $data = json_encode($data);
//        $data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
        if($this->post_by_curl($url,$data)){
            return true;
        }
    }

    /**
     * 有新订单时给员工推送消息(换宝)
     * @param $openid
     * @return bool
     */
    public function WxSend12($order_sn,$openid)
    {
        $access_token = $this->getAccessToken();
        $data = array();
        $data['touser'] = $openid;
        $data['template_id'] = '5QglfTTvBoHiAb0xIoOfeVAlH-kUUgQtKvaBaVcWY8I';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $data['url'] = $protocol.$_SERVER['HTTP_HOST']."/index.php/Portal/Login/index";
        $data['data']['first']['value'] = '您负责的地区有新订单！';
        $data['data']['first']['color'] = '#06C';
        $data['data']['keyword1']['value'] = $order_sn;
        $data['data']['keyword1']['color'] = '#06C';
        $data['data']['keyword2']['value'] = date('Y-m-d H:i');
        $data['data']['keyword2']['color'] = '#06C';
        $data['data']['remark']['value'] = '请尽快前往公众号接单！';
        $data['data']['remark']['color'] = '#06C';
        $data = json_encode($data);
//        $data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
        if($this->post_by_curl($url,$data)){
            return true;
        }
    }

    /**
     * 下单后给用户推送消息（商城）
     * @param $openid
     * @return bool
     */
    public function WxSend2($order_sn,$uids)
    {
        $goods_order = new GoodsOrderModel();
        $user = new UsersModel();
        $access_token = $this->getAccessToken();
        $order_info = $goods_order->field('user_id,total_price,freight_price,eid')->where('order_sn',$order_sn)->find()->toArray();
        $einfo = $user->where('id','in',$uids)->field('user_login,mobile')->select()->toArray();
        $str = '';
        foreach ($einfo as $k=>$v){
            $str .= ';员工姓名：'.$v['user_login']." 电话".$v['mobile'];
        }
        $data = array();
        $data['touser'] = $user->where('id',$order_info['user_id'])->value('openid');
        $data['template_id'] = 'coT3IfAiVn-6hY0rpqAhpEk8ziy3Wy3S1F7CN44i9nE';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
         $data['url'] = $protocol.$_SERVER['HTTP_HOST']."/index.php/Portal/Mine/index";
        $data['data']['first']['value'] = '您的订单已支付成功！';
        $data['data']['first']['color'] = '#06C';
        $data['data']['keyword1']['value'] = '帮换宝商城';
        $data['data']['keyword1']['color'] = '#06C';
        $data['data']['keyword2']['value'] = date('Y-m-d H:i');
        $data['data']['keyword2']['color'] = '#06C';
        $data['data']['keyword3']['value'] = $order_info['total_price'] + $order_info['freight_price'];
        $data['data']['keyword3']['color'] = '#06C';
        $data['data']['remark']['value'] = '已通知附近员工。'.trim($str,';');
        $data['data']['remark']['color'] = '#06C';
        $data = json_encode($data);
//        $data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
        if($this->post_by_curl($url,$data)){
            return true;
        }
    }

    /**
     * 下单后给用户推送消息（换宝）
     * @param $openid
     * @return bool
     */
    public function WxSend22($order_sn,$uids)
    {
        $rubbish_order = new RubbishOrderModel();
        $user = new UsersModel();
        $access_token = $this->getAccessToken();
        $order_info = $rubbish_order->field('uid')->where('order_sn',$order_sn)->find()->toArray();
        $str = '';
        $einfo = $user->where('id','in',$uids)->field('user_login,mobile')->select()->toArray();
        foreach ($einfo as $k=>$v){
            $str .= ';员工姓名：'.$v['user_login']." 电话".$v['mobile'];
        }
        $data = array();
        $data['touser'] = $user->where('id',$order_info['uid'])->value('openid');
        $data['template_id'] = 'coT3IfAiVn-6hY0rpqAhpEk8ziy3Wy3S1F7CN44i9nE';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $data['url'] = $protocol.$_SERVER['HTTP_HOST']."/index.php/Portal/Mine/index";
        $data['data']['first']['value'] = '您已下单成功！！';
        $data['data']['first']['color'] = '#06C';
        $data['data']['keyword1']['value'] = '帮换宝';
        $data['data']['keyword1']['color'] = '#06C';
        $data['data']['keyword2']['value'] = date('Y-m-d H:i');
        $data['data']['keyword2']['color'] = '#06C';
        $data['data']['keyword3']['value'] = '待确定';
        $data['data']['keyword3']['color'] = '#06C';
        $data['data']['remark']['value'] = '已通知附近员工。'.trim($str,';');
        $data['data']['remark']['color'] = '#06C';
        $data = json_encode($data);
//        $data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
        if($this->post_by_curl($url,$data)){
            return true;
        }
    }

    /**
     * 员工接单后给客户推送消息(商城)
     * @param $openid
     * @return bool
     */
    public function WxSend3($openid,$order_id)
    {
        $access_token = $this->getAccessToken();
        $goods_order = new GoodsOrderModel();
        $user = new UsersModel();
        $order_info = $goods_order->field('order_sn,eid')->where('id',$order_id)->find()->toArray();
        $user_info = $user->field('user_login,mobile')->where('id',$order_info['eid'])->find()->toArray();
        $data = array();
        $data['touser'] = $openid;
        $data['template_id'] = 'oO0zw-COiT-YEFQXfadxSb-h5r84vQICCu2nrq5rB6E';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
         $data['url'] = $protocol.$_SERVER['HTTP_HOST']."/index.php/Portal/Mine/index";
        $data['data']['first']['value'] = '您的订单已接收，正在发往目的地';
        $data['data']['first']['color'] = '#06C';
        $data['data']['keyword1']['value'] = $order_info['order_sn'];
        $data['data']['keyword1']['color'] = '#06C';
        $data['data']['keyword2']['value'] = $user_info['user_login'];
        $data['data']['keyword2']['color'] = '#06C';
        $data['data']['keyword3']['value'] = $user_info['mobile'];
        $data['data']['keyword3']['color'] = '#06C';
        $data['data']['remark']['value'] = '点击前往查看详情';
        $data['data']['remark']['color'] = '#06C';
        $data = json_encode($data);
//        $data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
        if($this->post_by_curl($url,$data)){
            return true;
        }
    }

    /**
     * 员工接单后给客户推送消息(换宝)
     * @param $openid
     * @return bool
     */
    public function WxSend32($order_id)
    {
        $access_token = $this->getAccessToken();
        $rubbish_order = new RubbishOrderModel();
        $user = new UsersModel();
        $order_info = $rubbish_order->field('order_sn,uid,eid')->where('id',$order_id)->find()->toArray();
        $user_info = $user->field('user_login,mobile')->where('id',$order_info['eid'])->find()->toArray();
        $data = array();
        $data['touser'] = $user->where('id',$order_info['uid'])->value('openid');
        $data['template_id'] = 'oO0zw-COiT-YEFQXfadxSb-h5r84vQICCu2nrq5rB6E';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $data['url'] = $protocol.$_SERVER['HTTP_HOST']."/index.php/Portal/Mine/index";
        $data['data']['first']['value'] = '您的订单已接收，正在前往接收';
        $data['data']['first']['color'] = '#06C';
        $data['data']['keyword1']['value'] = $order_info['order_sn'];
        $data['data']['keyword1']['color'] = '#06C';
        $data['data']['keyword2']['value'] = $user_info['user_login'];
        $data['data']['keyword2']['color'] = '#06C';
        $data['data']['keyword3']['value'] = $user_info['mobile'];
        $data['data']['keyword3']['color'] = '#06C';
        $data['data']['remark']['value'] = '点击前往查看详情';
        $data['data']['remark']['color'] = '#06C';
        $data = json_encode($data);
//        $data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
        if($this->post_by_curl($url,$data)){
            return true;
        }
    }

    /**
     * 订单完成给客户推送消息
     * @param $openid
     * @return bool
     */
    public function WxSend4($openid,$order_id)
    {
        $access_token = $this->getAccessToken();
        $goods_order = new GoodsOrderModel();
        $data = array();
        $order_info = $goods_order->field('order_sn')->where('id',$order_id)->find()->toArray();
        $data['touser'] = $openid;
        $data['template_id'] = '0Sb0BdockgSuagPdMvc6SPyqy7os3yqUQ9zepEfUw58';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
         $data['url'] = $protocol.$_SERVER['HTTP_HOST']."/index.php/Portal/Shop/index";
        $data['data']['first']['value'] = '您的订单已确认，交易完成';
        $data['data']['first']['color'] = '#06C';
        $data['data']['keyword1']['value'] = $order_info['order_sn'];
        $data['data']['keyword1']['color'] = '#06C';
        $data['data']['keyword2']['value'] = '帮换宝商城服务';
        $data['data']['keyword2']['color'] = '#06C';
        $data['data']['remark']['value'] = '感谢您的支持';
        $data['data']['remark']['color'] = '#06C';
        $data = json_encode($data);
//        $data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
        if($this->post_by_curl($url,$data)){
            return true;
        }
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    private function post_by_curl($url,$data)
    {
        $curl = curl_init();// 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址

        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS,  $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 500); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, true); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        if($result) {
            return $result;
        }
    }

}