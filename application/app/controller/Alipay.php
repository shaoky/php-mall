<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 9:32
 */

namespace app\app\controller;

//use \vendor\alipay\aop;

use think\Controller;
use Think\Model;

use app\h5\model\Order;
use app\apphm\model\ShopOrder;
require_once "../vendor/lib/alipay/aop/AopClient.php";


//use vendor\alipay\aop\AopClient;
class Alipay extends Controller
{
    //    正式
    private $APPID = "";
    private $Method = "";
    private $CharSet = "utf-8";
    private $SignType = "";
    private $SellerID = "";
    private $Version = "1.0";
    private $format = "JSON";
    private $GetWayUrl = "";
    private $Str = "";
    //    private $Publicstr = "MIIEpQIBAAKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQABAoIBAQCICrITdGJVmKLEqJgfZUmXvFsfanWYUXv+9Ak7c0+1Cs/2Ak65D7PAL79iaNRNXR6CLd6cSYd3JcGpuo4ji3zvHOWESn61C9Wq3GytDTvfnrLAjZOfnhc0l9ukibSm8bs26nWxdt+ZRx7BDE1vfg53uN5bcysyecMVFPRy099ksBEER6fKbVvs8j96pATTyXx9xSKejAIm/efGiVmFvgDJ/GYI2jlbUi/WZAq4RhZlfHtwZxaDofx3p4AV8RC8ac6H+vH57ck14o34AGy8n9TcHZWW0msg+nuvasFEV+hTkE+MNq+J7AiLELvri/EEAyQjPS2MHNUTx7h3HRRmKtlhAoGBAODVyjioTs6nG+chTuPYlaRm9rehPOgi8ZYBo85wcIkUTh65m0Qqq8UdNaYLyvHiiwErZclvBbrq8o33C9i9yiTuzwPBY3ErW01ZffsQgOnMAGLqRMfWO8R+KzpOTrDucLz5N+5p0pyFrmuz4AeNH+AmfdGQizPQzxPEjVHsvjjzAoGBAMB4KIDEhk+piYDBK91y5/raxSVY1lJnT+uFq+nmLmFABYK7hAva6Z0e+GHVYRKP4NSdc6i20imMpKncFBwlkmLMgbW2tsPLdtj8qcwdxUGgdVJidiP2WlTqHxot5x+P9FiAFn8g0BsUJqvabUrqOUJtEiKGaniAj66nGClTmO5HAoGBANwIPdxO0rkpD4SVs0gGKQR4g5DYKewgbn9YYX/hyDBO/9jP2iTPAcVpPZyfw+AOzey6FOYaUehIkFHg6OYXbuHQGCZbtz8EqFi9cS4oS5ECxYoILIHJpjo5I4LvKdG7t/coitU3GRd3OGhM471gHkbWRKG3rJNsEYwN+iOn5c2DAoGAQzMw4RS82F+XVM8dY6pU+K8dmlaULh1IODFpvrbRhr71rZmPFxyN6IABtRbtV6/IjjOTSFrcsgkbDPt8Qf+URs6kky+CTIZjros5Nb1HPAysKeGa4h+HlpJP3bIdOlXmuGewcx6fynSUs/gFpNx2tYyjgk5wKjao9A//TZ6UjM0CgYEAz5/5eGNn2qG0GELekNx4BML3lkmNDGTrmrIJl37/uKsIaZaYLk0sEPFa7f+9X+UFMC5JLWdj2nps9wYRBaxaAUZRrDVNB4r13eD2Vj1kpqENHLC8m2hauchiQlak+4YxZbdIL+nq6jjuRNWLv8bVlp1oepHBzXc9HPL2AXgSKdc=";
    private $Publicstr = "";
//    private $Publicstr = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxoHJ8qkfqJTC0jU8wDd0dtpaGAP+lDPhq6hLb0YWXKs/MPhY8f3QgT0xkxMXXq+S+V8UF69DIvFRliEIJxiK5AEdv9CLTrKGsMs/m0OLYQbKmaQ1CbQIbC8OzVWe5Ogqf2UcGSirK+B0XCIzMIkeNnIwjRa8BGU/oGsfVX6TUFJSVXYbb4WjWq6BX5V8i5BYsnpKEVOpz185aSHnQKRahP1LABinwdVtlm9r2tJZdSaysrFnM0fvMXG662hqPNDti0XKJ4DyivjpeG2ntQj4cadCMI042PtBn5zhjKw/Dz7dzNBJEjOllIieLzFauVggi2PbfphF5pfNOdJmlUlF3wIDAQAB";
    //沙箱
//    private $APPID = "2016072300103638";
//    private $Method = "alipay.trade.app.pay";
//    private $CharSet = "utf-8";
//    private $SignType = "RSA2";
//    private $SellerID = "2088102168688109";
//    private $Version = "1.0";
//    private $format = "JSON";
//    private $GetWayUrl = "https://openapi.alipaydev.com/gateway.do";
//    private $Str = "MIIEpQIBAAKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQABAoIBAQCICrITdGJVmKLEqJgfZUmXvFsfanWYUXv+9Ak7c0+1Cs/2Ak65D7PAL79iaNRNXR6CLd6cSYd3JcGpuo4ji3zvHOWESn61C9Wq3GytDTvfnrLAjZOfnhc0l9ukibSm8bs26nWxdt+ZRx7BDE1vfg53uN5bcysyecMVFPRy099ksBEER6fKbVvs8j96pATTyXx9xSKejAIm/efGiVmFvgDJ/GYI2jlbUi/WZAq4RhZlfHtwZxaDofx3p4AV8RC8ac6H+vH57ck14o34AGy8n9TcHZWW0msg+nuvasFEV+hTkE+MNq+J7AiLELvri/EEAyQjPS2MHNUTx7h3HRRmKtlhAoGBAODVyjioTs6nG+chTuPYlaRm9rehPOgi8ZYBo85wcIkUTh65m0Qqq8UdNaYLyvHiiwErZclvBbrq8o33C9i9yiTuzwPBY3ErW01ZffsQgOnMAGLqRMfWO8R+KzpOTrDucLz5N+5p0pyFrmuz4AeNH+AmfdGQizPQzxPEjVHsvjjzAoGBAMB4KIDEhk+piYDBK91y5/raxSVY1lJnT+uFq+nmLmFABYK7hAva6Z0e+GHVYRKP4NSdc6i20imMpKncFBwlkmLMgbW2tsPLdtj8qcwdxUGgdVJidiP2WlTqHxot5x+P9FiAFn8g0BsUJqvabUrqOUJtEiKGaniAj66nGClTmO5HAoGBANwIPdxO0rkpD4SVs0gGKQR4g5DYKewgbn9YYX/hyDBO/9jP2iTPAcVpPZyfw+AOzey6FOYaUehIkFHg6OYXbuHQGCZbtz8EqFi9cS4oS5ECxYoILIHJpjo5I4LvKdG7t/coitU3GRd3OGhM471gHkbWRKG3rJNsEYwN+iOn5c2DAoGAQzMw4RS82F+XVM8dY6pU+K8dmlaULh1IODFpvrbRhr71rZmPFxyN6IABtRbtV6/IjjOTSFrcsgkbDPt8Qf+URs6kky+CTIZjros5Nb1HPAysKeGa4h+HlpJP3bIdOlXmuGewcx6fynSUs/gFpNx2tYyjgk5wKjao9A//TZ6UjM0CgYEAz5/5eGNn2qG0GELekNx4BML3lkmNDGTrmrIJl37/uKsIaZaYLk0sEPFa7f+9X+UFMC5JLWdj2nps9wYRBaxaAUZRrDVNB4r13eD2Vj1kpqENHLC8m2hauchiQlak+4YxZbdIL+nq6jjuRNWLv8bVlp1oepHBzXc9HPL2AXgSKdc=";
//    private $publicstr = "MIIEpgIBAAKCAQEAxoHJ8qkfqJTC0jU8wDd0dtpaGAP+lDPhq6hLb0YWXKs/MPhY8f3QgT0xkxMXXq+S+V8UF69DIvFRliEIJxiK5AEdv9CLTrKGsMs/m0OLYQbKmaQ1CbQIbC8OzVWe5Ogqf2UcGSirK+B0XCIzMIkeNnIwjRa8BGU/oGsfVX6TUFJSVXYbb4WjWq6BX5V8i5BYsnpKEVOpz185aSHnQKRahP1LABinwdVtlm9r2tJZdSaysrFnM0fvMXG662hqPNDti0XKJ4DyivjpeG2ntQj4cadCMI042PtBn5zhjKw/Dz7dzNBJEjOllIieLzFauVggi2PbfphF5pfNOdJmlUlF3wIDAQABAoIBAQCWFuwqndViEZh8d+RHMabNdqtIHT0PsWqf78+ZgtTFnHVbAWu/DLnp/erN2VGCNh5ajpkSj2GjufKGINOZO+VIH8/uiC/MgQ91/TdKnfd0lT36jT3M6GK16Z63glVxnlkDmd905lsGAd/5CaLKv+IoKI6tHtNqP2OsV1KJwfqAb2f/mmooNHTcbDW3VIUAi86HEHh7NLpgP45iMgjgdmtByoXkbYgXlxeydZMJTt5MM8uRIiJ3rVIK6623Z06s5MO8RX87xkHpIKwcP69HV1z1Denv1dqT/EsTcUjav1Pj2SviqYiycSaTEFBD3KV/sWqcKtmwDFNy3BV9PqSK/scBAoGBAPPVUsH2FvsZmuJS3EyxqAAcB5k+ohW+rLilqJ76t7GVUWt+4ESa/ghFUFpnzDVC3epQ3LjHUJCFoiJQCQGIm0iExNpAYR4uXmp9yM8IYlBnqoFVfg15N9qoABCGzhMgj/Kpl/FM7aJfz9Wb8AzUlcbmkcYBiBGiooIqTDSG+SypAoGBANBpegKVoG8hk9EfrrU9QvJngH9U5gn/Cgwz+QS5+88VQIo0VAORzvfTKR/WiZ18LA8yZTx29hRmOPGd05k0O6z0meyB1jDcttJRIPFvsh/ACFS/SU8IWwG1leRi0Z0+YiDOTLOLcVze95agagHyhxynG1+pVpzrBIrTkuqBMatHAoGBAJ/my2jLW3K77ehx8mqRLt0zhIAopN6htl84tAXd4U0h4M0f3S7gfasq2XEQFMjw2trowHakrt635AWgeud4kGzUNMvz4DyMp/oqFuA+XUrhUN2UFR/82HYaKkCsRsQgXIYEVdI2YrhTINaydo/TcY2IcxxosLTv9KZr2qGvygRRAoGBAMI4KV3TNqmZ7vjJv7RinMrtrY/7/EyAgkqQ/JVtBL8dHQIudcTF0syc2gwzkBdVKnIXEsTgT77+7D4uSIzTYnFxmSgQbx0l8rHYrQySfLqd6KWhhZ3DOZxELqWheZpCEoxj9JK1djWIC88xOtkJvOP0zsu9MVwWoCsHtDgZocHtAoGBAOzEkT6W1UP+IOdYkVYzWyRI05niKv7tbx8RD2KcJO+xh3YhDx6jiIDuKSisnP/5KuAYgFu8cmSzLhB3IM76zeZbb0Q6zoW64rtv8kcrsTqR+BcWvlxpJYQRtKryCfbs/1SF9Ij203zpn1kc4syP2y7Noja/fgDCtNXsHoZ9w2bz";
        private $timestamp;
//公共参数
    private $alipay_public_key;

    public function __construct()
    {
//        $this->client = new \AopClient();
//        $config = D('Config')->queryConfigByGroup('ALIPAY', 'APP');
//        $this->client->appId = $config['appId'];
//        $this->appId = $config['appId'];
//        $this->seller_id = $config['seller_id'];
//        $this->client->rsaPrivateKeyFilePath = SVN_ROOT . $config['rsaPrivateKeyFilePath'];
//        $this->client->gatewayUrl = $config['gatewayUrl'];
        $this->client = new \AopClient();
        $this->client->appId = $this->APPID;
        $this->appId = $this->APPID;
//        $str = "MIICXAIBAAKBgQCsPRO3y00AFiZUp0ZSMTKybXKG4TTGCwnYnWNEIf3zkezorLk+P6Q7OU52c96Rn7SaE93l30WExwqIMn77gZ37JKIeTNOpYRN3JMi56i/OranwzX4kI66Rgd8DqUNZJPh4SU4blFOWz0dpDiHUOu3nfmylrWDniT2cQiGvG/81TwIDAQABAoGAaVhh+wQpcGgbwaMd65+TtnYimkScFpUhzI9W65Eu1g7mq9kY4+3g8Sg3yVNNShw4RhTon3RT/MYQtwTLjeDVUohUW9zaAwt3XG/M1UPDHrsjL3rzpD/XaNPAykti5LZmAer4EzYRi5uEv1mP6ow79KV8RalD+P2zc58QCTj9FMECQQDlU0BgyBDeI2j6xyB5o5jLpV/b3uHdtWTDrGXd6qH5JtQP5KpSOvPGvHRMGnTm89xp53U+a9D6XNtvIU6hdd43AkEAwEXsp5GwcGAty7tqr56C5AYVDlD246xTu2cgaWTtENMYer8pfBtvrawSRlaKMaBeta+0KoinrR87LNmlkzMVqQJAEGzUEZqCysZSgJO+mbfD++IOaZsfJlgVrYYtjaV+kG/dhH3mTRl32GYrFkBW2yvgP52aYiwqvaPk+6KxFRNyJQJAfRI/QjWUB1WZFKjiWkELNdb7BWgHYcxsX0BTJhvf1gzrJJWLH88dE2UWMikDFgsS8K7NzMk+AXCKZr9hZGJbmQJBAKAqZWeopOQxlY4JdCgX0sxjEEqEWlks8yGYPbsz11PORT1IJi9lQTo6Kps4g+8tET0lbs6Z8UVaS4PphMmMcN4=";
//        $str = "MIIEpgIBAAKCAQEAxoHJ8qkfqJTC0jU8wDd0dtpaGAP+lDPhq6hLb0YWXKs/MPhY8f3QgT0xkxMXXq+S+V8UF69DIvFRliEIJxiK5AEdv9CLTrKGsMs/m0OLYQbKmaQ1CbQIbC8OzVWe5Ogqf2UcGSirK+B0XCIzMIkeNnIwjRa8BGU/oGsfVX6TUFJSVXYbb4WjWq6BX5V8i5BYsnpKEVOpz185aSHnQKRahP1LABinwdVtlm9r2tJZdSaysrFnM0fvMXG662hqPNDti0XKJ4DyivjpeG2ntQj4cadCMI042PtBn5zhjKw/Dz7dzNBJEjOllIieLzFauVggi2PbfphF5pfNOdJmlUlF3wIDAQABAoIBAQCWFuwqndViEZh8d+RHMabNdqtIHT0PsWqf78+ZgtTFnHVbAWu/DLnp/erN2VGCNh5ajpkSj2GjufKGINOZO+VIH8/uiC/MgQ91/TdKnfd0lT36jT3M6GK16Z63glVxnlkDmd905lsGAd/5CaLKv+IoKI6tHtNqP2OsV1KJwfqAb2f/mmooNHTcbDW3VIUAi86HEHh7NLpgP45iMgjgdmtByoXkbYgXlxeydZMJTt5MM8uRIiJ3rVIK6623Z06s5MO8RX87xkHpIKwcP69HV1z1Denv1dqT/EsTcUjav1Pj2SviqYiycSaTEFBD3KV/sWqcKtmwDFNy3BV9PqSK/scBAoGBAPPVUsH2FvsZmuJS3EyxqAAcB5k+ohW+rLilqJ76t7GVUWt+4ESa/ghFUFpnzDVC3epQ3LjHUJCFoiJQCQGIm0iExNpAYR4uXmp9yM8IYlBnqoFVfg15N9qoABCGzhMgj/Kpl/FM7aJfz9Wb8AzUlcbmkcYBiBGiooIqTDSG+SypAoGBANBpegKVoG8hk9EfrrU9QvJngH9U5gn/Cgwz+QS5+88VQIo0VAORzvfTKR/WiZ18LA8yZTx29hRmOPGd05k0O6z0meyB1jDcttJRIPFvsh/ACFS/SU8IWwG1leRi0Z0+YiDOTLOLcVze95agagHyhxynG1+pVpzrBIrTkuqBMatHAoGBAJ/my2jLW3K77ehx8mqRLt0zhIAopN6htl84tAXd4U0h4M0f3S7gfasq2XEQFMjw2trowHakrt635AWgeud4kGzUNMvz4DyMp/oqFuA+XUrhUN2UFR/82HYaKkCsRsQgXIYEVdI2YrhTINaydo/TcY2IcxxosLTv9KZr2qGvygRRAoGBAMI4KV3TNqmZ7vjJv7RinMrtrY/7/EyAgkqQ/JVtBL8dHQIudcTF0syc2gwzkBdVKnIXEsTgT77+7D4uSIzTYnFxmSgQbx0l8rHYrQySfLqd6KWhhZ3DOZxELqWheZpCEoxj9JK1djWIC88xOtkJvOP0zsu9MVwWoCsHtDgZocHtAoGBAOzEkT6W1UP+IOdYkVYzWyRI05niKv7tbx8RD2KcJO+xh3YhDx6jiIDuKSisnP/5KuAYgFu8cmSzLhB3IM76zeZbb0Q6zoW64rtv8kcrsTqR+BcWvlxpJYQRtKryCfbs/1SF9Ij203zpn1kc4syP2y7Noja/fgDCtNXsHoZ9w2bz";
        $str = $this->Str;
        $this->seller_id = $this->SellerID;
//        $this->client->rsaPrivateKeyFilePath = SVN_ROOT."\application\common\saPrivateKeyFilePath.pem";
        $this->client->rsaPrivateKeyFilePath = $str;
        $this->alipay_public_key = $this->Publicstr;
        $this->client->gatewayUrl = $this->GetWayUrl;
        // $this->notifyUrl = $this->NotifyUrl;
        $this->notifyUrl = config('app.alipay_app_notify_url');
        $this->timestamp = date("Y-m-d H:i:s");
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
        $product_code = 'QUICK_MSECURITY_PAY'; //	销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        $id = 1;
        $total_amount = $this->input->request('total_amount', 0); //订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]

        $subject = $this->input->request('subject', ''); //商品的标题/交易标题/订单标题/订单关键字等。
        //支付订单号
        $arr2['payOrderNo'] = $out_trade_no;
        $arr2['payMoney'] = $total_amount;
        $arr2['userId'] = $id;
        $arr2['createTime'] = time();
        $arr2['payStatus'] = 1;
        $arr2['thirdNo'] = 1;
        db("pay")->data($arr2)->insert();

//        $rs = $this->createPayListObj()->insert($arr2);
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
//        $obj = new paylist_Model();
//        return $obj;
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

    public function payUrlAlipay($orderNo)
    {
        //业务参数
        $orderType = substr($orderNo, 0 ,2);
        $resorder = [];
        $shop = [];
        if ($orderType == '10') {
            $resorder = db('order')->where('orderNo', $orderNo)->find();
            }
        if ($orderType == '30') {
            $resorder = db('shop_order')->where('orderNo', $orderNo)->find();
            $shop = db('shop')->where('shopId', $resorder['shopId'])->find();
            $resorder['userName'] = $shop['shopName'];
        }
        // $resorder = db("order")->where($where)->find();
        $id = $resorder['userId'];
        $total_amount = $resorder['payableMoney'];
        $subject = $resorder['userName'];
        $result = $this->basePay($id, $subject, $total_amount, $orderNo,$resorder['userId']);
        $this->output_log_file(json_encode($result));
//        echo json_encode($result);

        return resultArray(['data' => $result]);
    }

    public function basePay($id, $subject, $total_amount, $ord_no,$userId, $type = 'd', $balance = 0, $points = 0)
    {
        $product_code = 'QUICK_MSECURITY_PAY'; //	销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY

        //支付订单号
//        $out_trade_no = time() . $this->code(4);
        $arr2['out_trade_no'] = $ord_no;
        $arr2['product_code'] = $product_code;
        $arr2['subject'] = $subject;
        $arr2['timeout_express'] = '30m';
        $arr2['total_amount'] = $total_amount;

        $biz_content = $this->createBiz_content($arr2);

        $arr['app_id'] = $this->appId;
        $arr['biz_content'] = $biz_content;
        $arr['charset'] = $this->CharSet;
        $arr['format'] = "json";
        $arr['method'] = $this->Method;
        $arr['notify_url'] = $this->notifyUrl;
        $arr['sign_type'] = $this->SignType;
        $arr['timestamp'] = $this->timestamp;
        $arr['version'] = "1.0";

        unset($arr2['product_code']);
        unset($arr2['timeout_express']);
        $arr3['orderNo'] = $ord_no;
        $arr3['payMoney'] = $total_amount;
        $arr3['payType'] = 1;
        $arr3['payStatus'] = 1;
        $arr3['thirdNo'] = 1;
        $arr3['userId'] = $userId;
        $arr3['createTime'] = time();
        db("pay")->data($arr3)->insert();

//        $rs = $this->createPayListObj()->insert($arr2);

//        if (!$rs) $this->output_result_alipay('1', '账单存储出现问题');

        $c = new \AopClient();

        $c->rsaPrivateKey = $this->client->rsaPrivateKeyFilePath;

        $sign = $c->generateSign($arr, $this->SignType);

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
        $this->output_log_file("\r\n" . "支付宝调用一步验证");
        $param = $_REQUEST;
        $this->output_log_file("\r\n" . json_encode($param));
        $aop = new \AopClient();
        //支付宝公钥
        $whereStatus['orderNo'] = $param['out_trade_no'];
        $checkOrder = db("pay")->where($whereStatus)->find();
        if ($checkOrder['payStatus'] == 3) {
            $this->output_result_alipay('0', '验证成功，已返回支付宝服务器！', true);
        }else {
            $aop->alipayrsaPublicKey = $this->alipay_public_key;
            $this->output_log_file("APP支付宝调用验证");
            $result = $aop->rsaCheckV1($param, $this->Publicstr, $this->SignType);
            $this->output_log_file('result:'.$result);
            $this->output_log_file("\r\n" . "APP支付宝调用二步验证");
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

//            项目逻辑处理开始
                
                $arrStatus['payStatus'] = 3;
                $arrStatus['thirdOrderNo'] = $param['out_trade_no'];
                $arrStatus['updateTime'] = time();
                $orderType = substr($param['out_trade_no'], 0 ,2);
                if ($orderType == '10') {
                    $order = db('order')->where('orderNo', $param['out_trade_no'])->find();
                }
                if ($orderType == '30') {
                    $order = db('shop_order')->where('orderNo', $param['out_trade_no'])->find();
                }
                // $orderid = db("order")->where($whereStatus)->find();
                //插入total_amount验证
                if (!$order)
                    $this->output_result_alipay('5', '查无此单。');

//            $total_amount = $order['payableMoney'];
//
//            if ($param['total_amount'] != $total_amount)
//                $this->output_result_alipay('6', '金额验证出现问题。');
                $this->output_log_file("查看该订单信息");
                $this->output_log_file(json_encode($order));
                $this->output_log_file("订单编号：" . $param['out_trade_no']);
                if ($orderType == '10') {
                    $res = Order::payOrder(['orderNo' => $param['out_trade_no'], 'payMoney' => $param['total_amount'], 'payType' => 1]);
                } 
                if ($orderType == '30') {
                    $res = ShopOrder::payOrder(['orderNo' => $param['out_trade_no'], 'payMoney' => $param['total_amount'], 'payType' => 1]);
                }
                
                $this->output_log_file(json_encode($res));
                db("pay")->where($whereStatus)->update($arrStatus);
                /**
                 * 支付记录新增
                 */
                $this->output_log_file("\r\n" . "支付宝调用记录新增开始");
                db("log_alipay")->data($param)->insert();
                $this->output_log_file("\r\n" . "支付宝调用记录新增结束");
                $this->output_log_file("异步调用结束了");
//                项目逻辑处理结束
                $this->output_result_alipay('0', '验证成功，已返回支付宝服务器！', true);
            } else {
                $this->output_result_alipay('3', '支付验证出错。');
            }
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
        $this->output_log_file('同步验证开始');
        $data = input("post.");
        $this->output_log_file(json_encode($data));
//        $params = "{".$data['resultInfo']."}";
        $params = $data['resultInfo'];
//        $params = json_decode($params,true);
        $aop = new \AopClient();
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

            $this->output_log_file("aaaaaaaaaaaaaaaaaaaaaaa");
        } else
            $this->output_error(2002);

        $this->output_log_file("bbbbbbbbbbbbbbbbbbbb");

        //插入app_id验证
        if ($param['app_id'] != $this->appId)
            $this->output_result_alipay('4', 'app_id验证出现问题。');

        //插入sell_id验证
        if ($param['seller_id'] != $this->SellerID)
            $this->output_result_alipay('2', 'seller_id验证出现问题。');
//        $order = new Order();
        $orderType = substr($param['out_trade_no'], 0 ,2);
        if ($orderType == '10') {
            $order = db('order')->where('orderNo', $param['out_trade_no'])->find();
        }
        if ($orderType == '30') {
            $order = db('shop_order')->where('orderNo', $param['out_trade_no'])->find();
        }
        $this->output_log_file("查看该订单信息");
        // $whereStatus['orderNo'] = $param['out_trade_no'];
        // $order = db("order")->where($whereStatus)->find();
        //插入total_amount验证
        $this->output_log_file(json_encode($order));
        if (!$order)
            $this->output_result_alipay('5', '查无此单。');

        $total_amount = $order['payableMoney'];
        $this->output_log_file($total_amount);
        $this->output_log_file(json_encode($param));
        if ($param['total_amount'] != $total_amount){
            $this->output_result_alipay('6', '金额验证出现问题。');
        }
        $this->output_log_file($param['total_amount']);
        $this->output_log_file("到这里了就是支付好了");

        return resultArray(['data' => "支付成功"]);
//        return resultArray(['data' => "支付成功"]);
//        $this->output_result();
    }

    protected function output_result_alipay($error_num, $errinfo = '', $result = false)
    {
        if ($errinfo) $str = json_encode(array('err_code' => $error_num . '', 'err_info' => $errinfo));
        else $str = json_encode(array('errcode' => $error_num . ''));

//        $str = preg_replace("/\\\u([0-9a-f]{4})/ie", "mb_convert_encoding(pack('V', hexdec('U$1')),'UTF-8','UCS-4LE')", $str);

//        $this->output_log_file("\r\n".$str);

        if ($result)
//            return [
//                'message' => 'success'
//            ];
            echo 'success';
        else
//            return [
//                'message' => "fail,error_num:" . $error_num
//            ];
            echo "fail,error_num:" . $error_num;

        exit;
    }


    /**
     * @author liuchaohang
     * @date 2017/12/14
     * @param $type
     * @param $id
     * @param $str
     */
    public function output_log_file($str, $type = "alipay", $id = "app")
    {
        $date = date('Y-m-d');
        if (PHP_OS == 'Linux') {
//            $path = DOCROOT . "logs/$type/$date";
////        var_dump($path);
            $path = "/var/log";
            $filename = $path . '/' . $id . ".log";
//        } else {
//            $path = DOCROOT . "logs\\$type\\$date";
////        var_dump($path);
//            $filename = $path . '\\' . $id . ".log";
//        }
//        var_dump($path);
//        if (!is_dir($filename)) {
//            mkdir($filename, 0777, true);
//        }
            $files = fopen($filename, 'a');
//        var_dump($filename);
            fwrite($files, "\r\n" . $str);
            fclose($files);
        }
    }
}