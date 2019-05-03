<?php 
// 用户
namespace app\h5\controller;
use think\Controller;
use wechat\Jssdk;
use Naixiaoxin\ThinkWechat\Facade;

class Wechat extends Controller {
    public function signature() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Wechat.signature');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $app = Facade::officialAccount();
        $app->jssdk->setUrl($params['url']);
        // dump($app);
        $data = $app->jssdk->buildConfig(['updateAppMessageShareData', 'updateTimelineShareData'], $debug = false, $beta = false, $json = true);

        // echo $params['url'];
        // $jssdk = new Jssdk($params['url']);
        // $data = $jssdk->getSignPackage();
        return resultArray(['data' => json_decode($data)]);
    }

    public function oauth() {
        $app = Facade::officialAccount();
        // dump($app);
        // $app->oauth->setUrl('http://shaoky.mynatapp.com/index');
        $data = $app->oauth->scopes(['snsapi_userinfo'])->redirect();
        return $data->send();
    }
}