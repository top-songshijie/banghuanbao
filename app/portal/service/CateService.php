<?php
/*
 * 商品分类相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/12 14:17
 */
namespace app\portal\service;

use app\portal\model\CateModel;

class CateService
{
    public static function  limit_cate($limit_num = false)
    {
        $cate = new CateModel();
        $cate_list = $cate
            ->field('id,cate_name,cate_thumb')
            ->limit($limit_num)
            ->order('sort ASC')
            ->select()
            ->toArray();
        foreach ($cate_list as $k=>$v){
            $cate_list[$k]['cate_thumb'] = cmf_get_image_url($v['cate_thumb']);
        }
        return $cate_list;
    }


}
