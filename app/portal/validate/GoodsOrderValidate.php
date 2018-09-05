<?php
namespace app\portal\validate;

use think\Validate;

class GoodsOrderValidate extends Validate
{
    protected $rule =   [
        'sh_village'  => 'require',
        'sh_detail_address'  => 'require',
        'sh_name'  => 'require',
        'sh_mobile'  => 'require',
    ];

    protected $message  =   [
        'sh_village.require' => '请选择小区',
        'sh_detail_address.require' => '请填写详细地址',
        'sh_name.require' => '请填写联系人姓名',
        'sh_mobile.require' => '请填写联系电话',
    ];
}