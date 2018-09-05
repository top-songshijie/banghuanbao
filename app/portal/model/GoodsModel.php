<?php
/*
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/6 9:36
 */
namespace app\portal\model;

use think\Model;

class GoodsModel extends Model
{
    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getGoodsBannerAttr($value)
    {
        $value = json_decode($value,true);
        foreach ($value as $k=>$v){
            $value[$k] = cmf_get_image_url($v);
        }
        return $value;
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getGoodsThumbAttr($value)
    {
        return cmf_get_image_url($value);
    }
}