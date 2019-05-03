<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/22
 * Time: 10:13
 */

namespace app\admin\controller\coupon;


use think\Controller;

class CouponCount extends Controller
{
    public function getList(){
        $params = input("post.");
        $model = model('CouponCount');
        $result = $model->getList($params);
        if (!$result){
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
}