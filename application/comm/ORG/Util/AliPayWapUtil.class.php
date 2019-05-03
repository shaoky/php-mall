<?php

namespace Common\ORG\Util;

/**
 *
 * Class AliPayWapUtil
 * @package extend\util
 */
class AliPayWapUtil
{

    /**
     * 交易创建，等待买家付款
     */
    const WAIT_BUYER_PAY = 'WAIT_BUYER_PAY';
    /**
     * 未付款交易超时关闭，或支付完成后全额退款
     */
    const TRADE_CLOSED = 'TRADE_CLOSED';
    /**
     * 交易支付成功
     */
    const TRADE_SUCCESS = 'TRADE_SUCCESS';
    /**
     * 交易结束，不可退款
     */
    const TRADE_FINISHED = '交易结束，不可退款';

    private $value;

    /**
     * AliPayWapUtil constructor.
     */
    public function __construct()
    {
        $this->value = C('ALIPAY_WAP');
    }

    /**
     * 支付宝手机网站支付
     * @param $payment_sn
     * @param $subject
     * @param $total_amount
     * @return string
     */
    public function aliWapPay($payment_sn, $subject, $total_amount)
    {
        //构造要请求的参数数组，无需改动
        $param = array(
            "service" => $this->value['service'],
            "partner" => $this->value['partner'],
            "seller_id" => $this->value['seller_id'],
            "payment_type" => $this->value['payment_type'],
            "notify_url" => $this->value['notify_url'],
            "return_url" => $this->value['return_url'],
            "_input_charset" => trim(strtolower($this->value['input_charset'])),
            'out_trade_no' => $payment_sn,
            'subject' => $subject,
            'total_fee' => $total_amount
//            'app_pay' => 'Y'//启用此参数能唤起钱包APP支付宝
        );
        $alipaySubmit = new \AlipaySubmit($this->value);
        $html_text = $alipaySubmit->buildRequestForm($param, "get", "确认");
        return $html_text;
    }
}