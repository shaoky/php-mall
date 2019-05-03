<?php

namespace Common\ORG\Util;

use Common\Core\Constant;
use JPush\Client;

/**
 * 极光推送
 * Class JPushUtil
 * @package extend\util
 */
class JPushUtil
{

    const PUSH_TYPE_B2B = 'B2B';
    const PUSH_TYPE_B2C = 'B2C';
    const PUSH_TYPE_CLOUD = 'CLOUD';
    const PUSH_TYPE_PLATFORM_ALL = 'all';
    const PUSH_TYPE_PLATFORM_IOS = 'ios';
    const PUSH_TYPE_PLATFORM_ANDROID = 'android';
    const PUSH_TYPE_PLATFORM_WINPHONE = 'winphone';
    const PUSH_APNS_PRODUCTION = true;

    const  ORDER_INVOICE = "您的订单已发货";
    const  ORDER_PAY = "有客户下单并已付款，请及时处理订单哦。";


    //通知模板
    const PUSH_ORDERS = '大掌柜的，有人下单了！请立即接单！';//用户下单通知
    const PUSH_RECEIVE_ORDER = '商家已接单，正在准备您的商品，请耐心等待。纵有千山万水，自难忘亲之订单。';//商家接单
    const PUSH_INVOICE = '商家已取得商品，正往赶往您的位置，请耐心等待。来也匆匆，去也匆匆，只差和你相逢！请耐心等候。';//商家发货
    const PUSH_CONFIRM_ORDER = '用户已确认收货，来日方长，江湖再会！';//确认收货
    const PUSH_BACK_ORDER = '用户提交了退款申请，赶紧查看。与其天涯思订单，恋恋不舍，莫若相忘于江湖。这单该退就退了吧。';//申请退款
    const PUSH_CANCEL_RECEIVE = '供应商很无奈的拒绝订单。江湖告急，还望海涵。';//拒单

    private $app_key;
    private $master_secret;
    private $client;

    /**
     * JPushUtil constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $config = D('Config')->queryConfigByGroup('JPush', $type);
        $this->app_key = $config['app_key'];
        $this->master_secret = $config['master_secret'];
        if (!$this->app_key && !$this->master_secret) {
            return false;
        }
        $this->client = new Client($this->app_key, $this->master_secret);
    }

    function __destruct()
    {

    }

    /**
     * 广播推送
     * @param string $platform 推送平台，使用常量
     * @param string $content 文本
     * @param $source 来源
     * @return mixed
     */
    public function pushPlatform($platform, $content, $source)
    {
        $param = array(
            'systype' => $source,
            'content' => $content
        );
        $pusher = $this->client->push();
        $pusher->setPlatform($platform);
        $pusher->addAllAudience();
        $pusher->setNotificationAlert($content);
        $pusher->options(array(
            //true:正式环境，false 开发环境；默认正式环境
            'apns_production' => self::PUSH_APNS_PRODUCTION,
            'sendno' => randomNumber(100000, PHP_INT_MAX)
        ));
        try {
            $send = $pusher->send();
            if ($send['http_code'] == 200) {
                $param['status'] = 'SUCCESS';
            } else {
                $param['msg'] = $send['http_code'];
                $param['status'] = 'ERROR';
            }
        } catch (\Exception $e) {
            $param['status'] = 'ERROR';
            $param['msg'] = $e;
        }
        return D('PushLog')->addPushLog($param);

    }

    /**
     *  单播推送TAG
     * @param array $tag 别名数组设备号
     * @param string $content 推送文本
     * @param array $extras 自定义消息数组
     * @return array
     */
    public function pushByTag($tag, $content, $extras)
    {
        $pusher = $this->client->push();
        $pusher->setPlatform(self::PUSH_TYPE_PLATFORM_ALL);
        $pusher->options(array(
            //true:正式环境，false 开发环境；默认正式环境
            'apns_production' => self::PUSH_APNS_PRODUCTION,
            'sendno' => randomNumber(100000, PHP_INT_MAX),
            'extras' => $extras
        ));
        $pusher->addTag($tag);
        $pusher->message($content, array(
            'extras' => $extras
        ));
        try {
            return $pusher->send();
        } catch (\Exception $e) {
            print false;
        }
    }

    /**
     *  单播推送ALIAS
     * @param array $alias 别名数组设备号
     * @param string $content 推送文本
     * @return array|string
     */
    public function pushByAlias($alias, $content, $extras = array())
    {
        $pusher = $this->client->push();
        $pusher->setPlatform(self::PUSH_TYPE_PLATFORM_ALL);
        $pusher->options(array(
            //true:正式环境，false 开发环境；默认正式环境
            'apns_production' => self::PUSH_APNS_PRODUCTION,
            'sendno' => randomNumber(100000, 100000000),
        ));
        $pusher->addAlias($alias);
        if ($extras) {
            $pusher->message($content, array(
                'extras' => $extras,
            ));
            $pusher->androidNotification($content, array(
                'extras' => $extras,
            ));
            $pusher->iosNotification($content, array(
                'extras' => $extras,
            ));
        }
        $pusher->setNotificationAlert($content);
        try {
            return $pusher->send();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 根据用户ID推送
     * @param string $member_id 用户ID
     * @param string $content 内容
     * @param string $source APP来源
     * @return bool
     */
    public function pushByMember($member_id, $content, $source)
    {
        try {
            $where = array(
                'member_id' => $member_id,
                'source' => $source
            );
            $tokens = D('Token')->where($where)->order('created desc')->find();
            if ($tokens && $tokens['device_token']) {
                $ret = $this->pushByAlias($tokens['device_token'], $content);
                if (is_string($ret)) {
                    $ret = str_replace('\t', '', $ret);
                    $status = 'ERROR';
                } else {
                    if ($ret['http_code'] == 200) {
                        $status = 'SUCCESS';
                    } else {
                        $status = 'ERROR';
                    }
                    $ret = json_encode($ret);
                }
                $data = array(
                    'member_id' => $member_id,
                    'source' => $source,
                    'systype' => $tokens['sysversion'],
                    'content' => $content,
                    'deviceid' => $tokens['device_token'],
                    'status' => $status,
                    'msg' => $ret
                );
                D('PushLog')->addPushLog($data);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
    }


    /**
     * 买家APP推送
     * @param $orderType
     * @param $memberId
     * @param $content
     */
    public static function buyerOrderPush($orderType, $memberId, $content, $extras = [])
    {
        $b2bOrderType = [
            Constant::ORDER_TYPE_PURCHASE,
            Constant::ORDER_TYPE_ACTIVITY,
            Constant::ORDER_TYPE_CATER,
            Constant::ORDER_TYPE_MIXED, //混合订单
            Constant::ORDER_TYPE_PRESALE,  //预售订单
        ];
        $b2cOrderType = [
            Constant::ORDER_TYPE_RETAIL,
            Constant::ORDER_TYPE_TUANPIN
        ];
        $pushType = '';
        $source = '';
        if (in_array($orderType, $b2bOrderType)) {
            $pushType = JPushUtil::PUSH_TYPE_CLOUD;
            $source = Constant::APP_SOURCE_CLOUD;
        } else if (in_array($orderType, $b2cOrderType)) {
            $pushType = JPushUtil::PUSH_TYPE_B2C;
            $source = Constant::APP_SOURCE_B2C;
        }
        $jPushUtil = new JPushUtil($pushType);
        $jPushUtil->pushByMemberTag($memberId, $content, $source, $extras);
    }

    /**
     * 根据用户ID推送
     * @param string $member_id 用户ID
     * @param string $content 内容
     * @param string $source APP来源
     * @param array $extras 自定义参数
     * @return bool
     */
    public function pushByMemberTag($member_id, $content, $source, $extras)
    {
        try {
            $where = array(
                'member_id' => $member_id,
            );
            if ($source) {
                $where['source'] = $source;
            }
            $tokens = D('Token')->where($where)->order('created desc')->find();
            if ($tokens && $tokens['device_token']) {
                $ret = $this->pushByAlias($tokens['device_token'], $content, $extras);
                if (is_string($ret)) {
                    $ret = str_replace('\t', '', $ret);
                    $status = 'ERROR';
                } else {
                    if ($ret['http_code'] == 200) {
                        $status = 'SUCCESS';
                    } else {
                        $status = 'ERROR';
                    }
                    $ret = json_encode($ret);
                }
                $data = array(
                    'member_id' => $member_id,
                    'source' => $source,
                    'systype' => $tokens['sysversion'],
                    'content' => $content,
                    'deviceid' => $tokens['device_token'],
                    'status' => $status,
                    'msg' => $ret,
                    'operation_link' => isset($extras['operation_link']) ? $extras['operation_link'] : '',
                    'operation_other' => isset($extras['operation_other']) ? $extras['operation_other'] : '',
                    'operation_type' => isset($extras['operation_type']) ? $extras['operation_type'] : ''
                );
                D('PushLog')->add($data);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
    }
}
