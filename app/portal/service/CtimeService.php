<?php
/*
 * 上门时间相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/8/13 16:53
 */
namespace app\portal\service;

use app\portal\model\CometimeModel;

class CtimeService
{
    public static function all_cate()
    {
        $cometime = new CometimeModel();
        $info = $cometime->where('id',1)->find()->toArray();
        $now_fen = date('i');
        if($now_fen > 20){
            $now_shi = date('H')+1;
        }else{
            $now_shi = date('H');
        }
        if($now_shi > $info['start_time']){
            for ($i = $now_shi;$i < $info['end_time'];$i++){
                if($i < 10){
                    $i = "0".$i;
                }
                $all_date[] = date('m月d日').$i.":00";
            }
        }else{
            for ($i = $info['start_time'];$i < $info['end_time'];$i++){
                if($i < 10){
                    $i = "0".$i;
                }
                $all_date[] = date('m月d日').$i.":00";
            }
        }
        //下一天的全时段
        for ($i = $info['start_time'];$i < $info['end_time'];$i++){
            if($i < 10){
                $i = "0".$i;
            }
            $all_date[] = date('m月d日',time()+86400).$i.":00";
//            $futureData[] = date('m月d日',time()+86400).$i.":00";
        }
        if(!isset($all_date)){
            $all_date[] = "今天已经不能下单了";
        }
//        $data['first'] = $all_date;
//        $data['two'] = $futureData;
        return $all_date;
    }

}