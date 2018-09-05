<?php
/*
 * 环保评论相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/2 14:15
 */
namespace app\portal\service;

use app\portal\model\RubbishCommentModel;

class RcommentService
{
    public static function  rubbish_comment()
    {
        $rubbish_comment = new RubbishCommentModel();
        $list = $rubbish_comment
            ->field('user_nickname,avatar,score,content,substr(c.create_time,1,10) as c_create_time')
            ->alias('c')
            ->join('__USERS__ u','c.uid = u.id')
            ->order('c.id DESC')
            ->select()->toArray();
        return $list;
    }


}
