<?php
/*
 * 生成分享二维码
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/8 13:44
 */
namespace app\portal\controller;

use cmf\controller\NolimitController;
use think\Config;

class CodeController extends NolimitController
{
    //微信端生成带参数的二维码
    public function weixin_code($uid)
    {
        $ticket = $this->getTicket($uid);
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
        return $url;

    }

    //微信端生成带参数的二维码
    public function getTicket($uid)
    {
        require_once EXTEND_PATH."jssdk/jssdk.php";
        $jssdk = new \JSSDK(Config::get('WX_APPID'),Config::get('WX_APP_SECRET'));
        $access_token = $jssdk->getAccessToken();
        $qrcode =  '{
             "expire_seconds": 2592000,
             "action_name": "QR_STR_SCENE",
             "action_info": {
                 "scene": {
                     "scene_str": '.$uid.'
                 }
             }
         }';
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
        $result = $this->https_post($url,$qrcode);
        $jsoninfo = json_decode($result,true);
        $ticket = $jsoninfo['ticket'];
        return $ticket;

    }


    //post方法
    public function https_post($url,$data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}