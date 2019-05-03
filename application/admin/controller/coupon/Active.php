<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/13
 * Time: 17:22
 */

namespace app\admin\controller\coupon;


use app\admin\controller\ApiCommon;

class Active extends ApiCommon
{
    public function Index()
    {
        $params = input('post.');
//        $validate = $this->validate($params, 'app\admin\validate\Ad');
//        if ($validate !== true) {
//            return resultArray(['error' => $validate]);
//        }
        $coupon = model('coupon_active');
        $result = $coupon->getList($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }

    public function addCouponActive(){
        $params = input('post.');
        $coupon = model('coupon_active');
        $result = $coupon->addCouponActive($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function updateCouponActive(){
        $params = input('post.');
        $coupon = model('coupon_active');
        $result = $coupon->updateCouponActive($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function updateStatusActive(){
        $params = input('post.');
        $coupon = model('coupon_active');
        $result = $coupon->updateStatusActive($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function deleteActive(){
        $params = input('post.');
        $coupon = model('coupon_active');
        $result = $coupon->deleteActive($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function getRedeem(){
        $params = input('post.');
        $coupon = model('coupon_active');
        $result = $coupon->getRedeem($params);
        if (!$result) {
            return resultArray(['error' => $coupon->getError()]);
        }
        return resultArray(['data' => $result]);
    }
}