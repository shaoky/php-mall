<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品秒杀验证器
 */
class GoodsSeckill extends Validate {
	protected $rule = [
        'gsTitle' => 'require|max:50',
        'goodsId' => 'require',
        'gsSort' => 'require',
        'gsImage' => 'require',
        'startTime' => 'require',
        'endTime' => 'require',
        'minBuy' => 'require',
        'maxBuy' => 'require',
        'activityPrice' => 'require',
        'memberMinBuy' => 'require',
        'memberMaxBuy' => 'require',
        'memberActivityPrice' => 'require',
        'isCommission' => 'require',
        'isOpen' => 'require',
        
    ];

    protected $message  =   [
        'title.require' => '请输入活动名称',
        'title.max'     => '活动不能超过50个字符',
        // 'width.require' => '广告位宽度',
        // 'height.require' => '广告位高度',
        // 'mark.require' => '请输入活动标记码',
    ];
}