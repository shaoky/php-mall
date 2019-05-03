<?php

namespace Common\ORG\Library;

use Common\Core\Constant;
use Common\ORG\Util\Logger;

/**
 * 平安快捷支付
 * Class PABankPayApi
 * @package extend\util
 */
class PABankPayApi
{

//绑成功卡支付
//successArray ( [errorCode] => [errorMsg] => [status] => 01 [date] => 20160507161807 [charge] => 0.00 [masterId] => 2000311146 [orderId] => 20003111462015060466406555 [currency] => RMB [amount] => 0.01 [objectName] => pingan pay test
//[paydate] => 20160507143506 [validtime] => [remark] => this is a test product [bindId] => 20003111462015060466406555 [accountNo] => 9919 [plantBankName] => 平安银行借记卡 [telephone] => 137****1831 [plantId] => 1000000004 [plantBankId]
//=> PAB-D )
    /**
     * 支付开始
     */
    private $masterId = "";
//    private $merchantCertFile = "";
    private $pingan = null;
    private $gateway = "";
    private $tTrustPayCertFile = "";

    public function __construct()
    {
        $config = D('Config')->queryConfigByGroup('PINGAN', 'PAY');
        $this->masterId = $config['masterId'];
        $this->merchantCertFile = SVN_ROOT . $config['merchantCertFile'];
        $this->gateway = $config['gateway'];
        $this->tTrustPayCertFile = SVN_ROOT . $config['tTrustPayCertFile'];

        $pkey = $config['pkey'];
        $this->pingan = new PabankPay();
        $this->pingan->setPkey($pkey);
    }

    /**
     * 平安支付跳转
     * @param $orderId      订单号
     * @param $amount       金额
     * @param $objectName
     * @param $remark       备注
     * @param $customerId   会员子账号
     * @param $plantBankId  银行卡代码
     * @param $notify_url   后台回调
     * @param $return_url   前台回调
     */
    public function dopay($orderId, $amount, $objectName, $remark, $customerId, $plantBankId, $notify_url, $return_url)
    {
        //组装订单数据
        $data = array(
            'masterId' => $this->masterId,
            'orderId' => $orderId,
            'currency' => 'RMB',
            'amount' => $amount,
            'objectName' => $objectName,
            'paydate' => date("YmdHis", microtime(true)),
            'remark' => $remark,
            'validtime' => '0',
            'customerId' => $customerId,
            'plantBankId' => $plantBankId,
        );

        $data = $this->pingan->getXmlData($data);
        //获取orig和sign
        list($orig, $sign) = $this->pingan->getOrigAndSing($this->merchantCertFile, $data);

        $parameter = array(
            'orig' => $orig,
            'sign' => $sign,
            'returnurl' => $return_url,
            'NOTIFYURL' => $notify_url,
        );
        $this->pingan->showHtml($parameter, $this->gateway . "khpayment/khPayment_bind.do");
    }

    public function dopay1($data)
    {
        //组装订单数据
        $data["masterId"] = $this->masterId;
        $data["currency"] = "RMB";
        $return_url = $data["returnurl"];
        $notify_url = $data["NOTIFYURL"];
        unset($data["returnurl"]);
        unset($data["NOTIFYURL"]);

        $data = $this->pingan->getXmlData($data);
        //获取orig和sign
        list($orig, $sign) = $this->pingan->getOrigAndSing($this->merchantCertFile, $data);

        $parameter = array(
            'orig' => $orig,
            'sign' => $sign,
            'returnurl' => $return_url,
            'NOTIFYURL' => $notify_url,
        );

        $this->pingan->showHtml($parameter, $this->gateway . "khpayment/khPayment_bind.do");
    }

    public function getdopay($data)
    {
        $data["masterId"] = $this->masterId;
        $data["objectName"] = iconv("UTF-8", "GBK", $data["objectName"]);

        $pabank_notify = "http://" . C("PAY_MODULE") . Constant::PABANK_NOTIFY;
        $pabank_return = "http://" . C("PAY_MODULE") . Constant::PABANK_RETURN;
        $data = $this->pingan->getXmlData($data);
        //获取orig和sign
        list($orig, $sign) = $this->pingan->getOrigAndSing($this->merchantCertFile, $data);
        $parameter = array(
            'orig' => $orig,
            'sign' => $sign,
            'returnurl' => $pabank_return,
            'NOTIFYURL' => $pabank_notify,
        );
        return $this->pingan->showHtml($parameter, $this->gateway . "khpayment/khPayment_bind.do");
    }

    /**
     *
     * @param $isnotice 是否后台通知
     * @return array
     */
    public function payreturn($isnotice)
    {
        $orig = $_POST['orig'];
        $sign = $_POST['sign'];
        Logger::info('接收参数：' . 'orig:' . $orig . ',sign:' . $sign);

        //1、进行数据转码
        if ($isnotice) {
            $orig = base64_decode($orig);
            $sign = base64_decode($sign);
            Logger::info('后台通知转码：' . 'orig:' . $orig . ',sign:' . $sign);
        } else {

            $orig = $this->pingan->_base64_url_decode($orig);
            $sign = $this->pingan->_base64_url_decode($sign);
            Logger::info('前台通知转码：' . 'orig:' . $orig . ',sign:' . $sign);
        }

        //2、验证签名是否正确
        $result = $this->pingan->validate($orig, $sign, $this->tTrustPayCertFile);
        if (!$result) {
            //写入日志
            Logger::info('证书验证失败！');
        }
        //3、将xml数据转换成array
        $orig_data = $this->pingan->xml_to_array($orig);
        Logger::info('xml转组数：' . json_encode($orig_data));
        return $orig_data;
    }

    public function action($hTranFunc, $data = array())
    {
        $gateway = "";
        switch ($hTranFunc) {
            case "KH0001":
                $intf = '单笔订单状态查询接口';
                $gateway = $this->gateway . "corporbank/KH0001.pay"; //orderId
                break;
            case "KH0002":
                $intf = '订单列表信息查询接口';
                $gateway = $this->gateway . "corporbank/KH0002.pay"; //beginDate,endDate(14位)
                break;
            case "KH0003":
                $intf = '每日对账单查询接口';
                $gateway = $this->gateway . "corporbank/KH0003.pay"; //date (8位)
                break;
            case "KH0005":
                $intf = '单笔订单退款接口';
                $gateway = $this->gateway . "corporbank/KH0005.pay"; //refundNo orderId currency refundAmt objectName remark NOTIFYURL
                break;
            case "KH0006":
                $intf = '订单退款查询接口';
                $gateway = $this->gateway . "corporbank/KH0006.pay"; //beginDate endDate
                break;
            case "API004"://查询已绑定银行卡列表API004（绑卡支付用）
                $intf = '查询已绑定银行卡列表-绑卡支付';
                $gateway = $this->gateway . "khpayment/API004.do"; //customerId
                break;
            case "API005"://解除已绑定银行卡API005（绑卡支付用）
                $intf = '解除已绑定银行卡-绑卡支付';
                $gateway = $this->gateway . "khpayment/API005.do"; //customerId bindId
                break;
            case "API006"://发送验证码API006（绑卡支付用）
                $intf = '发送验证码-绑卡支付';
                $gateway = $this->gateway . "khpayment/API006.do"; //customerId  amount bindId orderId
                break;
            case "API007"://使用已绑定银行卡支付API007（绑卡支付用）
                $intf = '使用已绑定银行卡支付-绑卡支付';
                $gateway = $this->gateway . "khpayment/API007.do"; //orderId currency amount objectName paydate validtime remark customerId bindId verifyCode dateTime NOTIFYURL
                break;
        }

        $data["masterId"] = $this->masterId;

        $xml_data = $this->pingan->array_to_xml($data);
        //获取签名后的orig和sign
        $orig = $this->pingan->getOrig($xml_data);
        $sign = $this->pingan->getSign($this->merchantCertFile, $xml_data);

        //通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;

        try {
            $rsponse = $this->pingan->curl($gateway, $parms);
            //通过字符串截取获取orig
            $rsponseData = explode('orig=', $rsponse);
            $rsponse2Data = explode('SDBPAYGATE=', $rsponseData[1]);
            //解码
            $formOrig = $this->pingan->_base64_url_decode($rsponse2Data[0]);
            $result = $this->pingan->xml_to_array($formOrig);
        } catch (\Exception $e) {
            $result = array(
                "errorCode" => "false",
                "errorMsg" => "连接银行服务失败",
                'exception' => $e->getMessage()
            );
        }
        $logData = array(
            "thirdlogno" => isset($data['orderId']) ? $data['orderId'] : '',
            "tranfunc" => $hTranFunc,
            "intf" => $intf,
            "custacctid" => $data['customerId'],
            "inparm" => json_encode($data, JSON_UNESCAPED_UNICODE),
            "outparm" => json_encode($result, JSON_UNESCAPED_UNICODE),
            'rspcode' => $result['errorCode'],
            'rspmsg' => $result['errorMsg'],
            'created' => time()
        );
        D('PabapiLog')->add($logData);
        return $result;
    }

}
