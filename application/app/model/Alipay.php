<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/8
 * Time: 17:30
 */

namespace app\app\model;


use app\h5\model\Common;
use think\Model;

//use app\h5\model\Order;
require_once "../vendor/lib/alipay/aop/AopClient.php";
require_once "../vendor/lib/alipay/aop/request/AlipayTradeRefundRequest.php";
class Alipay extends Model
{
//    APP
    private $APPID = "2018091161385080";
    private $Method = "alipay.trade.refund";
    private $CharSet = "utf-8";
    private $SignType = "RSA2";
    private $SellerID = "2088221376380055";
    private $Version = "1.0";
    private $format = "JSON";
    private $NotifyUrl = "https://api.mall.shaoky.com/app/alipay/checkRsaSign";
    private $GetWayUrl = "https://openapi.alipay.com/gateway.do";
    private $Str = "MIIEpgIBAAKCAQEAxoHJ8qkfqJTC0jU8wDd0dtpaGAP+lDPhq6hLb0YWXKs/MPhY8f3QgT0xkxMXXq+S+V8UF69DIvFRliEIJxiK5AEdv9CLTrKGsMs/m0OLYQbKmaQ1CbQIbC8OzVWe5Ogqf2UcGSirK+B0XCIzMIkeNnIwjRa8BGU/oGsfVX6TUFJSVXYbb4WjWq6BX5V8i5BYsnpKEVOpz185aSHnQKRahP1LABinwdVtlm9r2tJZdSaysrFnM0fvMXG662hqPNDti0XKJ4DyivjpeG2ntQj4cadCMI042PtBn5zhjKw/Dz7dzNBJEjOllIieLzFauVggi2PbfphF5pfNOdJmlUlF3wIDAQABAoIBAQCWFuwqndViEZh8d+RHMabNdqtIHT0PsWqf78+ZgtTFnHVbAWu/DLnp/erN2VGCNh5ajpkSj2GjufKGINOZO+VIH8/uiC/MgQ91/TdKnfd0lT36jT3M6GK16Z63glVxnlkDmd905lsGAd/5CaLKv+IoKI6tHtNqP2OsV1KJwfqAb2f/mmooNHTcbDW3VIUAi86HEHh7NLpgP45iMgjgdmtByoXkbYgXlxeydZMJTt5MM8uRIiJ3rVIK6623Z06s5MO8RX87xkHpIKwcP69HV1z1Denv1dqT/EsTcUjav1Pj2SviqYiycSaTEFBD3KV/sWqcKtmwDFNy3BV9PqSK/scBAoGBAPPVUsH2FvsZmuJS3EyxqAAcB5k+ohW+rLilqJ76t7GVUWt+4ESa/ghFUFpnzDVC3epQ3LjHUJCFoiJQCQGIm0iExNpAYR4uXmp9yM8IYlBnqoFVfg15N9qoABCGzhMgj/Kpl/FM7aJfz9Wb8AzUlcbmkcYBiBGiooIqTDSG+SypAoGBANBpegKVoG8hk9EfrrU9QvJngH9U5gn/Cgwz+QS5+88VQIo0VAORzvfTKR/WiZ18LA8yZTx29hRmOPGd05k0O6z0meyB1jDcttJRIPFvsh/ACFS/SU8IWwG1leRi0Z0+YiDOTLOLcVze95agagHyhxynG1+pVpzrBIrTkuqBMatHAoGBAJ/my2jLW3K77ehx8mqRLt0zhIAopN6htl84tAXd4U0h4M0f3S7gfasq2XEQFMjw2trowHakrt635AWgeud4kGzUNMvz4DyMp/oqFuA+XUrhUN2UFR/82HYaKkCsRsQgXIYEVdI2YrhTINaydo/TcY2IcxxosLTv9KZr2qGvygRRAoGBAMI4KV3TNqmZ7vjJv7RinMrtrY/7/EyAgkqQ/JVtBL8dHQIudcTF0syc2gwzkBdVKnIXEsTgT77+7D4uSIzTYnFxmSgQbx0l8rHYrQySfLqd6KWhhZ3DOZxELqWheZpCEoxj9JK1djWIC88xOtkJvOP0zsu9MVwWoCsHtDgZocHtAoGBAOzEkT6W1UP+IOdYkVYzWyRI05niKv7tbx8RD2KcJO+xh3YhDx6jiIDuKSisnP/5KuAYgFu8cmSzLhB3IM76zeZbb0Q6zoW64rtv8kcrsTqR+BcWvlxpJYQRtKryCfbs/1SF9Ij203zpn1kc4syP2y7Noja/fgDCtNXsHoZ9w2bz";
    //    private $Publicstr = "MIIEpQIBAAKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQABAoIBAQCICrITdGJVmKLEqJgfZUmXvFsfanWYUXv+9Ak7c0+1Cs/2Ak65D7PAL79iaNRNXR6CLd6cSYd3JcGpuo4ji3zvHOWESn61C9Wq3GytDTvfnrLAjZOfnhc0l9ukibSm8bs26nWxdt+ZRx7BDE1vfg53uN5bcysyecMVFPRy099ksBEER6fKbVvs8j96pATTyXx9xSKejAIm/efGiVmFvgDJ/GYI2jlbUi/WZAq4RhZlfHtwZxaDofx3p4AV8RC8ac6H+vH57ck14o34AGy8n9TcHZWW0msg+nuvasFEV+hTkE+MNq+J7AiLELvri/EEAyQjPS2MHNUTx7h3HRRmKtlhAoGBAODVyjioTs6nG+chTuPYlaRm9rehPOgi8ZYBo85wcIkUTh65m0Qqq8UdNaYLyvHiiwErZclvBbrq8o33C9i9yiTuzwPBY3ErW01ZffsQgOnMAGLqRMfWO8R+KzpOTrDucLz5N+5p0pyFrmuz4AeNH+AmfdGQizPQzxPEjVHsvjjzAoGBAMB4KIDEhk+piYDBK91y5/raxSVY1lJnT+uFq+nmLmFABYK7hAva6Z0e+GHVYRKP4NSdc6i20imMpKncFBwlkmLMgbW2tsPLdtj8qcwdxUGgdVJidiP2WlTqHxot5x+P9FiAFn8g0BsUJqvabUrqOUJtEiKGaniAj66nGClTmO5HAoGBANwIPdxO0rkpD4SVs0gGKQR4g5DYKewgbn9YYX/hyDBO/9jP2iTPAcVpPZyfw+AOzey6FOYaUehIkFHg6OYXbuHQGCZbtz8EqFi9cS4oS5ECxYoILIHJpjo5I4LvKdG7t/coitU3GRd3OGhM471gHkbWRKG3rJNsEYwN+iOn5c2DAoGAQzMw4RS82F+XVM8dY6pU+K8dmlaULh1IODFpvrbRhr71rZmPFxyN6IABtRbtV6/IjjOTSFrcsgkbDPt8Qf+URs6kky+CTIZjros5Nb1HPAysKeGa4h+HlpJP3bIdOlXmuGewcx6fynSUs/gFpNx2tYyjgk5wKjao9A//TZ6UjM0CgYEAz5/5eGNn2qG0GELekNx4BML3lkmNDGTrmrIJl37/uKsIaZaYLk0sEPFa7f+9X+UFMC5JLWdj2nps9wYRBaxaAUZRrDVNB4r13eD2Vj1kpqENHLC8m2hauchiQlak+4YxZbdIL+nq6jjuRNWLv8bVlp1oepHBzXc9HPL2AXgSKdc=";
    private $Publicstr = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsEtL7rgJED+xu/0QZBohIpHbYcXvR8nPvWr12UraZO+Gr6ZqohLlbCaw8f5nJ4SD9Jn5vjVRtdQMTbwDPgF0mk3jMM06xr3Ez5XczARuUmpVmr5K2RbA1Tv9SizEOn39b0EvtX24lWdhtZlyb3fUb4YCsvUEn7Z3Rvsw6uPrxEw+K2x3XjXVRmnBEjGsAjCnAZnF5bbSnnh+yNOp7qlXnWAJ4U541oU5FVRckYTKmfqjp1xnoiu2qDTiVrZIdak0CGBgSY1YEcNjuAEkIlkDAhmJfF9sqVCBeeQOxpXwqo4/cGmjawaPf0rYrhywS9l9b86aNQUK10CL83KnKMBP5wIDAQAB";
    private $timestamp;
//公共参数
    private $alipay_public_key;
//    H5
    private $H5APPID = "2018091261360255";
//    private $H5SellerID = "2088221376380055";
//    private $H5NotifyUrl = "http://api.mall.shaoky.com/common/alipay/checkRsaSign";
//    private $H5GetWayUrl = "https://openapi.alipay.com/gateway.do";
//    privateH5 $alipay_public_key;
    private $H5Str = "MIIEogIBAAKCAQEAkkSIXSQ6r672Z3d0nfS9jJPToxTtvQ9qRrTOCQpnjmOxoqKYXrLbhuOGnuvWKfBvnVmJJJUeJ1LqbSoahX7NnE85JaCAG6x69A8FxMaws6WnnZAnAxCWXzppmZQ4jU2HQhwNjHCLIk/8sTsvGRV6dWfzYfnNM1E82gMgfH8vljBpm+Nd2KDSIBPmQZdgwyW1Wpx1d+FDH+JtO4Nk9Y9by2t3VInbRdYcAeQuarJUUPKU8J9QqGoQbobUJXpAwqxpzqrzlnjmFQxNZfK3arXFhMG2nXl0LUEv2+MA81JIKOXds/ou/oHI9rmkojtZNn7bfRaz+0p8hM3K5lJNAAyULwIDAQABAoIBAHxIKy4pSIxauBf2UmqcuCqouR1K/S612HG305Gu+4rrftZYHevpQcr9rTd/4HvGRrCGkZc59WMoevTC38Iqmn6EemRUWkYpalfSI+6tfRtIWIHzKJLcF0lXEr9iSCMYiyNLPt/kCPTgzi+xwDUqIOF9sDnBzfAayP/dxE7EqySLOXgekQl6YaIYSBaK38STkFgZtmvRmaXT3xZOEzQJKSXJ6quYJOX83PWD3jAnmWerS9gUaedAOQ4cOs16tHrtBa2Y6KDLVBUManaV2ge0Yc8nL4uCFDf363sXJYbRkJO5f+wBnGzxeLSdZX9ymavh6B6kq0NUiCfqV6dECWGyWfECgYEAwmwJwl2aEjFhs3ICxmSb6IPYx6w2WvoNARn/C+h1Bw7PEZjP8EbHEwsvNJVuUVdzaB2+V3Kqt6R+wDzvJiZAZ5mHz7coYe2BB7VXMjYuBC+8e3atN7AAr4dJ3xBFRVhPSYQGUL6cX7t9TW38+WI3abjbE5b8Q+OwffYEWwLao9MCgYEAwJgWP5UMmfnGl0GKcRbCtZZ6DozvHQljbQWf/2oTN+Fdg8O2YpGshZCLRXwNo00IsrmOY6xwAcDb246iLEQX7qE7tzuGhSoD2ZiERibm++S5eZ/YDWeLFsFt9jav92FV4SDgPHGcXPJ/jSAbgQTRfUHMIFdcWvpFNyivn6w2QLUCgYAeeE4jPwYpecHFHMYgj537l8Y+rNer9GpbVNaq6BiHLaP4LwnPr1f9vQ/93rl2WA1HMPomvFrTAx22RRZ51R7c3avyOtNCXbWOHrZ2sRUPJOgWNWg6RQQmfj5ujh89GfT7IYzX1LevLTQbEXTHJMRodkcnT89jWGjNpOPT26OmbwKBgBBamcEKagiFIizj3tqdgSkkPvcPVpqTmoK+cIiivEEJfQRcURdroUeyL3FWCF5ugyeSBBpIoTY05zZslS7HFXUo8cYsYs7Z83u9rLW8Re7rzCgcv57bxP7IrAq1OBn8hOtZ/FCFoS5vVQ9pw4wgGZ3WQXwZjvkWuVvms4sOd4jlAoGATmT+8mLyx1oFS6OULLy9gnuAzgHrSXTEAF7bvW/Ov+FWcf7gfMg9vkG5MmsSk0G0Q2uV8R4HjZblJ3jtxWt3npuns8Yuy6OTnzTGF1Adn+77QHod9szjmgdk9yVjn8wmXYtHyrBZgb3c43MLtxQ9+zlpZOPnkgKiH/sP/Qi29Ms=";
    //    priH5vate $rsaStr = "MIIEpQIBAAKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQABAoIBAQCICrITdGJVmKLEqJgfZUmXvFsfanWYUXv+9Ak7c0+1Cs/2Ak65D7PAL79iaNRNXR6CLd6cSYd3JcGpuo4ji3zvHOWESn61C9Wq3GytDTvfnrLAjZOfnhc0l9ukibSm8bs26nWxdt+ZRx7BDE1vfg53uN5bcysyecMVFPRy099ksBEER6fKbVvs8j96pATTyXx9xSKejAIm/efGiVmFvgDJ/GYI2jlbUi/WZAq4RhZlfHtwZxaDofx3p4AV8RC8ac6H+vH57ck14o34AGy8n9TcHZWW0msg+nuvasFEV+hTkE+MNq+J7AiLELvri/EEAyQjPS2MHNUTx7h3HRRmKtlhAoGBAODVyjioTs6nG+chTuPYlaRm9rehPOgi8ZYBo85wcIkUTh65m0Qqq8UdNaYLyvHiiwErZclvBbrq8o33C9i9yiTuzwPBY3ErW01ZffsQgOnMAGLqRMfWO8R+KzpOTrDucLz5N+5p0pyFrmuz4AeNH+AmfdGQizPQzxPEjVHsvjjzAoGBAMB4KIDEhk+piYDBK91y5/raxSVY1lJnT+uFq+nmLmFABYK7hAva6Z0e+GHVYRKP4NSdc6i20imMpKncFBwlkmLMgbW2tsPLdtj8qcwdxUGgdVJidiP2WlTqHxot5x+P9FiAFn8g0BsUJqvabUrqOUJtEiKGaniAj66nGClTmO5HAoGBANwIPdxO0rkpD4SVs0gGKQR4g5DYKewgbn9YYX/hyDBO/9jP2iTPAcVpPZyfw+AOzey6FOYaUehIkFHg6OYXbuHQGCZbtz8EqFi9cS4oS5ECxYoILIHJpjo5I4LvKdG7t/coitU3GRd3OGhM471gHkbWRKG3rJNsEYwN+iOn5c2DAoGAQzMw4RS82F+XVM8dY6pU+K8dmlaULh1IODFpvrbRhr71rZmPFxyN6IABtRbtV6/IjjOTSFrcsgkbDPt8Qf+URs6kky+CTIZjros5Nb1HPAysKeGa4h+HlpJP3bIdOlXmuGewcx6fynSUs/gFpNx2tYyjgk5wKjao9A//TZ6UjM0CgYEAz5/5eGNn2qG0GELekNx4BML3lkmNDGTrmrIJl37/uKsIaZaYLk0sEPFa7f+9X+UFMC5JLWdj2nps9wYRBaxaAUZRrDVNB4r13eD2Vj1kpqENHLC8m2hauchiQlak+4YxZbdIL+nq6jjuRNWLv8bVlp1oepHBzXc9HPL2AXgSKdc=";
    private $H5Publicstr = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsEtL7rgJED+xu/0QZBohIpHbYcXvR8nPvWr12UraZO+Gr6ZqohLlbCaw8f5nJ4SD9Jn5vjVRtdQMTbwDPgF0mk3jMM06xr3Ez5XczARuUmpVmr5K2RbA1Tv9SizEOn39b0EvtX24lWdhtZlyb3fUb4YCsvUEn7Z3Rvsw6uPrxEw+K2x3XjXVRmnBEjGsAjCnAZnF5bbSnnh+yNOp7qlXnWAJ4U541oU5FVRckYTKmfqjp1xnoiu2qDTiVrZIdak0CGBgSY1YEcNjuAEkIlkDAhmJfF9sqVCBeeQOxpXwqo4/cGmjawaPf0rYrhywS9l9b86aNQUK10CL83KnKMBP5wIDAQAB";
    private $H5timestamp;
    /**
     * 获取退款url
     * @api {post} /app/alipay/back 1. 支付宝支付退款申请
     * @apiName Alipay
     * @apiGroup h5PayGroup
     * @apiVersion 1.0.0
     * @param $orderNo
     */
    public function Back($orderNo,$thirdNo,$out_request_no,$payMoney,$orderFrom){
//        $refund_amount = $payMoney;
//        $out_trade_no = $orderNo;
        $this->output_log_file($payMoney."+++".$thirdNo."*****".$orderNo);
        if ($orderFrom == 1){
//           H5付款
            $result = $this->H5BasePays($thirdNo,$payMoney, $orderNo,$out_request_no);
        }else{
//            APP付款
            $result = $this->basePays($thirdNo,$payMoney, $orderNo,$out_request_no);
        }

        $this->output_log_file($result);
//        echo json_encode($result);
        return $result;
//        return resultArray(['data' => $result]);
    }

    public  function basePays($thirdNo,$total_amount, $ord_no,$out_request_no)
    {
//        $product_code = 'QUICK_MSECURITY_PAY'; //	销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
//        $total_amount = 0.01;
//        var_dump($total_amount);
        //支付订单号
//        $out_trade_no = time() . $this->code(4);
//        $where['out_trade_no'] = $ord_no;
//        $respay = db("log_alipay")->where($where)->find();
        $arr2['out_trade_no'] = $ord_no;
//        $arr2['out_trade_no'] = "10201810101448569312";
//        $arr2['product_code'] = $product_code;
        $arr2['refund_amount'] = $total_amount;
        $arr2['trade_no'] = $thirdNo;
//        $arr2['trade_no'] = "2018101022001429900599181354";
        $arr2['refund_reason'] = '正常退款';
        $arr2['out_request_no'] = $out_request_no;
        $arr2['operator_id'] = "OP001";
        $arr2['store_id'] = "NJ_S_001";
        $arr2['terminal_id'] = "NJ_T_001";


        $biz_content = $this->createBiz_content($arr2);

        $arr['app_id'] = $this->APPID;
        $arr['biz_content'] = $biz_content;
        $arr['charset'] = $this->CharSet;
        $arr['format'] = "json";
        $arr['method'] = $this->Method;
        $arr['sign_type'] = $this->SignType;
        $arr['timestamp'] = $this->timestamp;
        $arr['version'] = "1.0";

        $c = new \AopClient();
        $c->appId = $this->APPID;
        $c->rsaPrivateKey = $this->Str;
        $c->alipayrsaPublicKey=$this->Publicstr;
        $c->apiVersion = $this->Version;
        $c->signType = $this->SignType;
        $c->postCharset=$this->CharSet;
        $c->format='json';
        $c->biz_content = $biz_content;
        $c->method = $this->Method;
        $c->timestamp = $this->timestamp;
        $c->version = "1.0";
        $c->gatewayUrl = $this->GetWayUrl;
//        $c->rsaPrivateKey = $this->client->rsaPrivateKeyFilePath;

        $sign = $c->generateSign($arr, $this->SignType);

        $arr['sign'] = $sign;

//        $result['err_code'] = 0;
//        $result['url'] = $this->createLinkstring($arr);
        $request = new \AlipayTradeRefundRequest ();
//        var_dump(json_encode($biz_content));
        $request->setBizContent($biz_content);
//        echo 13;
        $result = $c->execute ( $request);
//        echo 46;
//        var_dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
//        var_dump($resultCode);
        $this->output_log_file(json_encode($result));
        if(!empty($resultCode)&&$resultCode == 10000){
//            return "退款成功";
            return 1;
        } else {
//            return "退款失败";
            return 2;
        }
//        return $result;
    }
    /**
     * @author liuchaohang
     * @date 2017/12/14
     * @param $type
     * @param $id
     * @param $str
     */
    public function output_log_file( $str)
    {
        $date = date('Y-m-d');
        if (PHP_OS == 'Linux') {
            $path =  "/var/log";
//        var_dump($path);
            $filename = $path . '/' . "app.log";
        } else {
//            $path = DOCROOT . "logs\\$type\\$date";
////        var_dump($path);
//            $filename = $path . '\\' . $id . ".log";
        }
//        var_dump($path);
//        if (!is_dir($path)) {
//            mkdir($path, 0777, true);
//        }
        $files = fopen($filename, 'a');
//        var_dump($filename);
        fwrite($files, "\r\n".$str);
        fclose($files);
    }
    public  function H5BasePays($thirdNo,$total_amount, $ord_no,$out_request_no)
    {
        $arr2['out_trade_no'] = $ord_no;
        $arr2['refund_amount'] = $total_amount;
        $arr2['trade_no'] = $thirdNo;
        $arr2['refund_reason'] = '正常退款';
        $arr2['out_request_no'] = $out_request_no;
        $arr2['operator_id'] = "OP001";
        $arr2['store_id'] = "NJ_S_001";
        $arr2['terminal_id'] = "NJ_T_001";


        $biz_content = $this->createBiz_content($arr2);

        $arr['app_id'] = $this->H5APPID;
        $arr['biz_content'] = $biz_content;
        $arr['charset'] = $this->CharSet;
        $arr['format'] = "json";
        $arr['method'] = $this->Method;
        $arr['sign_type'] = $this->SignType;
        $arr['timestamp'] = $this->timestamp;
        $arr['version'] = "1.0";

        $c = new \AopClient();
        $c->appId = $this->H5APPID;
        $c->rsaPrivateKey = $this->H5Str;
        $c->alipayrsaPublicKey=$this->H5Publicstr;
        $c->apiVersion = $this->Version;
        $c->signType = $this->SignType;
        $c->postCharset=$this->CharSet;
        $c->format='json';
        $c->biz_content = $biz_content;
        $c->method = $this->Method;
        $c->timestamp = $this->timestamp;
        $c->version = "1.0";
        $c->gatewayUrl = $this->GetWayUrl;
//        $c->rsaPrivateKey = $this->client->rsaPrivateKeyFilePath;

        $sign = $c->generateSign($arr, $this->SignType);

        $arr['sign'] = $sign;

//        $result['err_code'] = 0;
//        $result['url'] = $this->createLinkstring($arr);
        $request = new \AlipayTradeRefundRequest ();
//        var_dump(json_encode($biz_content));
        $request->setBizContent($biz_content);
//        echo 13;
        $result = $c->execute ( $request);
//        echo 46;
//        var_dump($result);
        $this->output_log_file(json_encode($result));
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
//        var_dump($resultCode);
        if(!empty($resultCode)&&$resultCode == 10000){
//            return "退款成功";
            return 1;
        } else {
//            return "退款失败";
            return 2;
        }
//        return $result;
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
    public function RandNo(){
        $str = time();
        $no = substr($str,1,9);
        return $no;
    }
}