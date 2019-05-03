<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 9:32
 */

namespace app\comm\model;


class Alipay
{
    private $APPID = "2018091161385080";
    private $Method = "alipay.trade.app.pay";
    private $CharSet = "utf-8";
    private $SignType = "RSA2";
    private $SellerID = "2088221376380055";
    private $Version = "1.0";
    private $format = "JSON";
    // private $NotifyUrl;
    private $GetWayUrl = "https://openapi.alipay.com/gateway.do";
    private $alipay_public_key;

    public function __construct()
    {
        $this->client = new \AopClient();
        $this->client->appId = $this->APPID;
        $this->appId = $this->APPID;
        $this->seller_id = $this->SellerID;
        $this->client->rsaPrivateKeyFilePath = "../rsaPrivateKeyFilePath.pem";
        $this->alipay_public_key = "../alipay_public_key.pem";
        $this->client->gatewayUrl = $this->GetWayUrl;
        $this->notifyUrl = config('app.alipay_h5_notify_url');
//        $this->notifyUrl = 'http://' . C('PAY_MODULE') . $config['notifyUrl'];
    }

//    生成签名

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
            "method" => $this->Method,
            "timestamp" => date("Y-m-d H:i:s"),
            "charset" => $this->CharSet,
            "format" => $this->format,
            'sign_type' => $this->SignType,
            'version' => $this->Version,
            'notify_url' => $this->notifyUrl,
            'biz_content' => json_encode(array(
                'seller_id' => $this->SellerID,
//                'product_code' => 'QUICK_MSECURITY_PAY',
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

    public function createPayListObj()
    {
//        var_dump($this->notify_url);die;
        $obj = new paylist_Model();
        return $obj;
    }

    /**
     * 验签方法
     * @param $arr :验签支付宝返回的信息，使用支付宝公钥。
     * @return boolean
     */
    function check($arr)
    {
        $aop = new AopClient();
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $result = $aop->rsaCheckV1($arr, $this->alipay_public_key);
        return $result;
    }

    /**
     * 获取支付url
     * @param $total_amount
     * @param $id
     * @param $ord_no
     */

    public function payUrlAlipay($total_amount, $id, $ord_no)
    {
        //业务参数
        $id = $this->confirmToken(); //等客户端修改完，改默认值。
        $total_amount = $this->input->request('total_amount', 0); //订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]

        $subject = $this->input->request('subject', ''); //商品的标题/交易标题/订单标题/订单关键字等。

        //公共参数
        $ord_no = $this->input->request('no'); //	商户网站唯一订单号
        $type = $this->input->request('type'); //	支付类型：d：订单支付，y:余额充值
        $result = $this->basePay($id, $subject, $total_amount, $ord_no, $type);

        echo json_encode($result);
    }

    public function basePay($id, $subject, $total_amount, $ord_no, $type = 'd', $balance = 0, $points = 0)
    {
        $product_code = 'QUICK_MSECURITY_PAY'; //	销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY

        //支付订单号
        $out_trade_no = time() . $this->code(4);
        $arr2['out_trade_no'] = $out_trade_no;
        $arr2['product_code'] = $product_code;
        $arr2['subject'] = $subject;
        $arr2['timeout_express'] = '30m';
        $arr2['total_amount'] = $total_amount;

        $biz_content = $this->createBiz_content($arr2);

        $arr['app_id'] = $this->appId;
        $arr['biz_content'] = $biz_content;
        $arr['charset'] = $this->charset;
        $arr['format'] = "json";
        $arr['method'] = $this->method;
        $arr['notify_url'] = $this->notify_url;
        $arr['sign_type'] = $this->sign_type;
        $arr['timestamp'] = $this->timestamp;
        $arr['version'] = "1.0";

        unset($arr2['product_code']);
        unset($arr2['timeout_express']);

        $arr2['user_id'] = $id;
        $arr2['type'] = $type;
        $arr2['ref_no'] = $ord_no ? $ord_no : $id;
        $arr2['balance'] = $balance ? $balance : 0;
        $arr2['points'] = $points ? $points : 0;

        $rs = $this->createPayListObj()->insert($arr2);

        if (!$rs) $this->output_result_alipay('1', '账单存储出现问题');

        $c = new AopClient();

        $c->rsaPrivateKey = Kohana::config('constants.rsaPrivateKey');

        $sign = $c->generateSign($arr, $this->sign_type);

        $arr['sign'] = $sign;

        $result['err_code'] = 0;
        $result['url'] = $this->createLinkstring($arr);

        return $result;
    }

    protected function createBiz_content($para)
    {
        $arg = "{";
        while (list ($key, $val) = each($para)) {
            $arg .= '"' . $key . '"' . ":" . '"' . $val . '"' . ",";
        }

        $arg = substr($arg, 0, count($arg) - 2);

        $arg .= "}";
        return $arg;
    }

    public function test()
    {
        $str = $this->input->request('a');
        echo $str ? $str : 'ha';
    }

    /**
     * 数组组成url
     * @param $para :url参数
     * @return string
     *
     */

    protected function createLinkstring($para, $encode = true)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            if ($encode)
                $val = urlencode($val);
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }

    /**
     * 异步验证
     */
    public function checkRsaSign()
    {
//        $this->output_log('123','支付宝调用一步验证');
        $param = $_REQUEST;
//        $this->output_log(json_encode($param));
        $aop = new \AopClient();
        //支付宝公钥

        $aop->alipayrsaPublicKey = $this->alipay_public_key;

        $result = $aop->rsaCheckV1($param, $this->rsaPublicKeyFilePath, $this->sign_type);

        if ($result) {

            //插入trade_status验证
            if ($param['trade_status'] != 'TRADE_SUCCESS')
                $this->output_result_alipay('1', '支付状态出现问题。');

            //插入app_id验证
            if ($param['app_id'] != $this->appId)
                $this->output_result_alipay('4', 'app_id验证出现问题。');

            //插入sell_id验证
            if ($param['seller_id'] != $this->seller_id)
                $this->output_result_alipay('2', 'seller_id验证出现问题。');


            //插入total_amount验证
            $rs = $this->createPayListObj()->getInfoEx('total_amount,user_id', array('out_trade_no' => $param['out_trade_no']));

            if (!$rs)
                $this->output_result_alipay('5', '查无此单。');

            $total_amount = $rs['total_amount'];
//            $this->output_log('100','验证订单信息没有问题');
            if ($param['total_amount'] != $total_amount)
                $this->output_result_alipay('2', 'seller_id验证出现问题。');
//            项目逻辑处理开始
//            项目逻辑处理结束
            $this->output_result_alipay('0', '验证成功，已返回支付宝服务器！', true);
        } else {
            $this->output_result_alipay('3', '支付验证出错。');
        }
    }


    public function gateWay()
    {
        $this->input->request('resultStatus');
    }

    /**
     * 同步验证
     */

    public function returnUrl()
    {

        $params = $this->input->request();

        $aop = new AopClient();
        //支付宝公钥

        if ($params['resultStatus'] != '9000') {
            $this->output_error(2001);
        }

//        echo "<pre>";
//        print_r(json_encode($params));exit();
        $param = array();
        if (isset($params['result'])) {
            $temp = json_decode($params['result'], true);
            if (isset($temp['alipay_trade_app_pay_response'])) {
                $param = $temp['alipay_trade_app_pay_response'];
            } else
                $this->output_error(2003);

        } else
            $this->output_error(2002);

//        echo "<pre>";
//        print_r($param);exit();
//        $aop->alipayrsaPublicKey = $this->alipay_public_key;
//
//        $result = $aop->rsaCheckV1($param, $this->rsaPublicKeyFilePath,$temp['sign_type']);
//
//        if ($result != $temp['sign'])
//            $this->output_result_alipay('6', '同步验签失败。');

        //插入app_id验证
        if ($param['app_id'] != $this->appId)
            $this->output_result_alipay('4', 'app_id验证出现问题。');

        //插入sell_id验证
        if ($param['seller_id'] != $this->seller_id)
            $this->output_result_alipay('2', 'seller_id验证出现问题。');

        //插入total_amount验证
        $rs = $this->createPayListObj()->getInfoEx('total_amount,user_id', array('out_trade_no' => $param['out_trade_no']));

        if (!$rs)
            $this->output_result_alipay('5', '查无此单。');

        $total_amount = $rs['total_amount'];

        if ($param['total_amount'] != $total_amount)
            $this->output_result_alipay('6', '金额验证出现问题。');

        $this->output_result();
    }

    protected function output_result_alipay($error_num, $errinfo = '', $result = false)
    {
        if ($errinfo) $str = json_encode(array('err_code' => $error_num . '', 'err_info' => $errinfo));
        else $str = json_encode(array('errcode' => $error_num . ''));

        $str = preg_replace("/\\\u([0-9a-f]{4})/ie", "mb_convert_encoding(pack('V', hexdec('U$1')),'UTF-8','UCS-4LE')", $str);

        $this->output_log_file($str);

        if ($result)
            echo 'success';
        else
            echo "fail,error_num:" . $error_num;

        exit;
    }
}