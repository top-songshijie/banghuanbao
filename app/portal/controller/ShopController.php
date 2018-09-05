<?php
/*
 * 商城部分
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/12 13:35
 */
namespace app\portal\controller;

use app\portal\model\CarModel;
use app\portal\model\CateModel;
use app\portal\model\GoodsCommentModel;
use app\portal\model\GoodsModel;
use app\portal\model\GoodsOrderModel;
use app\portal\model\XsdataModel;
use app\portal\service\CarService;
use app\portal\service\CateService;
use app\portal\service\GcommentService;
use app\portal\service\GoodsService;
use cmf\controller\HomeBaseController;
use think\Session;
use think\Db;

class ShopController extends HomeBaseController
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
        $banner = Db::name('slide_item')->field('image,url')->where('slide_id', 2)->select()->toArray();
        foreach ($banner as $k => $v) {
            $banner[$k]['image'] = cmf_get_image_url($v['image']);
        }
        //获取分类（前四个）
        $cate_list = CateService::limit_cate(4);
        //限时特价
        $tejia_goods = GoodsService::special_goods('if_tejia', 4);
        //兑换商品
        $exchange_goods = GoodsService::special_goods('if_only_exchange', 4);
        //热门商品
        $recommend_goods = GoodsService::special_goods('if_recommend', 4);
        //限时倒计时
        $xsdata = new XsdataModel();
        $xsinfo = $xsdata->where('id', 1)->find();
        $xsinfo['end_time'] = $xsinfo['end_time'] * 1000;

        $this->assign('banner', $banner);
        $this->assign('cate_list', $cate_list);
        $this->assign('tejia_goods', $tejia_goods);
        $this->assign('exchange_goods', $exchange_goods);
        $this->assign('recommend_goods', $recommend_goods);
        $this->assign('xsinfo', $xsinfo);
        return $this->fetch();
    }

    /**
     * 全部分类
     */
    public function all_cate()
    {
        $this->check_staff();
        $cate_id = $this->request->param('cate_id', '', 'intval');
        $cate_list = CateService::limit_cate();
        $cate_id = empty($cate_id) ? $cate_list[0]['id'] : $cate_id;
        $goods_list = GoodsService::cate_goods($cate_id);

        $this->assign('cate_list', $cate_list);
        $this->assign('goods_list', $goods_list);
        $this->assign('cate_id', $cate_id);
        return $this->fetch();
    }

    /**
     * 首页特殊商品的全部
     * special => if_tejia,if_only_exchange,if_recommend
     */
    public function special_goods_list()
    {
        $this->check_staff();
        $special = $this->request->param('special', 'if_recommend');
        $list = GoodsService::special_goods($special);

        $this->assign('list', $list);
        if ($special == 'if_tejia') {
            //限时倒计时
            $xsdata = new XsdataModel();
            $xsinfo = $xsdata->where('id', 1)->find();
            $xsinfo['end_time'] = $xsinfo['end_time'] * 1000;
            $this->assign('xsinfo', $xsinfo);
            return $this->fetch('tejia_list');
        } elseif ($special == 'if_only_exchange') {
            return $this->fetch('exchange_list');
        } else {
            return $this->fetch('recommend_list');
        }
    }


    /**
     * 商品详情
     */
    public function goods_detail()
    {
        $this->check_staff();
        Session::delete('shop');
        $goods_id = $this->request->param('id');
        $goods_info = GoodsService::goods_as_three($goods_id);
        $goods_comment_list = GcommentService::goods_comment($goods_id);
        $car = new CarModel();
        $car_num = $car->where('user_id', $this->user_id)->sum('num');
        //限时倒计时
        $xsdata = new XsdataModel();
        $xsinfo = $xsdata->where('id', 1)->find();
        //判断商品是否可销售
        if (($xsinfo['end_time'] == time() or $xsinfo['end_time'] < time()) and $goods_info['if_tejia'] == 1) {
            $this->assign('if_mw', 1);
        } else {
            $this->assign('if_mw', 0);
        }
        $xsinfo['end_time'] = $xsinfo['end_time'] * 1000;
//        echo "<pre/>";
//        print_r($goods_info);
//        die;
        $this->assign('goods_info', $goods_info);
        $this->assign('goods_comment_list', $goods_comment_list);
        $this->assign('car_num', $car_num);
        $this->assign('xsinfo', $xsinfo);
        return $this->fetch();
    }


    /**
     * 评论
     */
    public function comment()
    {
        $this->check_staff();
        if($this->request->isAjax()){
            $goods_id = $this->request->param('goods_id','','intval');
            $score = $this->request->param('score','','intval');
            $content = $this->request->param('content','');
            $goods_comment = new GoodsCommentModel();
            $res = $goods_comment->insert([
                'user_id' => $this->user_id,
                'goods_id' => $goods_id,
                'score' => $score,
                'content' => $content,
                'create_time' => date('Y-m-d H"i:s')
            ]);
            if (!$res) {
                $this->apiResponse(0,'网络延迟，请稍后重试','');
            }
            $this->apiResponse(1,'ok','');
        }else{
            $goods_id = $this->request->param('goods_id','','intval');
            $goods = new GoodsModel();
            $info = $goods->find($goods_id);

            $this->assign('info',$info);
            $this->assign('goods_id',$goods_id);
            return $this->fetch();
        }
    }

    //购物车页面
    public function car()
    {
        $this->check_staff();
        Session::delete('shop');
        $car = new CarModel();
        $list = CarService::car_list($this->user_id);
        //判断是否到时
        $xsdata = new XsdataModel();
        $xsinfo = $xsdata->find(1)->toArray();
        if($xsinfo['end_time'] < time()){
            foreach ($list as $k=>$v){
                if($v['if_tejia'] == 1){
                    $car->where('id',$v['id'])->delete();
                    $is_delete = 1;
                }
            }
        }
        if(empty($list)){
            $is_null = 1;
        }
        $list = CarService::car_list($this->user_id);
        $this->assign('list', $list);
        $this->assign('is_null',isset($is_null)?$is_null:2);
        $this->assign('is_delete', isset($is_delete)?$is_delete:'0');
        return $this->fetch();
    }

    /**
     * 接单
     */
    public function jiedan()
    {
        if ($this->request->isAjax()) {
            //goods_order表id
            $id = $this->request->param('id', '', 'intval');
            $goods_order = new GoodsOrderModel();
            $res = $goods_order->where('id', $id)->data([
                'order_status' => 1,
                'eid' => $this->user_id
            ])->update();
            $info = $goods_order
                ->alias('o')
                ->field('u.openid')
                ->join('__USERS__ u','u.id = o.user_id')
                ->find();
            $notive = new NoticeController();
            $notive->WxSend3($info['openid'],$id);
            if ($res) {
                $this->apiResponse(1, 'ok', '');
            }
        }
    }

    /**
     * 配送
     */
    public function peisong()
    {
        if ($this->request->isAjax()) {
            //goods_order表id
            $id = $this->request->param('id', '', 'intval');
            $goods_order = new GoodsOrderModel();
            $res = $goods_order->where('id', $id)->data([
                'order_status' => 2,
            ])->update();
            if ($res) {
                $this->apiResponse(1, 'ok', '');
            }
        }
    }

    /**
     * 完成
     */
    public function wancheng()
    {
        if ($this->request->isAjax()) {
            //goods_order表id
            $id = $this->request->param('id', '', 'intval');
            $goods_order = new GoodsOrderModel();
            $res = $goods_order->where('id', $id)->data([
                'order_status' => 3,
            ])->update();
            $info = $goods_order
                ->alias('o')
                ->field('u.openid')
                ->join('__USERS__ u','u.id = o.user_id')
                ->find();
            $notive = new NoticeController();
            $notive->WxSend4($info['openid'],$id);
            if ($res) {
                $this->apiResponse(1, 'ok', '');
            }
        }
    }

    /**
     * 搜索
     */
    public function search()
    {
        $this->check_staff();
        $keyword = $this->request->param('keyword', '');
        $goods = new GoodsModel();
        $where['if_shelf'] = 1;
        $where['goods_name'] = ['like', "%$keyword%"];
        $list = $goods
            ->field('id,goods_name,goods_thumb,goods_now_price,goods_ago_price,if_tejia,if_only_exchange,if_recommend')
            ->where($where)
            ->order('id DESC')
            ->select()
            ->toArray();

        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        return $this->fetch();
    }

}
