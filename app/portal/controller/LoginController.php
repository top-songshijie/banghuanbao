<?php
/*
 * 员工端登录
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/4 9:22
 */
namespace app\portal\controller;

use app\portal\model\UsersModel;
use cmf\controller\NolimitController;
use think\Db;
use think\Config;
use think\Session;

class LoginController extends NolimitController
{
    public function index()
    {
        $this->getUserinfo();
        $openid = Session::get('reg_data.openid');
        $user = new UsersModel();
        $userinfo = $user->where('openid',$openid)->find();
        if($userinfo['type'] == 2 and !empty($userinfo['user_login']) and !empty($userinfo['password'])){
            Session::set('user_id',$userinfo['id']);
            $this->redirect('Portal/Mine/index');
        }
        return $this->fetch();
    }

    /**
     * 登录验证
     */
    public function login_post()
    {
        if($this->request->isAjax()){
            $user = new UsersModel();
            $post = $this->request->param();
            $user_login = $post['user_login'];
            $password = $post['password'];
            $info = $user->where([
                'user_login' => $user_login,
                'password' => cmf_password($password)
            ])->find();
            if(!$info){
                $this->apiResponse(0,'账号或密码错误不正确','');
            }

            if($info['openid']){
                //已绑定，直接登录
                //查询用户是否是员工
                $type = $user->where('openid',$info['openid'])->value('type');
                if($type == 1){
                    $this->apiResponse(0,'您已注册为会员，无法登陆员工端','');
                }elseif($type == 2){
                    //已通过openid注册，直接登录
                    $user->where('openid',$info['openid'])->update([
                        'last_login_time' => date('Y-m-d H:i:s'),
                        'last_login_ip' => get_client_ip(0, true),
                    ]);
                    Session::set('user_id',$info['id']);
                    if(Session::get('user_id')){
                        $this->apiResponse(1,'登录成功,正在跳转','');
                    }
                }
            }else{
                //未绑定
                $userinfoData = Session::get('reg_data');
                $userinfo = $user->where('openid',$userinfoData['openid'])->find();
                if(empty($userinfo)){
                    //未通过openid注册，绑定后在登录
                    $this->check_login($info['id']);
                    if(Session::get('user_id')){
                        $this->apiResponse(1,'您已成功绑定为帮换宝员工','');
                    }
                }else{
                    //已通过openid注册
                    $data = [
                        'openid' => $userinfoData['openid'],
                        'avatar' => $userinfoData['avatar'],
                        'user_nickname' => $userinfoData['user_nickname'],
                        'last_login_time' => date('Y-m-d H:i:s'),
                        'last_login_ip' => get_client_ip(0, true),
                        'pid' => $userinfo['pid']
                    ];
                    $user->where('id',$userinfo['id'])->delete();
                    $user->where('id',$info['id'])->data($data)->update();
                    Session::set('user_id',$info['id']);
                }
            }
        }
    }

    /**
     * 获取用户信息
     */
    private function getUserinfo()
    {
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
        if (isset($userinfoData) and isset($userinfoData['openid'])) {
            $reg_data = [
                'openid' => $userinfoData['openid'],
                'avatar' => $userinfoData['headimgurl'],
                'user_nickname' => $userinfoData['nickname'],
            ];
            Session::set('reg_data',$reg_data);
        } else {
            $url = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            header('Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=' .
                Config::get('WX_APPID') . '&redirect_uri=' . $url .
                '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect');
        }
    }

    /**
     * 检查员工是否已通过openid注册
     */
    protected function check_login($uid)
    {
        $userinfoData = Session::get('reg_data');
        //查询此openid是否已注册
        $is_zhuce = Db::name('users')->where('openid',$userinfoData['openid'])->find();
        if($is_zhuce){
            $this->apiResponse(0,'该账号已是普通会员，无法再绑定员工账号','');
        }
        $data = [
            'openid' => $userinfoData['openid'],
            'avatar' => $userinfoData['avatar'],
            'user_nickname' => $userinfoData['user_nickname'],
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => get_client_ip(0, true)
        ];
        if(empty($userinfoData['openid'])){
            $this->apiResponse(0,'您未授权，绑定失败','');
        }
        Db::name('users')->where('id',$uid)->data($data)->update();
        Session::set('user_id',$uid);
    }

}