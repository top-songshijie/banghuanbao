<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use app\portal\model\UsersModel;
use think\Db;
use app\admin\model\ThemeModel;
use think\Session;
use think\View;
use think\Config;

class HomeBaseController extends BaseController
{

    public function _initialize()
    {
        if(!cmf_is_wechat()){
            echo "请在微信端登录";die();
        }
        $this->check_login();
        // 监听home_init
        hook('home_init');
        parent::_initialize();
        $siteInfo = cmf_get_site_info();
        View::share('site_info', $siteInfo);
    }

    /**
     * 检查用户登录
     */
    protected function check_login()
    {
//        echo "<pre/>";
//        print_r($this->getAccessToken());
//        die;
//        Session::delete('user_id');die();
        $session_user = Session::get('user_id');
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
            if (isset($userinfoData) and isset($userinfoData['openid'])) {
                //注册或者登录
                $Model = Db::name('users');
                $user = $Model->where('openid',$userinfoData['openid'])->find();
                if ($user) {
                    //查询信息是否完整
                    if(empty($user['avatar'])){
                        $Model->where('openid',$userinfoData['openid'])->update([
                            'type' => 1,
                            'avatar' => $userinfoData['headimgurl'],
                            'user_nickname' => $userinfoData['nickname'],
                            'last_login_time' => date('Y-m-d H:i:s'),
                            'last_login_ip' => get_client_ip(0, true)
                        ]);
                        Session::set('user_id',$user['id']);
                    }else{
                        $Model->where('openid',$userinfoData['openid'])->update([
                            'last_login_time' => date('Y-m-d H:i:s'),
                            'last_login_ip' => get_client_ip(0, true)
                        ]);
                        Session::set('user_id',$user['id']);
                    }
                } else {
                    $data = [
                        'type' => 1,
                        'openid' => $userinfoData['openid'],
                        'avatar' => $userinfoData['headimgurl'],
                        'user_nickname' => $userinfoData['nickname'],
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

    public function _initializeView()
    {
        $cmfThemePath    = config('cmf_theme_path');
        $cmfDefaultTheme = cmf_get_current_theme();

        $themePath = "{$cmfThemePath}{$cmfDefaultTheme}";

        $root = cmf_get_root();
        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }

        $viewReplaceStr = array_merge(config('view_replace_str'), $viewReplaceStr);
        config('template.view_base', "{$themePath}/");
        config('view_replace_str', $viewReplaceStr);

        $themeErrorTmpl = "{$themePath}/error.html";
        if (file_exists_case($themeErrorTmpl)) {
            config('dispatch_error_tmpl', $themeErrorTmpl);
        }

        $themeSuccessTmpl = "{$themePath}/success.html";
        if (file_exists_case($themeSuccessTmpl)) {
            config('dispatch_success_tmpl', $themeSuccessTmpl);
        }


    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array $vars 模板输出变量
     * @param array $replace 模板替换
     * @param array $config 模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $template = $this->parseTemplate($template);
        $more     = $this->getThemeFileMore($template);
        $this->assign('theme_vars', $more['vars']);
        $this->assign('theme_widgets', $more['widgets']);
        return parent::fetch($template, $vars, $replace, $config);
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    private function parseTemplate($template)
    {
        // 分析模板文件规则
        $request = $this->request;
        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($module, $template) = explode('@', $template);
        }

        $viewBase = config('template.view_base');

        if ($viewBase) {
            // 基础视图目录
            $module = isset($module) ? $module : $request->module();
            $path   = $viewBase . ($module ? $module . DS : '');
        } else {
            $path = isset($module) ? APP_PATH . $module . DS . 'view' . DS : config('template.view_path');
        }

        $depr = config('template.view_depr');
        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = cmf_parse_name($request->controller());
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DS, $controller) . $depr . $request->action();
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DS, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }
        return $path . ltrim($template, '/') . '.' . ltrim(config('template.view_suffix'), '.');
    }

    /**
     * 获取模板文件变量
     * @param string $file
     * @param string $theme
     * @return array
     */
    private function getThemeFileMore($file, $theme = "")
    {

        //TODO 增加缓存
        $theme = empty($theme) ? cmf_get_current_theme() : $theme;

        // 调试模式下自动更新模板
        if (APP_DEBUG) {
            $themeModel = new ThemeModel();
            $themeModel->updateTheme($theme);
        }

        $themePath = config('cmf_theme_path');
        $file      = str_replace('\\', '/', $file);
        $file      = str_replace('//', '/', $file);
        $file      = str_replace(['.html', '.php', $themePath . $theme . "/"], '', $file);

        $files = Db::name('theme_file')->field('more')->where(['theme' => $theme])->where(function ($query) use ($file) {
            $query->where(['is_public' => 1])->whereOr(['file' => $file]);
        })->select();

        $vars    = [];
        $widgets = [];
        foreach ($files as $file) {
            $oldMore = json_decode($file['more'], true);
            if (!empty($oldMore['vars'])) {
                foreach ($oldMore['vars'] as $varName => $var) {
                    $vars[$varName] = $var['value'];
                }
            }

            if (!empty($oldMore['widgets'])) {
                foreach ($oldMore['widgets'] as $widgetName => $widget) {

                    $widgetVars = [];
                    if (!empty($widget['vars'])) {
                        foreach ($widget['vars'] as $varName => $var) {
                            $widgetVars[$varName] = $var['value'];
                        }
                    }

                    $widget['vars']       = $widgetVars;
                    $widgets[$widgetName] = $widget;
                }
            }
        }

        return ['vars' => $vars, 'widgets' => $widgets];
    }

    public function checkUserLogin()
    {
        $userId = cmf_get_current_user_id();
        if (empty($userId)) {
            if ($this->request->isAjax()) {
                $this->error("您尚未登录", cmf_url("user/Login/index"));
            } else {
                $this->redirect(cmf_url("user/Login/index"));
            }
        }
    }

    /**
     *  API返回信息格式函数 ；0失败，1成功
     * @param string $code
     * @param string $message
     * @param array $data
     */
    public function apiResponse($code = '', $msg = '',$data = array()){
        header('Access-Control-Allow-Origin: *');
        header('Content-Type:application/json; charset=utf-8');
        $result = array(
            'code'=>$code,
            'msg'=>$msg,
            'data'=>$data,
        );
        die(json_encode($result,JSON_UNESCAPED_UNICODE));
    }

    /**
     * 检测是否是员工
     */
    public function check_staff()
    {
        $id = Session::get('user_id');

        $user = new UsersModel();
        $type = $user->where('id',$id)->value('type');
        $is_login = Session::get('is_login');
        //2是员工
        if($type == 2){
            $this->redirect('Login/index');
        }
    }

    /**
     * 检测是否是用户
     */
    public function check_user()
    {
        $id = Session::get('user_id');

        $user = new UsersModel();
        $type = $user->where('id',$id)->value('type');
        $is_login = Session::get('is_login');
        //1是用户
        if($type == 1){
            $this->redirect('Mine/index');
        }
    }

    /**
     * @title 获取signPackage
     * @return $signPackage
     */
    public function getSignPackage()
    {
        require_once EXTEND_PATH."jssdk/jssdk.php";
        $jssdk = new \JSSDK(Config::get('WX_APPID'), Config::get('WX_APP_SECRET'));
        return $jssdk->getSignPackage();
    }

    /**
     * @title 获取AccessToken
     * @return $acessToken
     */
    public function getAccessToken()
    {
        require_once EXTEND_PATH."jssdk/jssdk.php";
        $jssdk = new \JSSDK(Config::get('WX_APPID'), Config::get('WX_APP_SECRET'));
        return $jssdk->getAccessToken();
    }

}