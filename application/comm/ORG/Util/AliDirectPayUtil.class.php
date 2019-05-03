<?php

namespace Common\ORG\Util;

/**
 * 阿里及时支付
 * Class AliDirectPayUtil
 * @package extend\util
 */
class AliDirectPayUtil
{
    public function __construct()
    {
        $this->value = C('DIRECT_PAY');
    }

    /**
     * 支付宝及时支付
     * @param $payment_sn
     * @param $subject
     * @param $total_amount
     * @return \提交表单HTML文本
     */
    public function aliDirectPay($payment_sn, $subject, $total_amount)
    {
        //构造要请求的参数数组，无需改动
        $param = array(
            "service" => $this->value['service'],
            "partner" => $this->value['partner'],
            "seller_id" => $this->value['seller_id'],
            "payment_type" => $this->value['payment_type'],
            "notify_url" => $this->value['notify_url'],
            "return_url" => $this->value['return_url'],
            'out_trade_no' => $payment_sn,
            'subject' => $subject,
            'total_fee' => $total_amount,
            "_input_charset" => trim(strtolower($this->value['input_charset'])),
        );
        $aliDirectPaySubmit = new \AlipaySubmit($this->value);
        $html_text = $aliDirectPaySubmit->buildRequestForm($param, "get", "确认");
        return $html_text;
    }
}