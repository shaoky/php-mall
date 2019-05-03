<?php

namespace Common\ORG\Library;

use Common\ORG\Util\Logger;


/**
 * 平安API
 * Class PABankApi
 * @package extend\library
 */
class PABankApi
{

    private $socket;
    private $bQydm = ''; //企业代码(客户号)
    private $bSupAcctId = ''; //资金汇总账号;
    private $server_Ip = "";
    private $service_port = "";
    private $tranMessage = '';
    private $outMessage = '';
    private $socket_time_out = 5;

    public function __construct()
    {
        $config = D('Config')->queryConfigByGroup('PINGAN', 'API');
        $this->bQydm = $config['qydm'];
        $this->bSupAcctId = $config['SupAcctId'];
        $this->server_Ip = $config['server_ip'];
        $this->service_port = $config['service_port'];
    }

    public function conn()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
            return false;
        }
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $this->socket_time_out, "usec" => 0)); //发送超时
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => $this->socket_time_out, "usec" => 0)); //接收超时
        $address = $this->server_Ip;
        $service_port = $this->service_port;
        $result = socket_connect($this->socket, $address, $service_port);
        if ($result === false) {
            Logger::error("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)) . "\n");
            return false;
        }
        return true;
    }

    private function getTranMessageNetHead($hLength, $hThirdLogNo)
    {
        /* 组公网通讯报文头 */
        $netHeadPart1 = "A001130101";
        $netHeadPart2 = "                "; //16个空格
        $hTradeCode = "000000";
        $hServType = "01";
        //$hMacCode="                ";
        $hTrandateTime = date('Ymdhis'); //yyyMMddHHmmss
        $hRspCode = "999999";
        $hRspMsg = "                                                                                                    "; //100个空格
        $hConFlag = "0";
        $hCounterId = "PA001";
        $hTimes = "000";
        $hSignFlag = "0";
        $hSignPacketType = "0";
        $netHeadPart3 = "            "; //12个空格
        $netHeadPart4 = "00000000000";

        $tranMessageNetHead = $netHeadPart1 . $this->bQydm . $netHeadPart2 . $hLength . $hTradeCode . $hCounterId . $hServType . $hTrandateTime . $hThirdLogNo
            . $hRspCode . $hRspMsg . $hConFlag . $hTimes . $hSignFlag . $hSignPacketType . $netHeadPart3 . $netHeadPart4;
        return $tranMessageNetHead;
    }

    private function getTranMessageHead($hTranFunc, $hThirdLogNo, $hLength)
    {
        /* 组公网业务报文头 */
        $hServType = "01";
        $hMacCode = "                ";
        $hTrandateTime = date('Ymdhis'); //yyyMMddHHmmss
        $hRspCode = "999999";
        $hRspMsg = "                                          ";
        $hConFlag = "0";
        $hCounterId = "PA001";
        $tranMessageHead = $hTranFunc . $hServType . $hMacCode . $hTrandateTime . $hRspCode . $hRspMsg . $hConFlag . $hLength . $hCounterId . $hThirdLogNo . $this->bQydm;
        return $tranMessageHead;
    }

    /**
     * 发送报文
     * @param $hTranFunc
     * @param $hThirdLogNo
     * @param $tranMessageBody
     * @return string
     */
    private function data($hTranFunc, $hThirdLogNo, $tranMessageBody)
    {
        $iLength = strlen($tranMessageBody);
        $hLength = str_pad($iLength, 8, '0', STR_PAD_LEFT);
        $hNetLength = str_pad($iLength + 122, 10, '0', STR_PAD_LEFT);
        $tranMessage = $this->getTranMessageNetHead($hNetLength, $hThirdLogNo) . $this->getTranMessageHead($hTranFunc, $hThirdLogNo, $hLength) . $tranMessageBody;
        $this->tranMessage = iconv("GBK", "UTF-8", $tranMessage);
        return $tranMessage;
    }

    private function parsingTranMessageString($msgstr)
    {
        Logger::info("交易返回报文：" . iconv('gbk', 'utf-8', $msgstr));
        $arr = array();
        if (!$msgstr) {
            $arr['RspCode'] = $msgstr;
            $arr['RspMsg'] = '连接平安银行服务失败';
        } else {
            $arr['RspCode'] = substr($msgstr, 87, 6);
            $RspMsgBak = substr($msgstr, 93, 100);
            $arr['RspMsg'] = trim($RspMsgBak);
            $arr['RspMsg'] = iconv('gbk', 'utf-8', $arr['RspMsg']);
            if ($arr['RspCode'] === '000000') {
                $arr['TranFunc'] = substr($msgstr, 222, 4);
                $arr['BodyMsg'] = substr($msgstr, 344);
                $arr['BodyMsg'] = iconv('gbk', 'utf-8', $arr['BodyMsg']);
                $this->spiltMessage($arr);
            } else {
                Logger::error("交易失败：" . $msgstr);
            }
        }
        return $arr;
    }

    private function spiltMessage(&$arr)
    {
        $bmArr = explode('&', $arr['BodyMsg']);
        unset($arr['BodyMsg']);
        switch ($arr['TranFunc']) {
            case 6027:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['Reserve'] = $bmArr[$arr['TotalCount'] * 2 + 1];
                $arr['Reserve2'] = $bmArr[$arr['TotalCount'] * 2 + 2];
                for ($i = 0; $i < $arr['TotalCount']; $i++) {
                    $row = array();
                    $fi = 1 + $i * 2;
                    $row['NodeName'] = $bmArr[$fi]; //网点名称NodeName
                    $row['NodeCode'] = $bmArr[$fi + 1]; //网点联行号NodeCode
                    $arr['rows'][] = $row;
                }
                break;

            case 6000:
                $arr['CustAcctId'] = $bmArr[0]; //子账户账号
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6055:
                $arr['Reserve'] = $bmArr[0];
                break;
            case 6064:
                $arr['FrontLogNo'] = $bmArr[0]; //前置流水号
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6066:
                $arr['Reserve'] = $bmArr[0];
                break;
            case 6067:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6065:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6056:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6005:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['HandFee'] = $bmArr[1];
                $arr['Reserve'] = $bmArr[2];
                break;
            case 6033:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['HandFee'] = $bmArr[1];
                $arr['Reserve'] = $bmArr[2];
                break;
            case 6085:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6008:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;

            case 6053:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6006:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;

            case 6034:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;

            case 6052:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;

            case 6031:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6007:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6070:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];
                break;
            case 6077:
                $arr['FrontLogNo'] = $bmArr[0];
                $arr['Reserve'] = $bmArr[1];

                break;
            case 6082:
                $arr['RevMobilePhone'] = $bmArr[0];
                $arr['SerialNo'] = $bmArr[1];
                $arr['Reserve'] = $bmArr[2];
                break;
            case 6083:
                $arr['RevMobilePhone'] = $bmArr[0];
                $arr['SerialNo'] = $bmArr[1];
                $arr['Reserve'] = $bmArr[2];
                break;
            case 6084:
                $arr['Reserve'] = $bmArr[0];
                break;
            case 6010:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['BeginNum'] = intval($bmArr[1]);
                $arr['LastPage'] = intval($bmArr[2]);
                $arr['RecordNum'] = intval($bmArr[3]);
                $arr['Reserve'] = $bmArr[$arr['RecordNum'] * 7 + 4];
                $arr['rows'] = array();
                for ($i = 0; $i < $arr['RecordNum']; $i++) {
                    $row = array();
                    $fi = 4 + $i * 7;
                    $row['CustAcctId'] = $bmArr[$fi];
                    $row['CustType'] = $bmArr[$fi + 1];
                    $row['ThirdCustId'] = $bmArr[$fi + 2];
                    $row['CustName'] = $bmArr[$fi + 3];
                    $row['TotalBalance'] = $bmArr[$fi + 4];
                    $row['TotalTranOutAmount'] = $bmArr[$fi + 5];
                    $row['TranDate'] = $bmArr[$fi + 6];
                    $arr['rows'][] = $row;
                }
                break;
            case 6014:
                $arr['TranFlag'] = $bmArr[0];
                $arr['TranStatus'] = $bmArr[1];
                $arr['TranAmount'] = $bmArr[2];
                $arr['TranDate'] = $bmArr[3];
                $arr['TranTime'] = $bmArr[4];
                $arr['InCustAcctId'] = $bmArr[5];
                $arr['OutCustAcctId'] = $bmArr[6];
                $arr['Reserve'] = $bmArr[7];
                break;
            case 6048:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['Reserve'] = $bmArr[$arr['TotalCount'] * 5 + 1];
                for ($i = 0; $i < $arr['TotalCount']; $i++) {
                    $row = array();
                    $fi = 1 + $i * 5;
                    $row['FrontLogNo'] = $bmArr[$fi];
                    $row['ThirdLogNo'] = $bmArr[$fi + 1];
                    $row['Remark'] = $bmArr[$fi + 2];
                    $row['WithDrawRemark'] = $bmArr[$fi + 3];
                    $row['WithDrawDate'] = $bmArr[$fi + 4];
                    $arr['rows'][] = $row;
                }
                break;
            case 6050:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['BeginNum'] = intval($bmArr[1]);
                $arr['LastPage'] = intval($bmArr[2]);
                $arr['RecordNum'] = intval($bmArr[3]);
                $arr['Reserve'] = $bmArr[$arr['RecordNum'] * 11 + 4];
                $arr['rows'] = array();
                for ($i = 0; $i < $arr['RecordNum']; $i++) {
                    $row = array();
                    $fi = 4 + $i * 11;
                    $row['TranType'] = $bmArr[$fi];
                    $row['ThirdCustId'] = $bmArr[$fi + 1];
                    $row['CustAcctId'] = $bmArr[$fi + 2];
                    $row['TranAmount'] = $bmArr[$fi + 3];
                    $row['InAcctId'] = $bmArr[$fi + 4];
                    $row['InAcctIdName'] = $bmArr[$fi + 5];
                    $row['CcyCode'] = $bmArr[$fi + 6];
                    $row['AcctDate'] = $bmArr[$fi + 7];
                    $row['BankName'] = $bmArr[$fi + 8];
                    $row['Note'] = $bmArr[$fi + 9];
                    $row['FrontLogNo'] = $bmArr[$fi + 10];
                    $arr['rows'][] = $row;
                }
                break;
            case 6072:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['BeginNum'] = intval($bmArr[1]);
                $arr['LastPage'] = intval($bmArr[2]);
                $arr['RecordNum'] = intval($bmArr[3]);
                $arr['Reserve'] = $bmArr[$arr['RecordNum'] * 10 + 4];
                $arr['rows'] = array();
                for ($i = 0; $i < $arr['RecordNum']; $i++) {
                    $row = array();
                    $fi = 4 + $i * 10;
                    $row['TranFlag'] = $bmArr[$fi];
                    $row['TranStatus'] = $bmArr[$fi + 1];
                    $row['TranAmount'] = $bmArr[$fi + 2];
                    $row['TranDate'] = $bmArr[$fi + 3];
                    $row['TranTime'] = $bmArr[$fi + 4];
                    $row['FrontLogNo'] = $bmArr[$fi + 5];
                    $row['KeepType'] = $bmArr[$fi + 6];
                    $row['InCustAcctId'] = $bmArr[$fi + 7];
                    $row['OutCustAcctId'] = $bmArr[$fi + 8];
                    $row['Note'] = $bmArr[$fi + 9];
                    $arr['rows'][] = $row;
                }
                break;
            case 6073:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['BeginNum'] = intval($bmArr[1]);
                $arr['LastPage'] = intval($bmArr[2]);
                $arr['RecordNum'] = intval($bmArr[3]);
                $arr['Reserve'] = $bmArr[$arr['RecordNum'] * 12 + 4];
                $arr['rows'] = array();
                for ($i = 0; $i < $arr['RecordNum']; $i++) {
                    $row = array();
                    $fi = 4 + $i * 12;
                    $row['TranFlag'] = $bmArr[$fi];
                    $row['TranStatus'] = $bmArr[$fi + 1];
                    $row['FuncMsg'] = $bmArr[$fi + 2];
                    $row['ThirdCustId'] = $bmArr[$fi + 3];
                    $row['CustAcctId'] = $bmArr[$fi + 4];
                    $row['CustName'] = $bmArr[$fi + 5];
                    $row['TranAmount'] = $bmArr[$fi + 6];
                    $row['HandFee'] = $bmArr[$fi + 7];
                    $row['TranDate'] = $bmArr[$fi + 8];
                    $row['TranTime'] = $bmArr[$fi + 9];
                    $row['FrontLogNo'] = $bmArr[$fi + 10];
                    $row['Note'] = $bmArr[$fi + 11];
                    $arr['rows'][] = $row;
                }
                break;
            case 6011:
                $arr['LastBalance'] = $bmArr[0];
                $arr['CurBalance'] = $bmArr[1];
                $arr['Reserve'] = $bmArr[2];
                break;
            case 6037:
                $arr['CustAcctId'] = $bmArr[0];
                $arr['TotalAmount'] = $bmArr[1];
                $arr['TotalBalance'] = $bmArr[2];
                $arr['TotalFreezeAmount'] = $bmArr[3];
                $arr['Reserve'] = $bmArr[4];
                break;
            case 6079:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['BeginNum'] = intval($bmArr[1]);
                $arr['LastPage'] = intval($bmArr[2]);
                $arr['RecordNum'] = intval($bmArr[3]);
                $arr['Reserve'] = $bmArr[$arr['RecordNum'] * 12 + 4];
                $arr['rows'] = array();
                for ($i = 0; $i < $arr['RecordNum']; $i++) {
                    $row = array();
                    $fi = 4 + $i * 12;
                    $row['TranFlag'] = $bmArr[$fi];
                    $row['TranStatus'] = $bmArr[$fi + 1];
                    $row['ThirdCustId'] = $bmArr[$fi + 2];
                    $row['CustAcctId'] = $bmArr[$fi + 3];
                    $row['CustAcctName'] = $bmArr[$fi + 4];
                    $row['TranAmount'] = $bmArr[$fi + 5];
                    $row['TranDate'] = $bmArr[$fi + 6];
                    $row['TranTime'] = $bmArr[$fi + 7];
                    $row['FrontLogNo'] = $bmArr[$fi + 8];
                    $row['ThirdLogNo'] = $bmArr[$fi + 9];
                    $row['Note'] = $bmArr[$fi + 10];
                    $row['Note2'] = $bmArr[$fi + 11];

                    $arr['rows'][] = $row;
                }
                break;
            case 6080:
                $arr['TotalCount'] = intval($bmArr[0]);
                $arr['BeginNum'] = intval($bmArr[1]);
                $arr['LastPage'] = intval($bmArr[2]);
                $arr['RecordNum'] = intval($bmArr[3]);
                $arr['Reserve'] = $bmArr[$arr['RecordNum'] * 17 + 4];
                $arr['rows'] = array();
                for ($i = 0; $i < $arr['RecordNum']; $i++) {
                    $row = array();
                    $fi = 4 + $i * 17;
                    $row['TranFlag'] = $bmArr[$fi];
                    $row['TranStatus'] = $bmArr[$fi + 1];
                    $row['TranAmount'] = $bmArr[$fi + 2];
                    $row['HandFee'] = $bmArr[$fi + 3];
                    $row['TranDate'] = $bmArr[$fi + 4];
                    $row['TranTime'] = $bmArr[$fi + 5];
                    $row['FrontLogNo'] = $bmArr[$fi + 6];
                    $row['ThirdLogNo'] = $bmArr[$fi + 7];
                    $row['ThirdHtId'] = $bmArr[$fi + 8];
                    $row['OutCustAcctId'] = $bmArr[$fi + 9];
                    $row['OutThirdCustId'] = $bmArr[$fi + 10];
                    $row['OutCustAcctName'] = $bmArr[$fi + 11];
                    $row['InCustAcctId'] = $bmArr[$fi + 12];
                    $row['InThirdCustId'] = $bmArr[$fi + 13];
                    $row['InCustAcctName'] = $bmArr[$fi + 14];
                    $row['Note'] = $bmArr[$fi + 15];
                    $row['Note2'] = $bmArr[$fi + 16];

                    $arr['rows'][] = $row;
                }
                break;
            default:
                break;
        }
    }

    private function send($hTranFunc, $hThirdLogNo, $tranMessageBody)
    {
        //建立连接
        if (!$this->conn()) {
            //连接失败
            $arr = array();
            $arr['RspCode'] = '201';
            $arr['RspMsg'] = '连接服务器失败';
            Logger::error('连接服务器失败');
            return $arr;
        }
        $tranMessageBody = iconv('utf-8', 'gbk//TRANSLIT', $tranMessageBody);
        $in = $this->data($hTranFunc, $hThirdLogNo, $tranMessageBody);
        socket_write($this->socket, $in, strlen($in)); //发送数据
        $outAll = '';
        try {
            while ($out = socket_read($this->socket, 8192)) {
                $outAll .= $out; //接收数据
            }
        } catch (\Exception $e) {
            $outAll = false;
            Logger::error("连接平安银行服务失败:" . $e->getMessage());
        }
        $this->outMessage = iconv("GBK", "UTF-8", $outAll);
        socket_close($this->socket); //断开连接
        return $this->parsingTranMessageString($outAll);
    }

    public function action($hThirdLogNo, $hTranFunc, $arr = array())
    {
        $tranMessageBody = '';
        $arr["SupAcctId"] = $this->bSupAcctId;
        switch ($hTranFunc) {
//---------会员注册与绑定---------
            case 6027://查询大小额联行号----因为绑卡的时候需要上送大小额联行号，该接口用于获取大小额联行号。
                $intf = '查询大小额联行号';
                $tranMessageBody .= isset($arr['BankNo']) ? $arr['BankNo'] . "&" : "&";
                $tranMessageBody .= isset($arr['KeyWord']) ? $arr['KeyWord'] . "&" : "&";
                $tranMessageBody .= isset($arr['BankName']) ? $arr['BankName'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6000: //会员子账户开立【6000】
                $intf = '会员子账户开立';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustProperty']) ? $arr['CustProperty'] . "&" : "&";
                $tranMessageBody .= isset($arr['NickName']) ? $arr['NickName'] . "&" : "&";
                $tranMessageBody .= isset($arr['MobilePhone']) ? $arr['MobilePhone'] . "&" : "&";
                $tranMessageBody .= isset($arr['Email']) ? $arr['Email'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6055: //会员绑定提现账户-小额鉴权【6055】
                $intf = '会员绑定提现账户-小额鉴权';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustName']) ? $arr['CustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdType']) ? $arr['IdType'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdCode']) ? $arr['IdCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['AcctId']) ? $arr['AcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['BankType']) ? $arr['BankType'] . "&" : "&";
                $tranMessageBody .= isset($arr['BankName']) ? $arr['BankName'] . "&" : "&";
                $tranMessageBody .= isset($arr['BankCode']) ? $arr['BankCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['SBankCode']) ? $arr['SBankCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['MobilePhone']) ? $arr['MobilePhone'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6064://验证鉴权金额【6064】
                $intf = '验证鉴权金额';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['AcctId']) ? $arr['AcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6066://会员绑定提现账户-银联验证【6066】
                $intf = '会员绑定提现账户-银联验证';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustName']) ? $arr['CustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdType']) ? $arr['IdType'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdCode']) ? $arr['IdCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['AcctId']) ? $arr['AcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['BankType']) ? $arr['BankType'] . "&" : "&";
                $tranMessageBody .= isset($arr['BankName']) ? $arr['BankName'] . "&" : "&";
                $tranMessageBody .= isset($arr['BankCode']) ? $arr['BankCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['SBankCode']) ? $arr['SBankCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['MobilePhone']) ? $arr['MobilePhone'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6067://验证短信验证码【6067】
                $intf = '验证短信验证码';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['AcctId']) ? $arr['AcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['MessageCode']) ? $arr['MessageCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6065://会员解绑提现账户【6065】
                $intf = '会员解绑提现账户';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['AcctId']) ? $arr['AcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
//---------会员清分与提现---------
            case 6056://会员清分【6056】
                $intf = '会员清分';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6005: //会员提现【6005】（验密）
                $intf = '会员提现';
                $tranMessageBody .= isset($arr['TranWebName']) ? $arr['TranWebName'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdType']) ? $arr['IdType'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdCode']) ? $arr['IdCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustName']) ? $arr['CustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutAcctId']) ? $arr['OutAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutAcctIdName']) ? $arr['OutAcctIdName'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                $tranMessageBody .= isset($arr['WebSign']) ? $arr['WebSign'] . "&" : "&";
                break;
            case 6033://会员提现【6033】
                $intf = '会员提现';
                $tranMessageBody .= isset($arr['TranWebName']) ? $arr['TranWebName'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdType']) ? $arr['IdType'] . "&" : "&";
                $tranMessageBody .= isset($arr['IdCode']) ? $arr['IdCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustName']) ? $arr['CustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutAcctId']) ? $arr['OutAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutAcctIdName']) ? $arr['OutAcctIdName'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                $tranMessageBody .= isset($arr['WebSign']) ? $arr['WebSign'] . "&" : "&";
                break;
            case 6085://会员提现（支持手续费）【6085】
                $intf = '会员提现（支持手续费）';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustName']) ? $arr['CustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutAcctId']) ? $arr['OutAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutAcctIdName']) ? $arr['OutAcctIdName'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['HandFee']) ? $arr['HandFee'] . "&" : "&";
                $tranMessageBody .= isset($arr['SerialNo']) ? $arr['SerialNo'] . "&" : "&";
                $tranMessageBody .= isset($arr['MessageCode']) ? $arr['MessageCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                $tranMessageBody .= isset($arr['WebSign']) ? $arr['WebSign'] . "&" : "&";
                break;
            case 6008://登记挂账【6008】
                $intf = '登记挂账';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustName']) ? $arr['CustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6053://会员批量清分【6053】
                $intf = '会员批量清分';
                $custAcctArr = isset($arr['custAcctArr']) ? $arr['custAcctArr'] : null;
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctArr']) ? count(CustAcctArr) . "&" : "&";
                foreach ($custAcctArr as $val) {
                    $tranMessageBody .= isset($val['CustAcctId']) ? $val['CustAcctId'] . "&" : "&";
                    $tranMessageBody .= isset($val['ThirdCustId']) ? $val['ThirdCustId'] . "&" : "&";
                    $tranMessageBody .= isset($val['TranAmount']) ? $val['TranAmount'] . "&" : "&";
                    $tranMessageBody .= isset($val['Note']) ? $val['Note'] . "&" : "&";
                    $tranMessageBody .= isset($val['MarketLogNo']) ? $val['MarketLogNo'] . "&" : "&";
                }
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
//---------会员买卖交易---------
            case 6006://会员交易【6006】（验密）
                $intf = '会员交易(验密)';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustAcctId']) ? $arr['OutCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutThirdCustId']) ? $arr['OutThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustName']) ? $arr['OutCustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['InCustAcctId']) ? $arr['InCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['InThirdCustId']) ? $arr['InThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['InCustName']) ? $arr['InCustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranFee']) ? $arr['TranFee'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranType']) ? $arr['TranType'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtId']) ? $arr['ThirdHtId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtMsg']) ? $arr['ThirdHtMsg'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                $tranMessageBody .= isset($arr['WebSign']) ? $arr['WebSign'] . "&" : "&";
                break;
            case 6034://会员交易【6034】
                $intf = '会员交易';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustAcctId']) ? $arr['OutCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutThirdCustId']) ? $arr['OutThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustName']) ? $arr['OutCustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['InCustAcctId']) ? $arr['InCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['InThirdCustId']) ? $arr['InThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['InCustName']) ? $arr['InCustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranFee']) ? $arr['TranFee'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranType']) ? $arr['TranType'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtId']) ? $arr['ThirdHtId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtMsg']) ? $arr['ThirdHtMsg'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                $tranMessageBody .= isset($arr['WebSign']) ? $arr['WebSign'] . "&" : "&";
                break;
            case 6052://会员批量交易【6052】
                //FuncFlag,OutCustAcctId,OutThirdCustId,SupAcctId,ThirdHtCount,
                //Array
                //InCustAcctId,InThirdCustId,TranAmount,TranFee,CcyCode,ThirdHtId,ThirdHtMsg,Note,MarketLogNo
                //Array
                //Reserve,WebSign
                $intf = '会员批量交易';
                $orderArr = isset($arr['orderArr']) ? $arr['orderArr'] : null;
                $arr['ThirdHtCount'] = count($orderArr);
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustAcctId']) ? $arr['OutCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutThirdCustId']) ? $arr['OutThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtCount']) ? $arr['ThirdHtCount'] . "&" : "&";
                foreach ($orderArr as $val) {
                    $tranMessageBody .= isset($val['InCustAcctId']) ? $val['InCustAcctId'] . "&" : "&";
                    $tranMessageBody .= isset($val['InThirdCustId']) ? $val['InThirdCustId'] . "&" : "&";
                    $tranMessageBody .= isset($val['TranAmount']) ? $val['TranAmount'] . "&" : "&";
                    $tranMessageBody .= isset($val['TranFee']) ? $val['TranFee'] . "&" : "&";
                    $tranMessageBody .= isset($val['CcyCode']) ? $val['CcyCode'] . "&" : "&";
                    $tranMessageBody .= isset($val['ThirdHtId']) ? $val['ThirdHtId'] . "&" : "&";
                    $tranMessageBody .= isset($val['ThirdHtMsg']) ? $val['ThirdHtMsg'] . "&" : "&";
                    $tranMessageBody .= isset($val['Note']) ? $val['Note'] . "&" : "&";
                    $tranMessageBody .= isset($val['MarketLogNo']) ? $val['MarketLogNo'] . "&" : "&";
                }
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                $tranMessageBody .= isset($arr['WebSign']) ? $arr['WebSign'] . "&" : "&";
                echo $tranMessageBody;
                die();
                break;
            case 6031://平台订单管理【6031】
                $intf = '平台订单管理';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustAcctId']) ? $arr['OutCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutThirdCustId']) ? $arr['OutThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustName']) ? $arr['OutCustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['InCustAcctId']) ? $arr['InCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['InThirdCustId']) ? $arr['InThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['InCustName']) ? $arr['InCustName'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranFee']) ? $arr['TranFee'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranType']) ? $arr['TranType'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtId']) ? $arr['ThirdHtId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtMsg']) ? $arr['ThirdHtMsg'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6007://会员资金冻结【6007】
                $intf = '会员资金冻结';
// String tranMessageBody=FuncFlag+"&"+SupAcctId+"&"+CustAcctId+"&"+ThirdCustId+"&"+TranAmount+"&"+TranFee+"&"+CcyCode+"&"+ThirdHtId+"&"+ThirdHtMsg+"&"+Note+"&"+Reserve+"&";
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranFee']) ? $arr['TranFee'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtId']) ? $arr['ThirdHtId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtMsg']) ? $arr['ThirdHtMsg'] . "&" : "&";
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;

            case 6070://会员资金支付【6070】
                $intf = '会员资金支付';
                $custAcctArr = isset($arr['custAcctArr']) ? $arr['custAcctArr'] : null;
                $arr['TotalCount'] = count($custAcctArr);
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutCustAcctId']) ? $arr['OutCustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['OutThirdCustId']) ? $arr['OutThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['HandFee']) ? $arr['HandFee'] . "&" : "&";
                $tranMessageBody .= isset($arr['CcyCode']) ? $arr['CcyCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtId']) ? $arr['ThirdHtId'] . "&" : "&";
                $tranMessageBody .= isset($arr['TotalCount']) ? $arr['TotalCount'] . "&" : "&";
                foreach ($custAcctArr as $val) {
                    $tranMessageBody .= isset($val['InCustAcctId']) ? $val['InCustAcctId'] . "&" : "&";
                    $tranMessageBody .= isset($val['InThirdCustId']) ? $val['InThirdCustId'] . "&" : "&";
                    $tranMessageBody .= isset($val['TranAmount']) ? $val['TranAmount'] . "&" : "&";
                }
                $tranMessageBody .= isset($arr['Note']) ? $arr['Note'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
//
                break;
            case 6077://交易撤销【6077】
                $intf = '交易撤销';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['OrigThirdLogNo']) ? $arr['OrigThirdLogNo'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
//---------短信验证类交易---------
            case 6082://申请短信动态码【6082】
                $intf = '申请短信动态码';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranType']) ? $arr['TranType'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranAmount']) ? $arr['TranAmount'] . "&" : "&";
                $tranMessageBody .= isset($arr['AcctId']) ? $arr['AcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdHtId']) ? $arr['ThirdHtId'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranNote']) ? $arr['TranNote'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6083: //申请修改手机号码【6083】
                $intf = '申请修改手机号码';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ModifiedType']) ? $arr['ModifiedType'] . "&" : "&";
                $tranMessageBody .= isset($arr['NewMobilePhone']) ? $arr['NewMobilePhone'] . "&" : "&";
                $tranMessageBody .= isset($arr['AcctId']) ? $arr['AcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6084://回填动态码-修改手机【6084】
                $intf = '回填动态码-修改手机';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ModifiedType']) ? $arr['ModifiedType'] . "&" : "&";
                $tranMessageBody .= isset($arr['SerialNo']) ? $arr['SerialNo'] . "&" : "&";
                $tranMessageBody .= isset($arr['MessageCode']) ? $arr['MessageCode'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
//---------查询类交易---------
            case 6010: //查询银行子账户余额【6010】
                $intf = '查询银行子账户余额';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SelectFlag']) ? $arr['SelectFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['PageNum']) ? $arr['PageNum'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6014: //查询银行单笔交易明细【6014】
                $intf = '查询银行单笔交易明细';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['OrigThirdLogNo']) ? $arr['OrigThirdLogNo'] . "&" : "&";
                $tranMessageBody .= isset($arr['TranDate']) ? $arr['TranDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6048: //查询银行提现退单信息【6048】
                $intf = '查询银行提现退单信息';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['BeginDate']) ? $arr['BeginDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['EndDate']) ? $arr['EndDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6050: //查询普通转账充值明细【6050】
                $intf = '查询普通转账充值明细';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['BeginDate']) ? $arr['BeginDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['EndDate']) ? $arr['EndDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['PageNum']) ? $arr['PageNum'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6072://查询银行时间段内交易明细【6072】
                $intf = '查询银行时间段内交易明细';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SelectFlag']) ? $arr['SelectFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['BeginDate']) ? $arr['BeginDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['EndDate']) ? $arr['EndDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['PageNum']) ? $arr['PageNum'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6073://查询银行时间段内清分提现明细【6073】
                $intf = '查询银行时间段内清分提现明细';
                $tranMessageBody .= isset($arr['FuncFlag']) ? $arr['FuncFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SelectFlag']) ? $arr['SelectFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['BeginDate']) ? $arr['BeginDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['EndDate']) ? $arr['EndDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['PageNum']) ? $arr['PageNum'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6011://查询资金汇总账户余额【6011】
                $intf = '查询资金汇总账户余额';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6037://查询会员子账号【6037】
                $intf = '查询会员子账号';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
//---------对账类交易---------
            case 6079: //提现与清分对账接口【6079】
                $intf = '提现与清分对账接口';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SelectFlag']) ? $arr['SelectFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['BeginDate']) ? $arr['BeginDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['EndDate']) ? $arr['EndDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['PageNum']) ? $arr['PageNum'] . "&" : "&";
                $tranMessageBody .= isset($arr['RecordMax']) ? $arr['RecordMax'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            case 6080://会员交易明细对账接口【6080】
                $intf = '会员交易明细对账接口';
                $tranMessageBody .= isset($arr['SupAcctId']) ? $arr['SupAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['CustAcctId']) ? $arr['CustAcctId'] . "&" : "&";
                $tranMessageBody .= isset($arr['SelectFlag']) ? $arr['SelectFlag'] . "&" : "&";
                $tranMessageBody .= isset($arr['BeginDate']) ? $arr['BeginDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['EndDate']) ? $arr['EndDate'] . "&" : "&";
                $tranMessageBody .= isset($arr['PageNum']) ? $arr['PageNum'] . "&" : "&";
                $tranMessageBody .= isset($arr['RecordMax']) ? $arr['RecordMax'] . "&" : "&";
                $tranMessageBody .= isset($arr['Reserve']) ? $arr['Reserve'] . "&" : "&";
                break;
            default:
                return array("RspCode" => "err400", "RspMsg" => "交易码不正确");
                break;
        }
        //传入：业务报文体tranMessageBody
        $start = microtime(true) * 1000;
        $ret = $this->send($hTranFunc, $hThirdLogNo, $tranMessageBody);
        $end = microtime(true) * 1000;
        $pabapi_logdata = array(
            "thirdlogno" => $hThirdLogNo,
            "tranfunc" => $hTranFunc,
            "intf" => $intf,
            "inparm" => json_encode($arr, JSON_UNESCAPED_UNICODE),
            "outparm" => json_encode($ret, JSON_UNESCAPED_UNICODE),
            'thirdcustid' => isset($arr['ThirdCustId']) ? $arr['ThirdCustId'] : null,
            'custacctid' => isset($arr['CustAcctId']) ? $arr['CustAcctId'] : null,
            'rspcode' => isset($ret['RspCode']) ? $ret['RspCode'] : null,
            'rspmsg' => isset($ret['RspMsg']) ? $ret['RspMsg'] : null,
            'created' => time(),
            'elapsed_time' => $end - $start,
            'tranmessage' => $this->tranMessage,
            'outmessage' => $this->outMessage
        );
        D('PabapiLog')->addData($pabapi_logdata);
        return $ret;
    }
}