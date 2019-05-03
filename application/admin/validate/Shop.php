<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Shop extends Validate {
	protected $rule = [
        'shopName' => 'require',
        'shopPhone' => 'require',
        'shopAddress' => 'require',
        'shopLocation' => 'require',
        'shopKeeper' => 'require',
        'shopDiscount' => 'require',
        'businessLicense' => 'require'
    ];

    protected $message  =   [
        'shopName.require' => '请输入店铺名称',
        'shopPhone.require' => '请输入店铺电话',
        'shopAddress.require' => '请输入店铺地址',
        'shopLocation.require' => '请输入店铺坐标地址',
        'shopKeeper.require' => '请输入店主姓名',
        'shopDiscount.require' => '请输入商铺折扣',
        'businessLicense.require' => '请上传商铺营业执照',

    ];
}