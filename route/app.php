<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// Route::post('/common/image/add', 'common/Image/add'); // ע��
Route::group('app', [
    '/index' => 'app/Index/index',
    '/huiming/index' => 'app/Index/huimingIndex',
    '/goods/list' => 'app/Goods/list',
    '/goods/info' => 'app/Goods/info',
    '/jiguang/send' => 'app/Jiguang/send',
    '/system/version/info' => 'app/System/getVersion',
    /**
     * 我是买手
     */
    '/buyer/index' => 'app/Buyer/index', //我是买手
    '/buyer/invitation' => 'app/Buyer/invitation', //邀请店主
    '/buyer/share' => 'app/Buyer/share', //分享我的店铺
    '/buyer/statistics/data' => 'app/Statistics/dataList', //数据中心
    '/buyer/statistics/my/client' => 'app/Statistics/myClient', //我的客户
    '/buyer/order/list' => 'app/Statistics/orderList', //我的订单
    '/buyer/contact/index' => 'app/Buyer/getContact', //联系我们
    '/buyer/contact/update' => 'app/Buyer/updateContact', //联系我们->保存微信
    '/buyer/cash/index' => 'app/Buyer/getCash', //提现
    '/buyer/cash/list' => 'app/Buyer/getCashList', //提现记录
    '/buyer/cash/info' => 'app/Buyer/getCashInfo', //提现记录详细
    '/buyer/cash/add' => 'app/Buyer/CashAdd', //提交提现
    '/buyer/cash/bankInfo' => 'app/Buyer/bankInfo', //银行卡信息


    '/login' => 'app/Login/index',
    '/passwordLogin' => 'app/Login/passwordLogin',
    '/password/update' => 'app/User/updatePassword',
    '/register' => 'app/Login/register',
    '/referee/list' => 'app/Login/referee',
    '/referee/info' => 'app/Login/refereeInfo',
    /**
     * 商品模块
     */
    'goods/list' => 'app/Goods/list',
    'goods/member/list' => 'app/Goods/memberList',
    'goods/info' => 'app/Goods/info',
    'goods/share' => 'app/Goods/goodsShare',
    'goods/series/list' => 'app/Goods/seriesList',
    'goods/search/list' => 'app/Goods/goodsSearchList',
    'goods/like/list' => 'app/Goods/goodsLikeList',
    /**
     * 购物车模块
     */
    'cart/list' => 'app/Cart/list',
    'cart/add' => 'app/Cart/add',
    'cart/update' => 'app/Cart/update',
    'cart/delete' => 'app/Cart/delete',
    'cart/select' => 'h5/Cart/select',
    /**
     * 订单模块
     */
    'order/goodsPreview' => 'app/Order/goodsPreview', // 单个商品购买
    'order/cartPreview' => 'app/Order/cartPreview', // 购物车下单
    'order/cartValidate' => 'app/Order/cartValidate', // 验证是否可以从购物车，加入订单预览
    'order/list' => 'app/Order/list',
    'order/info' => 'app/Order/info',
    'order/add' => 'app/Order/add',
    'order/pay' => 'app/Order/pay',
    'order/cancel' => 'app/Order/cancelOrder',
    'order/confirm' => 'app/Order/confirmOrder',
    'order/delete' => 'app/Order/delete',
    /**
     * 退货模块
     */
    'order/refund/list' => 'app/OrderRefund/list',
    'order/refund/add' => 'app/OrderRefund/add',
    'order/refund/info' => 'app/OrderRefund/info',
    /**
     * 个人信息
     */
    'user/info' => 'app/User/info',
    'user/update' => 'app/User/update',
    'user/index' => 'app/User/index',
    'user/logout' => 'app/User/logout',
    /**
     * 用户地址
     */
    'user/address/add' => 'app/UserAddress/add',
    'user/address/list' => 'app/UserAddress/list',
    'user/address/update' => 'app/UserAddress/update',
    'user/address/delete' => 'app/UserAddress/delete',
    'user/address/default/set' => 'app/UserAddress/setDefault',
    'user/address/default/get' => 'app/UserAddress/getDefault',
    /**
     * 支付宝支付h5
     */
    'alipay/payUrlAlipay' => 'app/Alipay/payUrlAlipay',
    'alipay/checkRsaSign' => 'app/Alipay/checkRsaSign',
    'alipay/checkRsaSign' => 'app/Alipay/checkRsaSign',
    'alipay/returnUrl' => 'app/Alipay/returnUrl',
    /**
     * 微信支付
     */
    'wxpay/settlement' => 'app/WxPay/settlement',
    'wxpay/success' => 'app/WxPay/paysuccess',
    'huiming/wxpay/success' => 'app/WxPay/huimingPaysuccess',
    'wxApi/selectOrder' => 'app/WxApi/selectOrder',
    /**
     * 优惠券
     */
    '/coupon/selfcoupon'=>'app/Coupon/getSelfCoupon',
    '/coupon/usablecoupon'=>'app/Coupon/getUsableCoupon',
    '/coupon/exchange'=>'app/Coupon/ExchangeCoupon',
    '/coupon/checkcoupon'=>'app/Coupon/CheckCoupon',
    '/coupon/getlog'=>'app/Coupon/getLog',
    /**
     * 商铺
     */
    'shop/getshopinfo' => 'app/Shop/getShopInfo',
    'shop/getshopqcode' => 'app/Shop/getGatheringQcode',
    'shop/shopsubtitle/get' => 'app/Shop/getShopSubtitle',
    'shop/shopsubtitle/update' => 'app/Shop/updateShopSubtitle',
    'shop/shopimage/get' => 'app/Shop/getShopImage',
    'shop/shopimage/add' => 'app/Shop/addShopImage',
    'shop/shopimage/del' => 'app/Shop/delShopImage',
    'shop/shopstatus/get' => 'app/Shop/getShopStatus',
    'shop/shopstatus/update' => 'app/Shop/updateShopStatus',
    'shop/applywith/apply' => 'app/Shop/applyWith',
])
->header([
    'Access-Control-Allow-Origin' => '*',
    'Content-Type' => 'application/json;charset=UTF-8'
])->allowCrossDomain();


return [

];
