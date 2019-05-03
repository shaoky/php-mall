<?php  
namespace app\comm\model;  
use think\Db;
use think\Model;
use app\comm\model\Common;

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
/**
 * @apiDefine commonGroup 通用接口
 */

Config::load();

/**
 * @api {post} /common/sms 1.1 发送短信
 * @apiName login
 * @apiGroup commonGroup
 * @apiParam {String} phone 手机号
 * @apiParam {Number} type 短信类型 1：登陆/注册
 * @apiSuccess {String} smsCode 短信码
 * @apiVersion 1.0.0
 */
class Sms extends Common {
    static $acsClient = null;

    public function add($params) {
        $headerParams = $this->getHeaderParams();
        $env = config('app.app_env');
        if ($env == 'production') {
            $smsCode = rand(1000,9999);
        } else {
            $smsCode = 1111;
        }
        
        $map = [
            'phone' => $params['phone'],
            'type' => $params['type'],
            'createTime' => time(),
            'smsCode' => $smsCode,
            'overdueTime' => strtotime(date("Y-m-d H:i:s", strtotime('+30 minute')))
        ];
        
        try {
            $SmsParams = [
                'code' => $smsCode
            ];
            
            if ($env == 'production') {
                $response = Sms::sendSms($params['phone'], $SmsParams, 'SMS_147935241', $headerParams['app']);
                if ($response->Code == 'OK') {
                    $data = $this->save($map);
                    if (!$data) {
                        $this->error = '获取短信失败';
                    }
                    return [
                        'message' => '发送成功'
                    ];
                } else {
                    $this->error = '发送失败';
                    return;
                }
            } else {
                $data = $this->save($map);
                return [
                    'message' => '发送成功'
                ];
            }
            
            
        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        } 
    }

    public static function getAcsClient() {
        //产品名称:云通信短信服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = ""; // AccessKeyId

        $accessKeySecret = ""; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return stdClass
     */
    public static function sendSms($tel, $SmsParams, $templateCode, $app) {
        if ($app == 1) { // 商城1
            $signName = '商城1';
        } else { // 商城2
            $signName = '商城2';
        }
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        //可选-启用https协议
        //$request->setProtocol("https");

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($tel);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($signName);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项

        // 短信模版
        // if ($templateCode == 'SMS_147935241') {
        //     $form = [
        //         'code' => $SmsParams['code']
        //     ];
        // }
        // 发货模版
        // if ($templateCode == 'SMS_147439039') {
            // $form = [
                // 'product' => $SmsParams['product']
            // ];
        // }

        // 升级模版
        // if ($templateCode == 'SMS_147970282') {
            // $form = [
                // 'name' => $SmsParams['name'],
                // 'identity' => $SmsParams['identity']
            // ];
        // }
        // dump($SmsParams);
        $request->setTemplateParam(json_encode($SmsParams), JSON_UNESCAPED_UNICODE);

        // 可选，设置流水号
        // $request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        // $request->setSmsUpExtendCode("1234567");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }
}