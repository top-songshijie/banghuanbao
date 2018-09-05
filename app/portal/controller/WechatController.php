<?php
/*
 * 微信相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/16 9:57
 */
namespace app\portal\controller;

use cmf\controller\NolimitController;
use think\Cache;
use think\Db;


class WechatController extends NolimitController
{
    function _initialize() {
        parent::_initialize();
        date_default_timezone_set("Asia/Shanghai");
        define("TOKEN", "banghuanbao");
        /**
         * 如果有"echostr"字段，说明是一个URL验证请求，
         * 否则是微信用户发过来的信息
         */
        if (isset($_GET["echostr"])){
            $this->valid();
        }else {
            $this->responseMsg();
        }
    }

    /**
     * 用于微信公众号里填写的URL的验证，
     * 如果合格则直接将"echostr"字段原样返回
     */
    public function valid() {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    /**
     * 用于验证是否是微信服务器发来的消息
     * @return bool
     */
    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature){
            return true;
        }else {
            return false;
        }
    }

    /**
     * 响应用户发来的消息
     */
    public function responseMsg() {
        //获取post过来的数据，它一个XML格式的数据
//        $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"])?$GLOBALS["HTTP_RAW_POST_DATA"]:'';
        $postStr = file_get_contents("php://input");
        if (!empty($postStr)){
            //将XML数据解析为一个对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            $FROMUSERNAME = trim($postObj->FromUserName);
            //消息类型分离
            switch($RX_TYPE){
                case "event":
                    $result = $this->receiveEvent($postObj,$FROMUSERNAME);
                    break;
                case "text":
                    $content = 'Hello,你好啊！';
                    $result = $this->transmitText($postObj, $content);
                    break;
                default:
                    $result = "unknow msg type:".$RX_TYPE;
                    break;
            }
            echo $result;
            exit;
        }else{
            echo "";
            exit;
        }
    }

    /**
     * 接收事件消息
     */
    private function receiveEvent($object,$FROMUSERNAME) {
        switch ($object->Event){
            //关注公众号事件
            case "subscribe":
                $openid = $FROMUSERNAME;//新用户的openid
                $pid = substr($object->EventKey,8);//邀请用户的uid
                //查询是否注册
                $is_login = Db::name('users')->where('openid',$openid)->find();
                //没注册进行注册，绑定上下级
                if(empty($is_login) and !empty($pid)){
                    Db::name('users')->insert([
                        'create_time' => date('Y-m-d H:i:s'),
                        'openid' => $openid,
                        'type' => 0,
                        'coin' => 0,
                        'is_first' => 1,
                        'pid' => $pid,
                        'total_coin' => 0,
                        'user_status' => 0
                    ]);
                }
                $content = "您好，欢迎关注帮换宝";
                $result = $this->transmitText($object, $content);
                break;
            case "CLICK";
                //点击逻辑
                break;
            default:
                break;
        }

        return $result;
    }

    /**
     * 回复文本消息
     */
    private function transmitText($object, $content) {
        $xmlTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime><![CDATA[%s]]></CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                   </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

}