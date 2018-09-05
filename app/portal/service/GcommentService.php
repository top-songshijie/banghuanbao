<?php
/*
 * 商品评论相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/12 16:43
 */
namespace app\portal\service;

use app\portal\model\GoodsCommentModel;

class GcommentService
{
    public static function  goods_comment($goods_id)
    {
        $goods_comment = new GoodsCommentModel();
        $list = $goods_comment
            ->field('user_nickname,avatar,score,content,substr(c.create_time,1,10) as c_create_time')
            ->alias('c')
            ->join('__USERS__ u','c.user_id = u.id')
            ->where('c.goods_id',$goods_id)
            ->order('c.id DESC')
            ->select()->toArray();
        return $list;
    }


}
