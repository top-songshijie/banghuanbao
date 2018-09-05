<?php
namespace app\portal\validate;

use think\Validate;

class AddressValidate extends Validate
{
    protected $rule =   [
        'village'  => 'require',
    ];

    protected $message  =   [
        'village.require' => '请填写小区名称',
    ];
}