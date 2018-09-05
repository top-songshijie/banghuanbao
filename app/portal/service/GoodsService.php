<?php
/*
 * 商品相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/10 17:33
 */
namespace app\portal\service;

use app\portal\model\GoodsAttkeyModel;
use app\portal\model\GoodsAttvalueModel;
use app\portal\model\GoodsModel;

class GoodsService
{
    public static function  goods_as_three($goods_id)
    {
        $goods = new GoodsModel();
        $attkey = new GoodsAttkeyModel();
        $attvalue = new GoodsAttvalueModel();
        $goods_info = $goods->where('id',$goods_id)->find()->toArray();
        $goods_info['content'] = htmlspecialchars_decode($goods_info['content']);
        $goods_info['low_price'] = $goods_info['goods_now_price'];
        if(empty($goods_info)){
            return false;
        }
        $goods_attkey = $attkey->where('good_id',$goods_id)->select()->toArray();
        foreach ($goods_attkey as $k=>$v){
            $goods_attkey[$k]['goods_attvalue'] = $attvalue->where('goods_attkey_id',$v['id'])->select()->toArray();
            //计算最低价
            $low_price = 99999999;
            foreach ($goods_attkey[$k]['goods_attvalue'] as $kk=>$vv){
                if($low_price > $vv['goods_attvalue_now_price']){
                    $low_price = $vv['goods_attvalue_now_price'];
                }
            }
            $goods_info['low_price'] += $low_price;
            $biaoji = '';
            foreach ($goods_attkey[$k]['goods_attvalue'] as $kk=>$vv){
                if($low_price == $vv['goods_attvalue_now_price'] and empty($biaoji)){
                    $goods_attkey[$k]['goods_attvalue'][$kk]['is_select'] = 1;
                    $biaoji = 1;
                }else{
                    $goods_attkey[$k]['goods_attvalue'][$kk]['is_select'] = 0;
                }
            }
        }
        $goods_info['goods_attkey'] = $goods_attkey;
        return $goods_info;
    }

    public static function special_goods($special,$limit_num = false)
    {
        $goods = new GoodsModel();
        $attkey = new GoodsAttkeyModel();
        $attvalue = new GoodsAttvalueModel();
        $list = $goods
            ->field('id,goods_name,goods_thumb,goods_now_price,goods_ago_price')
            ->where(['if_shelf'=>1,$special=>1])
            ->limit($limit_num)
            ->order('id DESC')
            ->select()
            ->toArray();
        foreach ($list as $k=>$v){
            $list[$k]['low_price'] = $v['goods_now_price'];
            $goods_attkey = $attkey->where('good_id',$v['id'])->select()->toArray();
            foreach ($goods_attkey as $kk=>$vv) {
                $goods_attkey[$kk]['goods_attvalue'] = $attvalue->where('goods_attkey_id', $vv['id'])->select()->toArray();
                //计算最低价
                $low_price = 99999999;
                foreach ($goods_attkey[$kk]['goods_attvalue'] as $kkk=>$vvv){
                    if($low_price > $vvv['goods_attvalue_now_price']){
                        $low_price = $vvv['goods_attvalue_now_price'];
                    }
                }
                $list[$k]['low_price'] += $low_price;
            }
        }
        return $list;
    }

    public static function cate_goods($cate_id)
    {
        $goods = new GoodsModel();
        $attkey = new GoodsAttkeyModel();
        $attvalue = new GoodsAttvalueModel();
        $list = $goods
            ->field('id,goods_name,goods_thumb,goods_now_price,goods_ago_price,if_only_exchange')
            ->where(['if_shelf'=>1,'cid'=>$cate_id])
            ->order('id DESC')
            ->select()
            ->toArray();
        foreach ($list as $k=>$v){
            $list[$k]['low_price'] = $v['goods_now_price'];
            $goods_attkey = $attkey->where('good_id',$v['id'])->select()->toArray();
            foreach ($goods_attkey as $kk=>$vv) {
                $goods_attkey[$kk]['goods_attvalue'] = $attvalue->where('goods_attkey_id', $vv['id'])->select()->toArray();
                //计算最低价
                $low_price = 99999999;
                foreach ($goods_attkey[$kk]['goods_attvalue'] as $kkk=>$vvv){
                    if($low_price > $vvv['goods_attvalue_now_price']){
                        $low_price = $vvv['goods_attvalue_now_price'];
                    }
                }
                $list[$k]['low_price'] += $low_price;
            }
        }
        return $list;
    }

    public static function attvalueid_to_attvaluename($attvalue_id)
    {
        $arr_attvalue_id = explode(',',$attvalue_id);
        $attvalue = new GoodsAttvalueModel();
        foreach ($arr_attvalue_id as $k=>$v){
            $arr_attvalue_name[] = $attvalue->where('id',$v)->value('attvalue_name');
        }
        return implode(',',$arr_attvalue_name);
    }

    public static function get_oneprice($goods_id,$goods_attvalue_id)
    {
        $goods = new GoodsModel();
        $attvalue = new GoodsAttvalueModel();
        $oneprice = $goods->where('id',$goods_id)->value('goods_now_price');
        $goods_attvalue_id = explode(',',$goods_attvalue_id);
        $twoprice = 0;
        foreach ($goods_attvalue_id as $k=>$v){
            $twoprice += $attvalue->where('id',$v)->value('goods_attvalue_now_price');
        }
        return $oneprice + $twoprice;
    }

}
