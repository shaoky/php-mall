<?php

namespace Common\ORG\Library;

/**
 * 支付宝
 * Class AliPayClient
 * @package extend\library
 */
class AliPayClient
{

    private $client;
    private $appId;
    private $seller_id;
    private $notifyUrl;

    private $charset = "utf-8";
    private $format = "JSON";
    private $sign_type = 'RSA';
    private $version = '1.0';


    public function __construct()
    {
        $this->client = new \AopClient();
        $config = D('Config')->queryConfigByGroup('ALIPAY', 'APP');
        $this->client->appId = $config['appId'];
        $this->appId = $config['appId'];
        $this->seller_id = $config['seller_id'];
        $this->client->rsaPrivateKeyFilePath = SVN_ROOT . $config['rsaPrivateKeyFilePath'];
        $this->client->gatewayUrl = $config['gatewayUrl'];
        $this->notifyUrl = 'http://' . C('PAY_MODULE') . $config['notifyUrl'];
    }

    /**
     * 生成签名
     * @param string $out_trade_no 订单号
     * @param string $subject 商品标题
     * @param string $total_amount 订单金额
     * @return string
     */
    public function generateSign($out_trade_no, $subject, $total_amount)
    {
        $str = array(
            "app_id" => $this->appId,
            "method" => 'alipay.trade.app.pay',
            "timestamp" => date("Y-m-d H:i:s"),
            "charset" => $this->charset,
            "format" => $this->format,
            'sign_type' => $this->sign_type,
            'version' => $this->version,
            'notify_url' => $this->notifyUrl,
            'biz_content' => json_encode(array(
                'seller_id' => C('ALIPAY.seller_id'),
                'product_code' => 'QUICK_MSECURITY_PAY',
                'total_amount' => $total_amount,
                'subject' => $subject,
                'timeout_express' => "90m",
                'out_trade_no' => $out_trade_no,
                'passback_params'
            ))
        );
        //签名
        $str['sign'] = $this->signature($str);
        $requestUrl = '';
        //系统参数放入GET请求串
        foreach ($str as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($this->client->characet($sysParamValue, $this->client->postCharset)) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        return $requestUrl;
    }

    /**
     * 签名
     * @param $str
     * @return string
     */
    public function signature($str)
    {
        return $this->client->generateSign($str);
    }

    /**
     * 查询对账单下载地址
     * @param $billType     账单类型 AlipayOrderModel::BILL_TYPE_TRADE 交易收单的业务账单
     *                              AlipayOrderModel::BILL_TYPE_SIGN_CUSTOMER:资金变动的帐务账单
     * @param $billDate     账单时间：日账单格式为yyyy-MM-dd，月账单格式为yyyy-MM。
     * @return bool/string
     */
    public function billDownloadUrlQuery($billType, $billDate)
    {
        $request = new \AlipayDataDataserviceBillDownloadurlQueryRequest();
        $request->setBizContent(
            json_encode(array(
                'bill_type' => $billType,
                'bill_date' => $billDate
            ))
        );
        $result = $this->client->execute($request);
        $sign = $result->sign;
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            $url = $result->$responseNode->bill_download_url;
            return $url;
        } else {
            return false;
        }
    }


}