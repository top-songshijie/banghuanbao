<?php
/*
 * 用户地址相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/12 9:38
 */
namespace app\portal\service;


use app\portal\model\AddressModel;
use app\portal\model\UserAddressModel;
use app\portal\model\UsersModel;
use think\Db;

class UseraddressService
{
    /**
     * 添加地址
     */
    public static function add($user_id,$village,$detail_address,$name,$mobile)
    {
        $user_address = new UserAddressModel();
        $res = $user_address->where('user_id',$user_id)->setField('is_default',0);
        $res2 = $user_address->insert([
            'user_id' => $user_id,
            'village' => $village,
            'detail_address' => $detail_address,
            'name' => $name,
            'mobile' => $mobile,
            'create_time' => date('Y-m-d H:i:s'),
            'is_default' => 1
        ]);
        if($res2){
            return true;
        }
        return false;
    }

    /**
     * 设为默认地址
     */
    public static function set_default($user_id,$user_address_id)
    {
        Db::startTrans();
        $user_address = new UserAddressModel();
        $res = $user_address->where('user_id',$user_id)->setField('is_default',0);
        $res2 = $user_address->where('id',$user_address_id)->setField('is_default',1);
        if(!$res or !$res2){
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * 编辑地址
     */
    public static function edit($user_address_id,$user_id,$village,$detail_address,$name,$mobile)
    {
        Db::startTrans();
        $user_address = new UserAddressModel();
        $res = $user_address->where('user_id',$user_id)->setField('is_default',0);
        $res2 = $user_address->update([
            'id' => $user_address_id,
            'user_id' => $user_id,
            'village' => $village,
            'detail_address' => $detail_address,
            'name' => $name,
            'mobile' => $mobile,
            'create_time' => date('Y-m-d H:i:s'),
            'is_default' => 1
        ]);
        if(!$res or !$res2){
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    public static function villagesid_to_villagesname($user_id)
    {
        $users = new UsersModel();
        $address = new AddressModel();
        $villages = $users->where('id',$user_id)->value('villages');
        $villages = explode(',',$villages);
        foreach ($villages as $k=>$v){
            $villages_name[] = $address->where('id',$v)->value('village');
        }
        return $villages_name;
    }

}
