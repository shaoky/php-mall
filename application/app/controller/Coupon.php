<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:22
 */

namespace app\app\controller;


class Coupon extends ApiCommon
{
    public function getSelfCoupon(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->getSelfCoupon($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }

    public function getUsableCoupon(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->getUsableCoupon($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }

    public function ExchangeCoupon(){
        $params = input('post.');
        $coupon = model('coupon');
        $validate = $this->validate($params, 'app\app\validate\Coupon.ExchangeCoupon');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $result = $coupon->ExchangeCoupon($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }

    public function checkcCoupon(){
        $coupon = model('coupon');
        $coupon->CheckCoupon();
    }
    public function getLog(){
        $coupon = model('coupon');
        $coupon->getLog();
    }
}