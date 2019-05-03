<?php

namespace Common\ORG\Util;

/**
 * 支付宝PC网站支付
 * Class AliPayPCPageUtil
 * @package Common\ORG\Util
 */
class AliPayPCPageUtil
{

    private $value;

    const SYSTEM_ERROR = 'ACQ.SYSTEM_ERROR';	//系统错误
    const INVALID_PARAMETER = 'ACQ.INVALID_PARAMETER';	//参数无效
    const SELLER_BALANCE_NOT_ENOUGH = 'ACQ.SELLER_BALANCE_NOT_ENOUGH';	//卖家余额不足
    const REFUND_AMT_NOT_EQUAL_TOTAL = 'ACQ.REFUND_AMT_NOT_EQUAL_TOTAL';	//退款金额超限
    const REASON_TRADE_BEEN_FREEZEN = 'ACQ.REASON_TRADE_BEEN_FREEZEN';	//请求退款的交易被冻结
    const TRADE_NOT_EXIST = 'ACQ.TRADE_NOT_EXIST';	//交易不存在
    const TRADE_HAS_FINISHED = 'ACQ.TRADE_HAS_FINISHED';	//交易已完结
    const TRADE_STATUS_ERROR = 'ACQ.TRADE_STATUS_ERROR';	//交易状态非法
    const DISCORDANT_REPEAT_REQUEST = 'ACQ.DISCORDANT_REPEAT_REQUEST';	//不一致的请求
    const REASON_TRADE_REFUND_FEE_ERR = 'ACQ.REASON_TRADE_REFUND_FEE_ERR';	//退款金额无效
    const TRADE_NOT_ALLOW_REFUND = 'ACQ.TRADE_NOT_ALLOW_REFUND';	//当前交易不允许退款

    const REFUND_ERROR = [
        self::SYSTEM_ERROR => '系统错误',//请使用相同的参数再次调用
        self::INVALID_PARAMETER => '参数无效',//请求参数有错，重新检查请求后，再调用退款
        self::SELLER_BALANCE_NOT_ENOUGH => '卖家余额不足',//商户支付宝账户充值后重新发起退款即可
        self::REFUND_AMT_NOT_EQUAL_TOTAL => '退款金额超限',//检查退款金额是否正确，重新修改请求后，重新发起退款
        self::REASON_TRADE_BEEN_FREEZEN => '请求退款的交易被冻结',//联系支付宝小二，确认该笔交易的具体情况
        self::TRADE_NOT_EXIST => '交易不存在',//检查请求中的交易号和商户订单号是否正确，确认后重新发起
        self::TRADE_HAS_FINISHED => '交易已完结',//该交易已完结，不允许进行退款，确认请求的退款的交易信息是否正确
        self::TRADE_STATUS_ERROR => '交易状态非法',//查询交易，确认交易是否已经付款
        self::DISCORDANT_REPEAT_REQUEST => '不一致的请求',//检查该退款号是否已退过款或更换退款号重新发起请求
        self::REASON_TRADE_REFUND_FEE_ERR => '退款金额无效',//检查退款请求的金额是否正确
        self::TRADE_NOT_ALLOW_REFUND => '当前交易不允许退款'//检查当前交易的状态是否为交易成功状态以及签约的退款属性是否允许退款，确认后，重新发起请求
    ];

    public function __construct()
    {
        $this->value = C('ALIPAY_PAGE');
    }

    /**
     * 支付宝及时支付
     * @param $payment_sn
     * @param $subject
     * @param $total_amount
     * @return string
     */
    public function alipayTradePagePay($payment_sn, $subject, $total_amount)
    {
        $config = C('ALIPAY_PAGE');
        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setSubject($subject);//订单名称
        $payRequestBuilder->setTotalAmount($total_amount);//付款金额
        $payRequestBuilder->setOutTradeNo($payment_sn);//商户订单号

        $aop = new \AlipayTradeService($config);
        return $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
    }




}