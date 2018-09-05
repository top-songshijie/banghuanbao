<?php
/*
 * 分享出去的页面
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/17 9:41
 */
namespace app\portal\controller;

use cmf\controller\NolimitController;

class ShareController extends NolimitController
{
    /**
     * 分享出去的页面
     */
    public function sharepage()
    {
        $user_id = $this->request->param('user_id','','intval');
        if(empty($user_id)){
            die();
        }

        $code = new CodeController();
        $code_url = $code->weixin_code($user_id);

        $this->assign('code_url',$code_url);
        return $this->fetch('sharepage');
    }

}