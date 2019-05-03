<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/13
 * Time: 14:09
 */

namespace app\admin\controller\coupon;


use app\admin\controller\ApiCommon;

class Index extends ApiCommon
{
    public function Index(){
        $params =  input('post.');
//        $validate = $this->validate($params, 'app\admin\validate\Ad');
//        if ($validate !== true) {
//            return resultArray(['error' => $validate]);
//        }
        $coupon = model('coupon');
        $result = $coupon->getList($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function addCoupon(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->addCoupon($params);
        if (!$result){
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function updateCoupon(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->updateCoupon($params);
        if (!$result){
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function getAttrGoods(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->getAttrGoods($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function deleteCoupon(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->deleteCoupon($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function updateState(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->updateState($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function couponCount(){
        $params = input('post.');
        $coupon = model('coupon');
        $result = $coupon->getCouponCountList($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
}