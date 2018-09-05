<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use app\portal\model\CometimeModel;
use cmf\controller\HomeBaseController;
use think\Cache;
use think\Db;

class IndexController
{
    /**
     * 初始化各个表
     */
    public function index()
    {
//        Db::execute("truncate cmf_goods_order");//商城订单表
//        Db::execute("truncate cmf_goods_order_detail");//商城订单详情表
//        Db::execute("truncate cmf_goods_comment");//商品评价表
//        Db::execute("truncate cmf_rubbish_order");//垃圾订单表
//        Db::execute("truncate cmf_rubbish_comment");//垃圾评价表
//        Db::execute("truncate cmf_use_record");//使用记录表
//        Db::execute("truncate cmf_return_record");//返佣记录表
//        Db::execute("truncate cmf_cash_record");//提现记录表
//        Db::execute("truncate cmf_car");//购物车表
//        Db::execute("truncate cmf_user_address");//用户地址表
//        Db::execute("truncate cmf_advice");//投诉建议表
//        Db::execute("truncate cmf_users");//用户表
    }
}
