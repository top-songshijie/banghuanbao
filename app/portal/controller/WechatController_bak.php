<?php
/*
 * 微信相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/16 9:57
 */
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Config;
use think\Db;
use think\Session;

class WechatController extends HomeBaseController
{
    /**
     * 检查用户登录
     */
    protected function check_login()
    {
        $session_user = Session::get('user');
        if (empty($session_user)) {
            $code = $this->request->param('code','','string');
            //通过code换取网页授权access_token和openid
            $openidUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" .
                Config::get('WX_APPID') . "&secret=" . Config::get('WX_APP_SECRET') . "&code=" .
                $code . "&grant_type=authorization_code";
            $openidData = json_decode(file_get_contents($openidUrl), true);
            isset($openidData['access_token'])? $access_token = $openidData['access_token']:$access_token='';
            if($access_token){
                // 通过access_token和openid拉取用户信息
                $userinfoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $openidData['access_token']
                    . "&openid=" . $openidData['openid'] . "&lang=zh_CN";
                $userinfoData = json_decode(file_get_contents($userinfoUrl), true);
            }
            if ($userinfoData['openid'] != '') {
                //注册或者登录
                $Model = Db::name('users');
                $user = $Model->where('openid',$userinfoData['openid'])->find();
                if ($user) {
                    $res = $Model->where('openid',$userinfoData['openid'])->update([
                        'last_login_time' => date('Y-m-d H:i:s'),
                        'last_login_ip' => get_client_ip(0, true)
                    ]);
                    Session::set('user_id',$user['id']);
                } else {
                    $data = [
                        'openid' => $userinfoData['openid'],
                        'avatar' => $userinfoData['headimgurl'],
                        'user_nickname' => $userinfoData['nickname'],
                        'sex' => $userinfoData['sex'],
                        'create_time' => date('Y-m-d H:i:s'),
                        'last_login_time' => date('Y-m-d H:i:s'),
                        'last_login_ip' => get_client_ip(0, true)
                    ];
                    $userid = $Model->insertGetId($data);
                    Session::set('user_id',$userid);
                }
            } else {
                $url = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                header('Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=' .
                    Config::get('WX_APPID') . '&redirect_uri=' . $url .
                    '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect');
            }
        }
    }

}