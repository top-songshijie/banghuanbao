<?php
namespace app\portal\validate;

use think\Validate;

class GoodsValidate extends Validate
{
    protected $rule =   [
        'shortcontent'  => 'require',
        'content'  => 'require',
        'goods_banner'  => 'require',
        'goods_thumb'  => 'require',
    ];

    protected $message  =   [
        'shortcontent.require' => '请填写产品简介',
        'content.require' => '请填写产品详情',
        'goods_banner.require' => '请上传产品相册图集',
        'goods_thumb.require' => '请上传产品缩略图',
    ];
}