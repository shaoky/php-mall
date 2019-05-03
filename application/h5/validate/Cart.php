<?php 
namespace app\h5\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Cart extends Validate {
	protected $rule = [
        'goodsId' => 'require',
        'cartId' => 'require',
        'goodsNum' => 'require',
        'isSelected' => 'require'
    ];

    protected $message  =   [
        'goodsId.require' => '请选择商品',
        'cartId.require' => '请选择购物车',
        'goodsNum.require' => '请输入商品数量',
        'isSelected.require' => '请输入是否选中商品'
    ];

    protected $scene = [
        'add' => ['goodsId', 'goodsNum'],
        'update' => ['cartId','goodsNum', 'isSelected'],
    ];
    
}