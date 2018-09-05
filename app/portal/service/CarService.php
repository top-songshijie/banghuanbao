<?php
/*
 * 购物车相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/23 16:13
 */
namespace app\portal\service;

use app\portal\model\CarModel;
use app\portal\model\GoodsAttkeyModel;
use app\portal\model\GoodsAttvalueModel;
use app\portal\model\GoodsModel;

class CarService
{
    public static function  car_list($user_id)
    {
        $car = new CarModel();
        $attvalue = new GoodsAttvalueModel();
        $list = $car
            ->alias('c')
            ->field('c.*,g.goods_thumb,g.goods_name,g.shortcontent,g.goods_now_price,if_tejia')
            ->join('__GOODS__ g','c.goods_id = g.id')
            ->where('user_id',$user_id)
            ->select()
            ->toArray();
        foreach ($list as $k=>$v){
            $list[$k]['goods_thumb'] = cmf_get_image_url($v['goods_thumb']);
            $good_attvalue_id = explode(',',$v['goods_attvalue_id']);
            $total_price = 0;
            foreach ($good_attvalue_id as $kk=>$vv){
                $total_price += $attvalue
                    ->where('id',$vv)
                    ->value('goods_attvalue_now_price');
            }
            $list[$k]['total_price'] = $total_price+$v['goods_now_price'];
        }
        return $list;
    }

    //暂弃
    public static function car_small_price($car_id)
    {
        $goods = new GoodsModel();
        $attkey = new GoodsAttkeyModel();
        $car = new CarModel();
        $carinfo = $car->find($car_id);
        $goods_now_price = $goods->where('id',$carinfo['goods_id'])->value('goods_now_price');
        $num = $car->where('id',$car_id)->value('num');
        $good_attvalue_id = explode(',',$carinfo['goods_attvalue_id']);
        $total_price = 0;
        foreach ($good_attvalue_id as $kk=>$vv){
            $total_price += $attkey
                ->alias('k')
                ->join('__GOODS_ATTVALUE__ v','k.id = v.goods_attkey_id')
                ->where('v.id',$vv)
                ->value('goods_attkey_now_price');
        }
        return $total_price = $total_price+$goods_now_price*$num;
    }

}
