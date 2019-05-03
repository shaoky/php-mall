<?php  
namespace app\h5\model;  
use app\h5\model\Common;
use think\Db;
use app\comm\model\Sms;
use jiguang\Jgsdk;
use app\app\model\Coupon;

/**
 * @apiDefine h5OrderGroup h5-订单模块
 */

 /**
 * @api {post} / 1. 订单表
 * @apiName order
 * @apiGroup h5OrderGroup
 * @apiSuccess {Number} orderId 订单Id
 * @apiSuccess {String} orderNo 订单编号
 * @apiSuccess {Number} provinceId 省
 * @apiSuccess {Number} cityId 市
 * @apiSuccess {Number} countyId 区县
 * @apiSuccess {String} userName 收货人
 * @apiSuccess {String} userAddress 用户详细地址
 * @apiSuccess {String} orderRemark 订单备注
 * @apiSuccess {String} totalMoney 商品总金额，未进行任何折扣的总价格
 * @apiSuccess {String} deliverMoney 商品运费
 * @apiSuccess {String} courierName 快递名称
 * @apiSuccess {String} courierNo 快递单号
 * @apiSuccess {String} remainingTime 订单关闭的时间，下单后生成
 * @apiSuccess {String} realTotalMoney 实际订单支付价格
 * @apiSuccess {Number} orderFrom 订单来源，1：h5，2：app
 * @apiSuccess {Number} payFrom 支付来源，1：支付宝，2：微信
 * @apiSuccess {Number} createTime 下单时间
 * @apiSuccess {Number} receiveTime 收货时间
 * @apiSuccess {Number} deliveryTime 发货时间
 * @apiSuccess {Number} payFrom 支付来源，1：支付宝，2：微信
 * @apiVersion 1.0.0
 */

class Order extends Common {
    /**
     * @api {post} /h5/order/cartValidate 1.1 购物车结算验证
     * @apiName orderCartValidate
     * @apiGroup h5OrderGroup
     * @apiHeader {String} Authorization token
     * @apiSuccess {Number} code 返回200，可继续操作
     * @apiSuccess {Boolean} data true
     * @apiVersion 1.0.0
     */
    public function cartValidate() {
        $headerParams = $this->getHeaderParams();
        $userId = $this->getUserId();
        try {
            $list = db('cart')->where('userId', $userId)->select();
            if (count($list) == 0) {
                $this->error = '购物车没有商品';
                return;
            }
            $count = 0;
            foreach($list as $item) {
                if ($item['isSelected'] == 1) {
                    $count++;
                }
            }
            if ($count == 0) {
                $this->error = '请选择商品';
                return;
            }

            if ($headerParams['app'] == 2) {
                $app_env = config('app.app_env');
                foreach($list as $item) {
                    if ($app_env == 'production') {
                        if ($item['goodsId'] == 161 && $item['goodsNum'] > 1) {
                            $this->error = '该商品限购最大购买1件';
                            return;
                        }
                    }
                    if ($app_env == 'test') {
                        if ($item['goodsId'] == 140 && $item['goodsNum'] > 1) {
                            $this->error = '该商品限购最大购买1件';
                            return;
                        }
                    }
                }

                // $order = Db::name('order')->where([
                //     ['userId', '=', $userId],
                //     ['orderStatus', 'in', [2,3,4]]
                // ])->select();
               
                // if (count($order) > 0) {
                //     foreach($order as $item) {
                //         $orderGoods = Db::name('order_goods')->where('orderId', $item['orderId'])->find();
                //         if ($app_env == 'production') {
                //             if ($orderGoods['goodsId'] == 161) {
                //                 $this->error = '该商品限购最大购买1次';
                //                 return;
                //             }
                //         }
                //         if ($app_env == 'test') {
                //             if ($orderGoods['goodsId'] == 140) {
                //                 $this->error = '该商品限购最大购买1次';
                //                 return;
                //             }
                //         }
                //     }
                // }
                
            }
            

            foreach($list as $item) {
                $goods = db('goods')->where('goodsId', $item['goodsId'])->find();
                if ($goods) {
                    if ($goods['goodsStock'] < $item['goodsNum']) {
                        $this->error = $goods['goodsName'].'商品库存数量不足，请重新选择';
                        return;
                    }
                } else {
                    $this->error = $item['goodsName'].'商品已经下架，请删除后提交';
                    return;
                }
                
            }
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/goodsPreview 1.2 商品订单预览
     * @apiName orderGoodsPreview
     * @apiGroup h5OrderGroup
     * @apiParam {Number} goodsId 产品id
     * @apiParam {Number} goodsNum 产品数量
     * @apiSuccess {Array} goodsList 见商品表
     * @apiSuccess {String} totalMoney 商品总金额
     * @apiSuccess {String} payMoney 支付金额
     * @apiSuccess {String} deliverMoney 运费
     * @apiVersion 1.0.0
     */
    public function goodsPreview($params) {
        $headerParams = $this->getHeaderParams();
        $user = $this->getUserInfo();

        $where = [
            'goodsId' => $params['goodsId']
        ];
        Db::startTrans();
        try {
            $data = [];
            $config = db('web_config')->find();
            $goods = Db::name('goods')->where($where)->find();
            if ($goods == null) {
                $this->error = '没有找到商品';
                return;
            }
            // 查看是否是抢购商品，如果是，看限购，如果超出去了，提示不可以买
            // if ($user['isBuyMemberGoods'] == 1) {
            //     $this->error = '您已经是VIP会员了';
            //     return;
            // }
            if ($user['userType'] == 1) {
                $data['totalMoney'] = $goods['shopPrice'] * $params['goodsNum'];
                $goods['goodsPrice'] = $goods['shopPrice'];
            }
            if ($user['userType'] > 1) {
                $data['totalMoney'] = $goods['memberPrice'] * $params['goodsNum'];
                $goods['goodsPrice'] = $goods['memberPrice'];
            }
            
            $goods['goodsNum'] = $params['goodsNum'];
            $data['goodsList'][0] = $goods;
            // 是否包邮
            if ($data['goodsList'][0]['isFreeShipping'] == 1) {
                $data['deliverMoney'] = 0;
                $data['payMoney'] = $data['totalMoney'];
            } else {
                $data['deliverMoney'] = $config['deliverMoney'];
                $data['payMoney'] = $data['totalMoney'] + $data['deliverMoney'];
            }
            
            
            Db::commit();
            return $data;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/cartPreview 1.3 购物车订单预览
     * @apiName orderCartPreview
     * @apiGroup h5OrderGroup
     * @apiParam {Number} [cuId] 优惠券Id，不使用任何优惠券，传-1
     * @apiSuccess {Array} goodsList 见商品表
     * @apiSuccess {String} totalMoney 商品总金额
     * @apiSuccess {String} payMoney 支付金额
     * @apiSuccess {String} deliverMoney 运费
     * @apiSuccess {Object} coupon 优惠券，注：存在可用优惠券，才会显示该对象数据，否则coupon: null
     * @apiSuccess {Number} .cuId 优惠id
     * @apiSuccess {String} .cuText 优惠券显示文本
     * @apiVersion 1.0.0
     */
    public function cartPreview($params) {
        $user = $this->getUserInfo();
        $headerParams = $this->getHeaderParams();

        $where = [
            'a.userId' => $user['userId'],
            'a.isSelected' => 1,
            'b.isOpen' => 1,
            'a.cartApp' => $headerParams['app']
        ];
        Db::startTrans();
        try {
            $config = Db::name('web_config')->find();
            $data['goodsList'] = Db::name('cart')->alias('a')
                ->join('goods b', 'a.goodsId = b.goodsId')
                ->field('a.goodsNum, a.skuId, b.*')
                ->where($where)
                // ->join('goods b', 'a.goodsId = b.goodsId')
                // ->join('goods_sku c', 'a.skuId = c.skuId')
                // ->field('a.goodsNum, a.skuId, b.*, c.shopPrice as skuShopPrice, c.memberPrice as skuMemberPrice')
                // ->where($where)
                ->select();
            if (!$data['goodsList']) {
                $this->error = '请添加购物车商品';
                return;
            }
            
            $data['totalMoney'] = 0;
            $isFreeShipping = 0;
            foreach($data['goodsList'] as &$item) {
                if ($item['isFreeShipping'] == 1) {
                    $isFreeShipping = 1;
                }
                if ($item['skuId']){
                     // 获取sku
                    $goodsSku = Db::name('goods_sku')->where('skuId', $item['skuId'])->find();
                    $goodsSku['skuSpec'] = json_decode($goodsSku['skuSpec']);
                    $item['skuSpec'] = implode("，", $goodsSku['skuSpec']);

                    $data['totalMoney'] += $goodsSku['shopPrice'] * $item['goodsNum'];
                    $item['goodsPrice'] = $goodsSku['shopPrice'];
                    // if ($user['userType'] == 1) {
                    //     $data['totalMoney'] += $item['skuShopPrice'] * $item['goodsNum'];
                    //     $item['goodsPrice'] = $item['skuShopPrice'];
                    // } else {
                    //     $data['totalMoney'] += $item['skuMemberPrice'] * $item['goodsNum'];
                    //     $item['goodsPrice'] = $item['skuMemberPrice'];
                    // }
                } else {
                    if ($user['userType'] == 1) {
                        $data['totalMoney'] += $item['shopPrice'] * $item['goodsNum'];
                        $item['goodsPrice'] = $item['shopPrice'];
                    } else {
                        $data['totalMoney'] += $item['memberPrice'] * $item['goodsNum'];
                        $item['goodsPrice'] = $item['memberPrice'];
                    }
                }
               
               
            }

            if ($isFreeShipping == 1) {
                $data['deliverMoney'] = 0;
                $data['payMoney'] = $data['totalMoney'];
            } else {
                if ($data['totalMoney'] < $config['freeShippingMoney']) {
                    $data['deliverMoney'] = $config['deliverMoney'];
                    $data['payMoney'] = $data['totalMoney'] +  $data['deliverMoney'];
                } else {
                    $data['deliverMoney'] = 0;
                    $data['payMoney'] = $data['totalMoney'];
                }
            }
            $couponWhere = [
                'a.userId' => $user['userId'],
                'a.cuStatus' => 2
            ];

            $isUseCoupon = true;
            if (!empty($params['cuId'])) {
                $couponWhere['a.cuId'] = $params['cuId'];
            }
            if (!empty($params['cuId'])) {
                if ($params['cuId'] == -1) {
                    $isUseCoupon = false;
                }
            }
            // 优惠券
            if ($isUseCoupon) {
                $couponList = Db::name('couuser')
                    ->alias('a')
                    ->join(['tp_coupon'=>'b'],'a.couId=b.couId')
                    ->field('a.cuId, a.cuStatus, a.createTime, a.overTime, b.couMoney, b.couRuleValue, b.couRangeKey, b.couRangValue, b.couStartTime, b.couStopTime, b.couAging')
                    ->order('couRuleValue desc')
                    ->where($couponWhere)
                    ->select();
                $coupon = [];
                foreach ($couponList as $key => $item) {
                    if ($item['cuStatus'] == 2) {
                        if ($item['couAging'] == 1) {
                            if (time() < $item['couStartTime'] || time() > $item['couStopTime']) {
                                continue;
                            }
                        }
                        if ($item['couAging'] == 2) {
                            if (time() < $item['createTime'] || time() > $item['overTime']) {
                                continue;
                            }
                        }
                    }
                    
                    if ($item['couRangeKey'] == 1) { // 全场
                        if ($item['couRuleValue'] <= $data['totalMoney']) {
                            $coupon[] = $item;
                        }
                    }
                    if ($item['couRangeKey'] == 3) { // 单品
                        $cartList = Db::name('cart')->where(['userId' => $user['userId'], 'isSelected' => 1])->select();
                        foreach($cartList as $key => $item1) {
                            if ($item1['goodsId'] == $item['couRangValue']) {
                                $coupon[] = $item;
                            }
                        }
                    }
                }
                if (count($coupon) > 0) {
                    $data['coupon'] = $coupon[0];
                    $data['coupon']['couText'] = '满'.$coupon[0]['couRuleValue'].'减'.$coupon[0]['couMoney'];
                    $data['payMoney'] = $data['payMoney'] - $data['coupon']['couMoney'];
                    if ($data['payMoney'] <= 0) {
                        $data['payMoney'] = 0.1;
                    }
                } else {
                    $data['coupon'] = null;
                }
            } else {
                $data['coupon'] = null;
            }
            
            
            // dump($couuser);
           
            Db::commit();
            return $data;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/add 1.4 订单提交
     * @apiName orderAdd
     * @apiGroup h5OrderGroup
     * @apiParam {Number} [userNo] 邀请人的userNo
     * @apiParam {Number} [cuId] 优惠券Id
     * @apiParam {Number} addressId 地址唯一id
     * @apiParam {Number} payType 支付方式：1支付宝，2微信
     * @apiParam {String} [orderRemark] 订单备注
     * @apiParam {Array} goodsList 订单商品列表
     * @apiParam {Number} .goodsId 商品id
     * @apiParam {Number} .goodsNum 商品数量
     * @apiVersion 1.0.0
     */
    public function add($params) {
        $user = $this->getUserInfo();
        $userId = $this->getUserId();
        // $viewFrom = $this->getViewFrom();
        $headerParams = $this->getHeaderParams();
        $form['orderNo'] = '10'.date("YmdHis").rand(1000,9999);
        $form['userId'] = $userId;
        $form['createTime'] = time();
        $form['orderFrom'] = $headerParams['from'];
        $form['orderApp'] = $headerParams['app'];
        $form['orderStatus'] = 1;
        $goodsList = $params['goodsList'];

        $form['totalMoney'] = 0;

        // 根据地址id查地址详情
        $where = [
            'userId' => $userId,
            'addressId' => $params['addressId']
        ];
        $address = db('userAddress')->where($where)->find();
        $form['provinceId'] = $address['provinceId'];
        $form['provinceName'] = $address['provinceName'];
        $form['cityId'] = $address['cityId'];
        $form['cityName'] = $address['cityName'];
        $form['countyId'] = $address['countyId'];
        $form['countyName'] = $address['countyName'];
        $form['userAddress'] = $address['address'];
        $form['userPhone'] = $address['userPhone'];
        $form['userName'] = $address['userName'];
        $form['payType'] = $params['payType'];
        if (!empty($params['orderRemark'])) {
            $form['orderRemark'] = $params['orderRemark'];
        }

        // 生成订单剩余时间
        $form['remainingTime'] = strtotime(date("Y-m-d H:i:s", strtotime('+15 minute')));

        
        if (!empty($params['userNo'])) {
            $inviterUser = db('user')->where('userNo', $params['userNo'])->field('userId')->find();
            $form['beneficiaryUserId'] = $inviterUser['userId'];
            $form['isInvitation'] = 1;
        } else {
            $superior = db('user')->where('userId', $userId)->field('superiorId')->find();
            $form['beneficiaryUserId'] = $superior['superiorId'];
            $form['isInvitation'] = 0;
        }


        foreach($goodsList as $item) {
            $goodsInfo = db('goods')->where('goodsId', $item['goodsId'])->find();

            if (empty($item['skuId'])) {
                if ($user['userType'] == 1) {
                    $form['totalMoney']+= $goodsInfo['shopPrice'] * $item['goodsNum'];
                } else {
                    $form['totalMoney']+= $goodsInfo['memberPrice'] * $item['goodsNum'];
                }
            } else {
                $goodsSku = Db::name('goods_sku')->where('skuId', $item['skuId'])->find();
                if ($user['userType'] == 1) {
                    $form['totalMoney']+= $goodsSku['shopPrice'] * $item['goodsNum'];
                } else {
                    $form['totalMoney']+= $goodsSku['memberPrice'] * $item['goodsNum'];
                }
            }
           

            // 判断是否有会员产品，设置该订单为升级会员的订单
            $isMemberGoods=+ $goodsInfo['isMemberGoods'];
            if ($isMemberGoods > 0) {
                $form['isMemberGoods'] = 1;
            } else {
                $form['isMemberGoods'] = 0;
            }
            // 临时处理
            $app_env = config('app.app_env');
            if ($app_env == 'production') {
                if ($goodsInfo['goodsId'] == 143) {
                    $form['isMemberGoods'] = 1;
                }
            }
            if ($app_env == 'test') {
                if ($goodsInfo['goodsId'] == 139) {
                    $form['isMemberGoods'] = 1;
                }
            }
            // 判断是否有秒杀产品，设置该订单为秒杀的订单
            $isSeckillGoods=+ $goodsInfo['isSeckillGoods'];
            if ($isSeckillGoods > 0) {
                $form['isSeckillGoods'] = 1;
            } else {
                $form['isSeckillGoods'] = 0;
            }
        }

        if (!empty($params['cuId'])) {
            $coupon = Db::name('couuser')
                ->alias('a')
                ->join(['tp_coupon'=>'b'],'a.couId=b.couId')
                ->field('a.cuId,a.cuStatus,a.overTime, b.couMoney, b.couRuleValue, b.couRangeKey')
                ->order('couRuleValue desc')
                ->where(['userId' => $user['userId'], 'cuId' => $params['cuId']])
                ->find();
            if ($coupon['cuStatus'] == 4) {
                $this->error = '您选择的优惠券已经使用过了';
                return;
            }
            // 注意的几点：1、优惠券过期时间，要根据overTime计算，不能根据couStopTime来，因为coupon表里，会被多次使用，活动时间也会有变化。
            if ($coupon['overTime'] < time()) {
                $this->error = '您选择的优惠券已经过期了';
                return;
            }
            
            // if ($coupon['couMoney'] > $form['totalMoney']) {
                // $this->error = '您选择的优惠券不符合，请重新选择';
                // return;
            // }
            $form['couponId'] = $params['cuId'];
            $form['couponMoney'] = $coupon['couMoney'];
        }
   
        // 获取该用户的上级等级信息
        $superiorUser = Db::name('user')->where('userId', $user['superiorId'])->find();
        $levelInfo = $this->getUserLevel($superiorUser['userType']);
        Db::startTrans();
        try {
            // 避免重复提交
            $whereOrder[] = ['userId', '=', $userId];
            $whereOrder[] = ['totalMoney', '=', $form['totalMoney']];
            $whereOrder[] = ['orderStatus', '=', 1];

            $isOrder = Db::name('order')->where($whereOrder)->find();
            if ($isOrder) {
                $orderGoodsList = Db::name('order_goods')->where('orderId', $isOrder['orderId'])->select();
                $orderGoods = [];
                $paramsOrderGoods = [];
                foreach($orderGoodsList as $key => $item) {
                    $orderGoods[] = [
                        'goodsId' => $item['goodsId'],
                        'goodsNum' => $item['goodsNum']
                    ];
                }
                foreach($goodsList as $key => $item) {
                    $paramsOrderGoods[] = [
                        'goodsId' => $item['goodsId'],
                        'goodsNum' => $item['goodsNum']
                    ];
                }
                if ($paramsOrderGoods == $orderGoods) {
                    if ($isOrder['createTime'] + 60 > time()) {
                        return [
                            'message' => '添加成功',
                            'orderNo'=> $isOrder['orderNo'],
                            'payType'=> $isOrder['payType'],
                            'orderId' => $isOrder['orderId']
                        ];
                    }
                }
                
            }

            // 添加到订单主表
            $orderId = Db::name('order')->insertGetId($form);
            if (!empty($params['cuId'])) {
                Db::name('couuser')->where('cuId', $params['cuId'])->update(['cuStatus' => 4]);
            }
            $goodsForm = [];
            $commissionMoney = 0;
            $freeShippingCount = 0; // 包邮商品的数量
            $saveMoneyCount = 0; // 节约金额总数
            
            foreach($goodsList as $item) {
                $goodsInfo = Db::name('goods')->where('goodsId', $item['goodsId'])->find();
                if (!$goodsInfo) {
                    return '该商品不存在';
                }
                if ($goodsInfo['isFreeShipping'] == 1) {
                    $freeShippingCount += 1;
                }

                if (empty($item['skuId'])) {
                    if ($user['userType'] == 1) {
                        $shopPrice = $goodsInfo['shopPrice'];
                    } else {
                        $shopPrice = $goodsInfo['memberPrice'];
                    }
                } else {
                    $goodsSku = Db::name('goods_sku')->where('skuId', $item['skuId'])->find();
                    if ($user['userType'] == 1) {
                        $shopPrice = $goodsSku['shopPrice'];
                    } else {
                        $shopPrice =  $goodsSku['memberPrice'];
                    }
                }
                
                
                
                $list = [
                    'goodsId' => $item['goodsId'],
                    'goodsNum' => $item['goodsNum'],
                    'orderId' => $orderId,
                    'goodsName' => $goodsInfo['goodsName'],
                    'goodsImage' => $goodsInfo['goodsImage'],
                    // 'goodsAttrML' => $goodsInfo['goodsAttrML'],
                    // 'goodsAttrUnit' => $goodsInfo['goodsAttrUnit'],
                    'goodsPrice' => $shopPrice,
                    'saveMoney' => 0,
                    'createTime' => time(),
                    'skuJson' => null,
                    'skuSpec' => null
                ];
                
                if ($item['skuId']) {
                    $skuJson = Db::name('goods_sku')->where('skuId', $item['skuId'])->find();
                    $skuJson['skuSpec'] = json_decode($skuJson['skuSpec']);
                    $list['skuJson'] = json_encode($skuJson, 320);
                    $skuSpec = $skuJson['skuSpec'];
                    $list['skuSpec'] = json_encode($skuSpec, 320);
                }

                /**
                 * 1.会员邀请非会员购买商品，获取佣金
                 * 直属会员购买商品，上级获取佣金收益，25%
                 * （某商品非会员价25元，会员价15元）
                 * 以上面的例子。你的直属会员购买商品，他在该商品上省了10元。10*25%=2.5元
                 */
                // if (!empty($params['inviterUserId']) || $user['userType'] == 1) {
                //     // 计算商品单个佣金
                //     $list['commissionMoney'] = $goodsInfo['shopPrice'] - $goodsInfo['memberPrice'];

                //     // 计算订单总佣金
                //     $commissionMoney += $list['commissionMoney'];
                // }

                /**
                 * 2.会员级别以上的下面的直属会员购买商品，获取佣金
                 */
                // if ($user['userType'] == 2 || $user['userType'] == 3 || $user['userType'] == 4) {
                    // $list['commissionMoney'] = $goodsInfo['memberPrice'] * ($levelInfo['memberGoodsCommission'] / 100);
                    // $list['saveMoney'] = 0;
                    if ($goodsInfo['memberPrice'] < $goodsInfo['shopPrice']) {
                        $list['saveMoney'] = ($goodsInfo['shopPrice'] - $goodsInfo['memberPrice']) * $item['goodsNum'];
                    }
                    // $commissionRate = $levelInfo['memberGoodsCommission'];
                    // $commissionMoney += $list['commissionMoney'];
                    $saveMoneyCount += $list['saveMoney'];
                // }
                
                $goodsForm[] = $list;
            }
            // 添加到订单商品表
            Db::name('order_goods')->insertAll($goodsForm);
            // 更新订单表的值
            $config = Db::name('web_config')->find();
            $orderUpdate = [];
            // $orderUpdate['commissionMoney'] = $commissionMoney;
            $orderUpdate['saveMoney'] = $saveMoneyCount;
            // 情况1：用户满足了，满xx元包邮
            if ($form['totalMoney'] < $config['freeShippingMoney']) {
                $orderUpdate['deliverMoney'] = $config['deliverMoney'];
            } else {
                $orderUpdate['deliverMoney'] = 0;
            }
            
            // 情况2：用户买了包邮的商品
            if ($freeShippingCount > 0) {
                $orderUpdate['deliverMoney'] = 0;
            }
            $orderUpdate['payableMoney'] = $form['totalMoney'] + $orderUpdate['deliverMoney'];
            if (!empty($params['cuId'])) {
                $orderUpdate['payableMoney'] = $orderUpdate['payableMoney'] - $coupon['couMoney'];
                if($orderUpdate['payableMoney'] <= 0) {
                    $orderUpdate['payableMoney'] = 0.1;
                }
            }
            /**
             * 佣金类型，区分
             * 1、商品佣金，用户正常购买了的奖励
             * 2、邀请返现，第一次成为会员的时候，上级奖励，上上级是经理或总监，也有奖励
             * 3、分享返现，需要带inviterUserId，进行区分
             * 4、团队返现，当前用户的上级是经理或总监，上上级是经理或总监
             */
            // 类型3
            // if (!empty($params['inviterUserId'])) {
            //     $orderUpdate['commissionType'] = 3;
            // }
            Db::name('order')->where('orderId', $orderId)->update($orderUpdate);
            // 清空购物车
            Db::name('cart')->where(['userId' => $userId, 'isSelected' => 1])->delete();
            Db::commit();
            return [
                'message' => '添加成功',
                'orderNo'=>$form['orderNo'],
                'payType'=>$form['payType'],
                'orderId' => $orderId
            ];
        } catch (\Exception $e) {
            trace('订单提交：'.$e->getMessage(), 'error');
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/pay 1.5 订单支付
     * @apiName payOrder
     * @apiGroup h5OrderGroup
     * @apiParam {Number}   orderNo 订单No
     * 
     * @apiVersion 1.0.0
     */
    public static function payOrder($params) {
        // $user = $this->getUserInfo();
        // trace('错误信息','error');
        Db::startTrans();
        try {
            $order = Db::name('order')->where('orderNo', $params['orderNo'])->find();
            $user = Db::name('user')->where('userId', $order['userId'])->find();
            // if ($order['orderStatus'] == 2) {
            //     $this->error = '该笔订单已经支付过了';
            //     return;
            // }

            // 判断是否是首次购买抢购商品
            $isSeckillGoods = false;
            if ($order['isSeckillGoods'] == 1) {
                $seckillGoodsCount = Db::name('order')->where([
                    ['userId', '=', $user['userId']],
                    ['orderStatus', 'in', [2,3,4]],
                    ['isMemberGoods', '=', 1]
                ])->count();
                if ($seckillGoodsCount == 0) {
                    $isSeckillGoods = true;
                } else {
                    $isSeckillGoods = false;
                }
            }

            $update = [
                'orderStatus' => 2,
                'paymentTime' => time(),
                'payMoney' => $params['payMoney'],
                'payType' => $params['payType']
            ];
            $data = Db::name('order')->where('orderNo', $params['orderNo'])->update($update);
            if ($order['isMemberGoods'] == 1 && $user['auditStatus'] == 0) {
                Db::name('user')->where('userId', $user['userId'])->update(['auditStatus' => 1]);
            }

            /**
             * 支付成功，受益人佣金表里生成数据
             */
            $order = Db::name('order')->where('orderNo', $params['orderNo'])->find();
            $beneficiaryUser = Db::name('user')->where('userId', $order['beneficiaryUserId'])->find();
            $levelInfo =  Db::name('user_level')->where('userType', $beneficiaryUser['userType'])->find();
            // 判断当前用户是不是普通用户，如果是普通用户，进行全额分佣
            // 判断是否有邀请人
            
            /**
             * 来源类型
             */
            $isAdd = false;
            // 是会员产品，并且没有买过
            if ($order['isMemberGoods'] == 1 && $user['isBuyMemberGoods'] == 0) {
                $isAdd = true;
                $commissionType = 2;
                $userCommissionMoney = $levelInfo['directlyMemberMoney'];
                $userCommissionRate = 0;
                
            }

            // 是会员产品，已经买过了
            if ($order['isMemberGoods'] == 1 && $user['isBuyMemberGoods'] == 1) {
                $isAdd = true;
                $commissionType = 1;
                $userCommissionRate = $levelInfo['memberGoodsCommission'];
                $userCommissionMoney = $order['saveMoney'] * ($levelInfo['memberGoodsCommission'] / 100);
            }

            // h5购买商品
            if ($order['isMemberGoods'] == 0) {
                $isAdd = true;
                if ($order['isInvitation'] == 1) { // 判断是不是邀请进来的
                    $commissionType = 3;
                    $userCommissionRate = $levelInfo['memberGoodsCommission'];
                    if ($user['userType'] == 1 && $beneficiaryUser['userType'] != 1) {
                        $userCommissionMoney = $order['saveMoney'];
                    } else {
                        $userCommissionMoney = $order['saveMoney'] * ($levelInfo['memberGoodsCommission'] / 100);
                    }
                } else {// 普通购买
                    $commissionType = 1;
                    $userCommissionRate = $levelInfo['memberGoodsCommission'];
                    if ($user['userType'] == 1 && $beneficiaryUser['userType'] != 1) {
                        $userCommissionMoney = $order['saveMoney'];
                    } else {
                        $userCommissionMoney = $order['saveMoney'] * ($levelInfo['memberGoodsCommission'] / 100);
                    }
                }
            }
            $userCommissionMoney = floor($userCommissionMoney * 100) / 100;
            if ($isAdd && $order['isSeckillGoods'] == 0) {
                $commissionForm = [
                    'orderId' => $order['orderId'],
                    'userId' => $user['userId'],
                    'orderMoney' => $order['payMoney'],
                    'beneficiaryUserId' => $order['beneficiaryUserId'],
                    'commissionMoney' => $userCommissionMoney,
                    'commissionRate' => $userCommissionRate,
                    'commissionType' => $commissionType,
                    'commissionStatus' => 1,
                    'createTime' => time()
                ];
                Db::name('commission')->insert($commissionForm);
            }

            

            // 查询用户上上级，分佣金
            if ($beneficiaryUser['superiorId']) { //　判断是否有上级
                $superior2 = Db::name('user')->where('userId', $beneficiaryUser['superiorId'])->find();
                $levelInfo2 = Db::name('user_level')->where('userType', $superior2['userType'])->find();
                /**
                 * 分2种情况
                 * 分支1：首次购买会员产品分佣
                 * 分支2：后续购买会员产品分佣，上上不分佣金
                 * 分支3：普通产品分佣
                 */
                /**
                 * 分支1
                 * 情况1：上级是会员，上上级是经理或总监（普通分佣）
                 * 情况2：上级是经理或总监，上上级是经理或总监（团队分佣），2018/10/10，需求：不需要团队分佣
                 * 情况3：上级是总监，上上级是经理（不享有分佣）
                 * 
                 */
                if ($order['isMemberGoods'] == 1 && $user['isBuyMemberGoods'] == 0 && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) { // 判断会员产品，并且没有买过
                    $commissionType = 2; // 邀请返现
                    $superior2CommissionRate = 0;
                    $next = true;
                    // 情况1
                    if ($beneficiaryUser['userType'] == 2 && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                        $superior2CommissionMoney = $levelInfo2['teamAddMemberCommission'];
                    }
                    // 情况2
                    if(($beneficiaryUser['userType'] == 3 || $beneficiaryUser['userType'] == 4) && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                        $superior2CommissionMoney = $levelInfo2['managerTeamAddMemberCommission'];
                        // $commissionType = 4;
                    }
                    // 情况3
                    if($beneficiaryUser['userType'] == 4 && $superior2['userType'] == 3) {
                        $next = false;
                    }
                    if($beneficiaryUser['userType'] == 4 && $superior2['userType'] == 4) {
                        $superior2CommissionMoney = $levelInfo2['directorTeamAddMemberCommission'];
                    }
                    $superior2CommissionMoney = floor($superior2CommissionMoney * 100) / 100;
                    if ($next && $order['isSeckillGoods'] == 0) {
                        $superior2Form = [
                            'orderId' => $order['orderId'],
                            'userId' => $user['userId'],
                            'orderMoney' => $order['payMoney'],
                            'beneficiaryUserId' => $superior2['userId'],
                            'commissionStatus' => 1,
                            'createTime' => time(),
                            'commissionType' => $commissionType,
                            'commissionRate' => $superior2CommissionRate,
                            'commissionMoney' => $superior2CommissionMoney
                        ];
                        Db::name('commission')->insert($superior2Form);
                    }
                    
                }
                
                /**
                 * 分支2
                 */
                // if ($order['isMemberGoods'] == 1 && $user['isBuyMemberGoods'] == 1 && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                    // 情况2
                    // $next2 = false;
                    // if ($beneficiaryUser['userType'] == 2 && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                    //     $next2 = true;
                    //     $superior2CommissionMoney = $order['saveMoney'] * ($levelInfo2['directlyMemberCommission'] / 100);
                    //     $superior2CommissionRate = $levelInfo2['directlyMemberCommission'];
                    //     $commissionType = 1;
                    // }
                    // 情况3
                    // if(($beneficiaryUser['userType'] == 3 || $beneficiaryUser['userType'] == 4) && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                    //     // 这里需要把上级的佣金拿到，再乘团队分佣比例
                    //     $superior2CommissionMoney = $userCommissionMoney * ($levelInfo2['directlyManagerCommission'] / 100);
                    //     $superior2CommissionRate = $levelInfo2['directlyManagerCommission'];
                    //     $commissionType = 4;
                    // }
                //     if ($next2) {
                //         $superior2Form = [
                //             'orderId' => $order['orderId'],
                //             'userId' => $user['userId'],
                //             'orderMoney' => $order['payMoney'],
                //             'beneficiaryUserId' => $superior2['userId'],
                //             'commissionStatus' => 1,
                //             'createTime' => time(),
                //             'commissionType' => $commissionType,
                //             'commissionRate' => $superior2CommissionRate,
                //             'commissionMoney' => $superior2CommissionMoney
                //         ];
                //         Db::name('commission')->insert($superior2Form);
                //     }
                // }

                /**
                 * 判断用户的上级，和上上级都是经理或总监，是的话进行团队分佣
                 * 分几种情况：
                 * 情况1：上级是会员，经理，总监，上上级是会员（直属会员分佣1级，上上级不参与分佣）
                 * 情况2：上级是会员，上上级是经理或总监（直属会员分佣，2级）
                 * 情况3：上级是经理或总监，上上级是经理或总监（直属经理团队分佣）
                 */
                // 情况1，上上级如果是会员不处理
                if ($order['isMemberGoods'] == 0 && $order['isSeckillGoods'] == 0 && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                    // 情况2
                    if ($beneficiaryUser['userType'] == 2 && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                        $superior2CommissionMoney = $order['saveMoney'] * ($levelInfo2['directlyMemberCommission'] / 100);
                        $superior2CommissionRate = $levelInfo2['directlyMemberCommission'];
                        $commissionType = 1;
                    }
                    // 情况3
                    if(($beneficiaryUser['userType'] == 3 || $beneficiaryUser['userType'] == 4) && ($superior2['userType'] == 3 || $superior2['userType'] == 4)) {
                        // 这里需要把上级的佣金拿到，再乘团队分佣比例
                        $superior2CommissionMoney = $userCommissionMoney * ($levelInfo2['directlyManagerCommission'] / 100);
                        $superior2CommissionRate = $levelInfo2['directlyManagerCommission'];
                        $commissionType = 4;
                    }
                    $superior2CommissionMoney = floor($superior2CommissionMoney * 100) / 100;
                    $superior2Form = [
                        'orderId' => $order['orderId'],
                        'userId' => $user['userId'],
                        'orderMoney' => $order['payMoney'],
                        'beneficiaryUserId' => $superior2['userId'],
                        'commissionStatus' => 1,
                        'createTime' => time(),
                        'commissionType' => $commissionType,
                        'commissionRate' => $superior2CommissionRate,
                        'commissionMoney' => $superior2CommissionMoney
                    ];
                    Db::name('commission')->insert($superior2Form);
                }
                
                
            }

            

            // 升级会员走这里，设置用户黄金的等级，发送短信
            if ($order['isMemberGoods'] == 1 || $order['isSeckillGoods'] == 1) {
                if ($user['userType'] == 1 && $user['isBuyMemberGoods'] == 0) {
                    
                    Db::name('user')->where('userNo', $user['userNo'])->update([
                        'isBuyMemberGoods' => 1, 
                        'userType' => 2, 
                        'memberTime' => time(),
                        'auditStatus' => 2,
                        'auditTime' => time()
                    ]);
                    $userTypeName = getStatusName('userType', 2);
                    if ($order['orderFrom'] != 1) {
                        $userToken = $this->getTokenArray($user['userId']);

                        // 升级消息推送
                        $push = new Jgsdk();
                        $m_type = 'https';//推送附加字段的类型
                        $m_txt = '';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
                        $m_time = '86400';//离线保留时间
                        $receive = $userToken;
                        $content = '尊敬的用户'.$user['userName'].'恭喜您正式升级为'.$userTypeName.'感谢您的支持';
                        $message="";//存储推送状态
                        $extras = [
                            'type' => 0
                        ];
                        $push->push($receive,$content,$m_type,$m_txt,$m_time,$extras);

                        // 通知他上级
                        
                        $userSuperior = Db::name('user')->where('userId', $order['beneficiaryUserId'])->find();
                        $userSuperiorToken = $this->getTokenArray($userSuperior['userId']);
                        $receive1 = $userSuperiorToken;
                        $content1 = '恭喜您邀请的用户'.$user['userName'].'，成为了'.$userTypeName;
                        if ($userSuperior['userFrom'] == 2) {
                            $extras1 = [
                                'type' => 1,
                                'page' => ''
                            ];
                        }
                        if ($userSuperior['userFrom'] == 3) {
                            $extras1 = [
                                'type' => 1,
                                'page' => ''
                            ];
                        }
                        if ($userSuperior['userFrom'] == 2 || $userSuperior['userFrom'] == 3) {
                            $push->push($receive1,$content1,$m_type,$m_txt,$m_time,$extras1);
                        }
                        
                        // 短信推送
                        $Sms = new Sms();
                        $smsParams = [
                            'name' => $userSuperior['userName'],
                            'invitername' => $user['userName']
                        ];
                        $response = $Sms->sendSms($userSuperior['userPhone'], $smsParams, 'SMS_150183052', $order['orderFrom']);

                    }
                    // 短信推送
                    $Sms = new Sms();
                    $smsParams = [
                        'name' => $user['userName'],
                        'identity' => $userTypeName
                    ];
                    $response = $Sms->sendSms($order['userPhone'], $smsParams, 'SMS_150172948', $order['orderFrom']);
                }
            }

            if ($isSeckillGoods) {

                $commissionSeckill = [
                    'orderId' => $order['orderId'],
                    'userId' => $user['userId'],
                    'orderMoney' => $order['payMoney'],
                    'beneficiaryUserId' => $order['beneficiaryUserId'],
                    'commissionMoney' => 9.9,
                    'commissionRate' => 0,
                    'commissionType' => 2,
                    'commissionStatus' => 1,
                    'createTime' => time()
                ];
                Db::name('commission')->insert($commissionSeckill);
            }
            
            $commissionList = Db::name('commission')->where('orderId', $order['orderId'])->select();
            foreach($commissionList as $item) {
                $superior = Db::name('user')->where('userId', $item['beneficiaryUserId'])->find();
                $superiorUpdate = [
                    'freezeAmount' => $superior['freezeAmount'] + $item['commissionMoney']
                ];
                $userData = Db::name('user')->where('userId', $item['beneficiaryUserId'])->update($superiorUpdate);
            }

            // 更新产品销量,更新产品库存
            $orderGoods = Db::name('order_goods')->where('orderId', $order['orderId'])->select();
            foreach($orderGoods as $item) {
                Db::name('goods')->where('goodsId', $item['goodsId'])->setInc('saleCount', $item['goodsNum']);
                Db::name('goods')->where('goodsId', $item['goodsId'])->setDec('goodsStock', $item['goodsNum']);
            }

            

            Db::commit();

            // 成为会员发送优惠券
            if ($order['isMemberGoods'] == 1) {
                $Coupon = new Coupon();
                $CouponParams = [
                    'idCode' => 'becomeMember'
                ];
                $result = $Coupon->ExchangeCoupon($CouponParams, $user['userId']);
                if (isset($result['error'])) {
                    trace('发放优惠券错误：'.$result['error'].'，订单编号：'.$order['orderNo'], 'error');
                }
            }

            // 成为会员发送优惠券
            if ($isSeckillGoods) {
                $Coupon = new Coupon();
                $CouponParams = [
                    'idCode' => 'HM-goodsSeckill'
                ];
                $result = $Coupon->ExchangeCoupon($CouponParams, $user['userId']);
                if (isset($result['error'])) {
                    trace('发放优惠券错误：'.$result['error'].'，订单编号：'.$order['orderNo'], 'error');
                }
            }
            

            return true;
        } catch (\Exception $e) {
            // output_log_file('支付成功处理，错误信息：'.$e->getMessage().'，订单号：'.$params['orderNo']);
            \think\facade\Log::record('支付成功处理，错误信息：'.$e->getMessage().'，订单号：'.$params['orderNo'], 'error');
            Db::rollback();
            Db::name('log_pay_error')->insert([
                'orderNo' => $params['orderNo'],
                'type' => 2,
                'error' => $e->getMessage(),
                'createTime' => time()
            ]);
            // $this->error = $e->getMessage();
            return true;
        }
    }
    /**
     * @api {post} /h5/order/list 2.1 用户订单列表
     * @apiName orderList
     * @apiGroup h5OrderGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} [orderStatus] 订单状态：1未付款 2待发货 3已发货 4交易完成 5退款中 6已退款 7已取消<br/>传0，null，不传，都可以查全部数据
     * @apiSuccess {Array} list 见订单表
     * @apiSuccess {Number} .isRefund 是否可退货，0不，1是
     * @apiVersion 1.0.0
     */
    public function orderList($params) {
        $userId = $this->getUserId();
        if (!empty($params['orderStatus'])) {
            $where['a.orderStatus'] = $params['orderStatus'];
            $where1['orderStatus'] = $params['orderStatus'];
        }
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        try {
            $where['a.userId'] = $userId;
            $data['list'] = $this->alias('a')
                ->where($where)
                ->leftJoin('order_refund b', 'a.orderId = b.orderId')
                ->field('a.*, b.refundId')
                ->page($params['page'], $params['size'])
                ->order('orderId', 'desc')
                ->select();
                
                
                
            $where1['userId'] = $userId;
            $data['count'] = $this->where($where1)->count();
            foreach($data['list'] as $key => $item) {
                $goodsList = Db::name('order_goods')->where(['orderId' => $item['orderId']])->select();
                foreach($goodsList as $key1 => $item1) {
                    if ($item1['skuSpec']) {
                        $skuSpec = json_decode($item1['skuSpec']);
                        $goodsList[$key1]['skuSpec'] = implode("，", $skuSpec);
                    }
                }
                $item['goodsList'] = $goodsList;
                $item['orderStatusName'] = getStatusName('orderStatus', $item['orderStatus']);
                if ($item['orderStatus'] == 2 || $item['orderStatus'] == 3) {
                    $item['isRefund'] = 1;
                }
                if ($item['orderStatus'] == 4) {
                    if ($item['refundAppleTime'] > time()) {
                        $item['isRefund'] = 1;
                    } else {
                        $item['isRefund'] = 0;
                    }
                }
            }
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/cancel 2.2 用户订单取消
     * @apiName cancelOrder
     * @apiGroup h5OrderGroup
     * @apiParam {Number} orderId 订单id
     * @apiVersion 1.0.0
     */
    public function cancelOrder($params) {
        $userId = $this->getUserId();
        
        try {
            $where = [
                'userId' => $userId,
                'orderId' => $params['orderId']
            ];
            $order = $this->where($where)->find();
            if ($order['orderStatus'] == 1) {
                $data = $this->where($where)->update(['orderStatus' => 7]);
                Db::name('couuser')->where('cuId', $order['couponId'])->update(['cuStatus' => 2]);
                if ($data) {
                    return [
                        'message' => '取消成功'
                    ];
                } else {
                    $this->error = '取消失败';
                    return;
                }
            } else {
                $this->error = '订单只有在待付款的时候，才能取消订单';
                return;
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/confirm 2.3 用户订单确认收货
     * @apiName confirmOrder
     * @apiGroup h5OrderGroup
     * @apiParam {Number} orderId 订单id
     * @apiVersion 1.0.0
     */
    public function confirmOrder($params) {
        // $user = $this->getUserInfo();
        // 确认收货后的设置
        $data = $this->setOrderConfirm($params['orderId']);
        if ($data) {
            return $data;
        }
    }
    /**
     * @api {post} /h5/order/info 2.4 用户订单详情
     * @apiName orderInfo
     * @apiGroup h5OrderGroup
     * @apiParam {Number} orderId 订单id
     * @apiVersion 1.0.0
     */
    public function getOrderInfo($params) {
        $userId = $this->getUserId();
        
        try {
            $where = [
                'userId' => $userId,
                'orderId' => $params['orderId']
            ];
            $order['info'] = $this->where($where)->find();
            if (!$order['info']) {
                $this->error = '该订单不存在';
                return;
            }
            $order['info']['address'] = $order['info']['userAddress'];
            $order['info']['orderStatusName'] = getStatusName('orderStatus', $order['info']['orderStatus']);
            $goodsList = db('orderGoods')->where(['orderId' => $order['info']['orderId']])->select();
            foreach($goodsList as &$item) {
                if ($item['skuSpec']) {
                    $skuSpec = json_decode($item['skuSpec']);
                    $item['skuSpec'] = implode("，", $skuSpec);
                }
            }
            $order['info']['goodsList'] = $goodsList;
            return $order;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/delete 2.5 用户订单删除
     * @apiName deleteOrder
     * @apiGroup h5OrderGroup
     * @apiParam {Number} orderId 订单id
     * @apiVersion 1.0.0
     */
    public function deleteOrder($params) {
        $userId = $this->getUserId();
        
        try {
            $where = [
                'userId' => $userId,
                'orderId' => $params['orderId']
            ];
            $order = $this->where($where)->find();
            if (!$order) {
                $this->error = '该订单不存在';
                return;
            }
            if ($order['orderStatus'] != 7) {
                $this->error = '订单只有在取消的状态下，才能删除';
                return;
            }
            $data = $this->where($where)->delete();
            if ($data == 1) {
                return [
                    'message' => '删除成功'
                ];
            } else {
                return [
                    'message' => '删除失败'
                ]; 
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/order/delete 2.6 用户订单数量
     * @apiName getOrderCount
     * @apiGroup h5OrderGroup
     * @apiVersion 1.0.0
     */
    public function getOrderCount() {
        $user = $this->getUserInfo();
        try {
            $order = Db::name('order')->field('COUNT()')->where($userId, $user['userId'])->find();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}