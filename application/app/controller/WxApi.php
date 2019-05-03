<?php 
// 用户
namespace app\app\controller;
use think\Controller;
use Naixiaoxin\ThinkWechat\Facade;
use EasyWeChat\Factory;

class WxApi extends Controller {
    public function selectOrder () {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.selectOrder');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }

        
        
    }
}