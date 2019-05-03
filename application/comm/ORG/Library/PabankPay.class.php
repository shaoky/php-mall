<?php

namespace Common\ORG\Library;

class PabankPay
{

    private $pkey = '';

    /**
     * @param string $pkey
     */
    public function setPkey($pkey)
    {
        $this->pkey = $pkey;
    }

    /**
     * ===================================================================
     * sign
     * ===================================================================
     */

    /**
     * get orig
     * @param string $data
     * @return string
     */
    public function getOrig($data)
    {
        $orig = $this->_base64_url_encode($data);
        return $orig;
    }

    /**
     * get sign
     * @param file $merchantCertFile
     * @param string $data
     * @return string
     */
    public function getSign($merchantCertFile, $data)
    {
        $sign = $this->_getSign($merchantCertFile, $data);
        $sign = $this->_base64_url_encode($sign);
        return $sign;
    }

    /**
     * sign by open_ssl
     * @param file $merchantCertFile
     * @param string $data
     * @return string
     */
    private function _getSign($merchantCertFile, $data)
    {
        $tCertificate = array();

        if (openssl_pkcs12_read(file_get_contents($merchantCertFile), $tCertificate, $this->pkey)) {
            $pkey = openssl_pkey_get_private($tCertificate['pkey']);
        }

        $signature = '';

        if (!openssl_sign($data, $signature, $pkey, OPENSSL_ALGO_MD5)) {
            exit("Have a error!Please check!");
        }
        $sign = bin2hex($signature);
        return $sign;
    }

    /**
     * to base64 and url
     * @param string $data
     * @return string
     */
    private function _base64_url_encode($data)
    {
        $data_base64 = base64_encode($data);           //base64

        $data_url = urlencode($data_base64);               //url
        return $data_url;
    }

    /**
     * ===================================================================
     * validate
     * ===================================================================
     */

    /**
     * 验证签名
     * @param string $orig
     * @param string $sign
     * @param file $tTrustPayCertFile
     * @return bool
     */
    public function validate($orig, $sign, $tTrustPayCertFile)
    {
        if (substr(PHP_VERSION, 0, 3) <= 5.3) {
            return $this->_validate_5_3($orig, $sign, $tTrustPayCertFile);
        }
        //进制转换
        $tSign = $this->hex2bin(trim($sign));
        //组装可用的证书
        $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode(file_get_contents($tTrustPayCertFile)), 64, "\n") . "-----END CERTIFICATE-----\n";
        //获取key
        $iTrustpayCertificate = openssl_x509_read($pem);
        $key = openssl_pkey_get_public($iTrustpayCertificate);
        openssl_free_key($key);
        return openssl_verify(trim($orig), $tSign, $key, OPENSSL_ALGO_MD5);
    }

    /**
     * 验证签名，PHP版本低于5.3（包括5.3的需要调用这个方法）
     * @param string $orig
     * @param string $sign
     * @param file $tTrustPayCertFile
     * @return bool
     */
    private function _validate_5_3($orig, $sign, $tTrustPayCertFile)
    {
        //进制转换
        $tSign = $this->hex2bin(trim($sign));
        //组装可用的证书
        $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode(file_get_contents($tTrustPayCertFile)), 64, "\n") . "-----END CERTIFICATE-----\n";
        //获取key
        $key = openssl_pkey_get_public($pem);
        return openssl_verify(trim($orig), $tSign, $key, OPENSSL_ALGO_MD5);
    }

    /**
     * 进制转换
     * @param type $hexdata
     * @return type
     */
    function hex2bin($hexdata)
    {
        $bindata = '';
        $length = strlen($hexdata);
        for ($i = 0; $i < $length; $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }

    public function _base64_url_decode($data)
    {

        $data_url = urldecode($data);


        $data_base64 = base64_decode($data_url);           //base64

        return $data_base64;
    }

    /**
     * ===================================================================
     * base
     * ===================================================================
     */

    /**
     * 将数据组装为xml格式的数据
     * @param array $data
     * @return string
     *
     * $data = '<kColl id="input" append="false"><field id="masterId" value="2000311146"/><field id="orderId" value="20003111462015060473550416"/><field id="currency" value="RMB"/><field id="amount" value="0.01"/><field id="objectName" value="��"/><field id="paydate" value="20150604143506"/><field id="remark" value="��"/><field id="validtime" value="0"/></kColl>';
     */
    public function array_to_xml($data)
    {
        $xml_data = '<kColl id="input" append="false">';
        foreach ($data as $key => $value) {
            $value = iconv("UTF-8", "GBK", $value);   //utf-8 to gbk
            $xml_data .= '<field id="' . $key . '" value="' . $value . '"/>';
        }
        $xml_data .= '</kColl>';

        return $xml_data;
    }

    /**
     * 将xml数据转换为array数组
     * @param type $orig_xml
     * @return type
     */
    public function xml_to_array($orig_xml)
    {
        $orig_xml = iconv("GBK", "UTF-8", $orig_xml);   // gbk to utf-8
        $orig_data = simplexml_load_string($orig_xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $result_data = array();
        foreach ($orig_data->field as $key => $row) {

            $item = $row->attributes();
            $id = $this->xml_attribute($item, 'id');
            $value = $this->xml_attribute($item, 'value') ? $this->xml_attribute($item, 'value') : '';
            $result_data[$id] = $value;
        }

        if ($orig_data->iColl) {
            $items = array();
            foreach ($orig_data->iColl->kColl as $key => $row) {
                $val = array();
                foreach ($row->field as $key1 => $row1) {
                    $item = $row1->attributes();
                    $id = $this->xml_attribute($item, 'id');
                    $value = $this->xml_attribute($item, 'value');
                    $val[$id] = $value;
                }
                $items[] = $val;
            }
            $item = $orig_data->iColl->attributes();
            $id = $this->xml_attribute($item, 'id');
            $result_data[$id] = $items;
        }
        return $result_data;
    }

    //来自于PHP官方网站
    private function xml_attribute($object, $attribute)
    {
        if (isset($object[$attribute])) {
            return (string)$object[$attribute];
        }
    }

    /**
     * ===================================================================
     * curl
     * ===================================================================
     */

    /**
     * PHP模拟POST的HTTPS的请求
     * @param string $url
     * @param string $parms
     * @return string
     */
    public function curl($url, $parms)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parms);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //HTTPS����
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 获取签名过后的原始数据orig和签名数据sign
     * @param file $merchantCertFile
     * @param string $data
     * @return list
     */
    public function getOrigAndSing($merchantCertFile, $data)
    {


        $orig = $this->getOrig($data);
        $sign = $this->getSign($merchantCertFile, $data);

        return array($orig, $sign);
    }

    /**
     * 将数据组装为xml格式的数据
     * @param array $data
     * @return string
     *
     * $data = '<kColl id="input" append="false"><field id="masterId" value="2000311146"/><field id="orderId" value="20003111462015060473550416"/><field id="currency" value="RMB"/><field id="amount" value="0.01"/><field id="objectName" value="��"/><field id="paydate" value="20150604143506"/><field id="remark" value="��"/><field id="validtime" value="0"/></kColl>';
     */
    public function getXmlData($data)
    {
        $xml_data = '<kColl id="input" append="false">';
        foreach ($data as $key => $value) {
            $xml_data .= '<field id="' . $key . '" value="' . $value . '"/>';
        }
        $xml_data .= '</kColl>';

        return $xml_data;
    }

    /**
     * 进行数据提交
     * @param array $parameter
     */
    public function showHtml($parameter, $gateway)
    {
        $html = '<html><head><meta charset="UTF-8" /></head><body>';
        $html .= '<form method="post" name="P_FORM" id="myForm"  action="' . $gateway . '">';
        foreach ($parameter as $key => $val) {
            $html .= "<input type='hidden' name='$key' value='$val' />";
        }
        $html .= '</form><script type="text/javascript">  document.getElementById("myForm").submit(); </script>';
        $html .= '</body></html>';
        return $html;
    }

}
