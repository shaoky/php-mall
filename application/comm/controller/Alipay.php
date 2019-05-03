<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 9:32
 */

/**
 * H5支付宝付款
 */
namespace app\comm\controller;
//use \vendor\alipay\aop;

use app\h5\model\Order;
use think\Controller;
require_once "../vendor/lib/alipaywap/aop/AopClient.php";
require_once "../vendor/lib/alipaywap/aop/request/AlipayTradeWapPayRequest.php";
//use vendor\alipay\aop\AopClient;
class Alipay extends Controller
{
//    正式
    private $APPID = "2018091261360255";
    private $Method = "alipay.trade.wap.pay";
    private $CharSet = "utf-8";
    private $SignType = "RSA2";
    private $SellerID = "2088221376380055";
    private $Version = "1.0";
    private $format = "JSON";
    private $GetWayUrl = "https://openapi.alipay.com/gateway.do";
//    private $alipay_public_key;
    private $rsaStr = "MIIEogIBAAKCAQEAkkSIXSQ6r672Z3d0nfS9jJPToxTtvQ9qRrTOCQpnjmOxoqKYXrLbhuOGnuvWKfBvnVmJJJUeJ1LqbSoahX7NnE85JaCAG6x69A8FxMaws6WnnZAnAxCWXzppmZQ4jU2HQhwNjHCLIk/8sTsvGRV6dWfzYfnNM1E82gMgfH8vljBpm+Nd2KDSIBPmQZdgwyW1Wpx1d+FDH+JtO4Nk9Y9by2t3VInbRdYcAeQuarJUUPKU8J9QqGoQbobUJXpAwqxpzqrzlnjmFQxNZfK3arXFhMG2nXl0LUEv2+MA81JIKOXds/ou/oHI9rmkojtZNn7bfRaz+0p8hM3K5lJNAAyULwIDAQABAoIBAHxIKy4pSIxauBf2UmqcuCqouR1K/S612HG305Gu+4rrftZYHevpQcr9rTd/4HvGRrCGkZc59WMoevTC38Iqmn6EemRUWkYpalfSI+6tfRtIWIHzKJLcF0lXEr9iSCMYiyNLPt/kCPTgzi+xwDUqIOF9sDnBzfAayP/dxE7EqySLOXgekQl6YaIYSBaK38STkFgZtmvRmaXT3xZOEzQJKSXJ6quYJOX83PWD3jAnmWerS9gUaedAOQ4cOs16tHrtBa2Y6KDLVBUManaV2ge0Yc8nL4uCFDf363sXJYbRkJO5f+wBnGzxeLSdZX9ymavh6B6kq0NUiCfqV6dECWGyWfECgYEAwmwJwl2aEjFhs3ICxmSb6IPYx6w2WvoNARn/C+h1Bw7PEZjP8EbHEwsvNJVuUVdzaB2+V3Kqt6R+wDzvJiZAZ5mHz7coYe2BB7VXMjYuBC+8e3atN7AAr4dJ3xBFRVhPSYQGUL6cX7t9TW38+WI3abjbE5b8Q+OwffYEWwLao9MCgYEAwJgWP5UMmfnGl0GKcRbCtZZ6DozvHQljbQWf/2oTN+Fdg8O2YpGshZCLRXwNo00IsrmOY6xwAcDb246iLEQX7qE7tzuGhSoD2ZiERibm++S5eZ/YDWeLFsFt9jav92FV4SDgPHGcXPJ/jSAbgQTRfUHMIFdcWvpFNyivn6w2QLUCgYAeeE4jPwYpecHFHMYgj537l8Y+rNer9GpbVNaq6BiHLaP4LwnPr1f9vQ/93rl2WA1HMPomvFrTAx22RRZ51R7c3avyOtNCXbWOHrZ2sRUPJOgWNWg6RQQmfj5ujh89GfT7IYzX1LevLTQbEXTHJMRodkcnT89jWGjNpOPT26OmbwKBgBBamcEKagiFIizj3tqdgSkkPvcPVpqTmoK+cIiivEEJfQRcURdroUeyL3FWCF5ugyeSBBpIoTY05zZslS7HFXUo8cYsYs7Z83u9rLW8Re7rzCgcv57bxP7IrAq1OBn8hOtZ/FCFoS5vVQ9pw4wgGZ3WQXwZjvkWuVvms4sOd4jlAoGATmT+8mLyx1oFS6OULLy9gnuAzgHrSXTEAF7bvW/Ov+FWcf7gfMg9vkG5MmsSk0G0Q2uV8R4HjZblJ3jtxWt3npuns8Yuy6OTnzTGF1Adn+77QHod9szjmgdk9yVjn8wmXYtHyrBZgb3c43MLtxQ9+zlpZOPnkgKiH/sP/Qi29Ms=";
    //    private $rsaStr = "MIIEpQIBAAKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQABAoIBAQCICrITdGJVmKLEqJgfZUmXvFsfanWYUXv+9Ak7c0+1Cs/2Ak65D7PAL79iaNRNXR6CLd6cSYd3JcGpuo4ji3zvHOWESn61C9Wq3GytDTvfnrLAjZOfnhc0l9ukibSm8bs26nWxdt+ZRx7BDE1vfg53uN5bcysyecMVFPRy099ksBEER6fKbVvs8j96pATTyXx9xSKejAIm/efGiVmFvgDJ/GYI2jlbUi/WZAq4RhZlfHtwZxaDofx3p4AV8RC8ac6H+vH57ck14o34AGy8n9TcHZWW0msg+nuvasFEV+hTkE+MNq+J7AiLELvri/EEAyQjPS2MHNUTx7h3HRRmKtlhAoGBAODVyjioTs6nG+chTuPYlaRm9rehPOgi8ZYBo85wcIkUTh65m0Qqq8UdNaYLyvHiiwErZclvBbrq8o33C9i9yiTuzwPBY3ErW01ZffsQgOnMAGLqRMfWO8R+KzpOTrDucLz5N+5p0pyFrmuz4AeNH+AmfdGQizPQzxPEjVHsvjjzAoGBAMB4KIDEhk+piYDBK91y5/raxSVY1lJnT+uFq+nmLmFABYK7hAva6Z0e+GHVYRKP4NSdc6i20imMpKncFBwlkmLMgbW2tsPLdtj8qcwdxUGgdVJidiP2WlTqHxot5x+P9FiAFn8g0BsUJqvabUrqOUJtEiKGaniAj66nGClTmO5HAoGBANwIPdxO0rkpD4SVs0gGKQR4g5DYKewgbn9YYX/hyDBO/9jP2iTPAcVpPZyfw+AOzey6FOYaUehIkFHg6OYXbuHQGCZbtz8EqFi9cS4oS5ECxYoILIHJpjo5I4LvKdG7t/coitU3GRd3OGhM471gHkbWRKG3rJNsEYwN+iOn5c2DAoGAQzMw4RS82F+XVM8dY6pU+K8dmlaULh1IODFpvrbRhr71rZmPFxyN6IABtRbtV6/IjjOTSFrcsgkbDPt8Qf+URs6kky+CTIZjros5Nb1HPAysKeGa4h+HlpJP3bIdOlXmuGewcx6fynSUs/gFpNx2tYyjgk5wKjao9A//TZ6UjM0CgYEAz5/5eGNn2qG0GELekNx4BML3lkmNDGTrmrIJl37/uKsIaZaYLk0sEPFa7f+9X+UFMC5JLWdj2nps9wYRBaxaAUZRrDVNB4r13eD2Vj1kpqENHLC8m2hauchiQlak+4YxZbdIL+nq6jjuRNWLv8bVlp1oepHBzXc9HPL2AXgSKdc=";
    private $Publicstr = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsEtL7rgJED+xu/0QZBohIpHbYcXvR8nPvWr12UraZO+Gr6ZqohLlbCaw8f5nJ4SD9Jn5vjVRtdQMTbwDPgF0mk3jMM06xr3Ez5XczARuUmpVmr5K2RbA1Tv9SizEOn39b0EvtX24lWdhtZlyb3fUb4YCsvUEn7Z3Rvsw6uPrxEw+K2x3XjXVRmnBEjGsAjCnAZnF5bbSnnh+yNOp7qlXnWAJ4U541oU5FVRckYTKmfqjp1xnoiu2qDTiVrZIdak0CGBgSY1YEcNjuAEkIlkDAhmJfF9sqVCBeeQOxpXwqo4/cGmjawaPf0rYrhywS9l9b86aNQUK10CL83KnKMBP5wIDAQAB";
   private  $timestamp;
    //    沙箱环境
//    private $APPID = "2016072300103638";
//    private $Method = "alipay.trade.wap.pay";
//    private $CharSet = "utf-8";
//    private $SignType = "RSA2";
//    private $SellerID = "2088102168688109";
//    private $Version = "1.0";
//    private $format = "JSON";
//    private $GetWayUrl = "https://openapi.alipaydev.com/gateway.do";
//    private $alipay_public_key;
//    private $rsaStr = "MIIEpQIBAAKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQABAoIBAQCICrITdGJVmKLEqJgfZUmXvFsfanWYUXv+9Ak7c0+1Cs/2Ak65D7PAL79iaNRNXR6CLd6cSYd3JcGpuo4ji3zvHOWESn61C9Wq3GytDTvfnrLAjZOfnhc0l9ukibSm8bs26nWxdt+ZRx7BDE1vfg53uN5bcysyecMVFPRy099ksBEER6fKbVvs8j96pATTyXx9xSKejAIm/efGiVmFvgDJ/GYI2jlbUi/WZAq4RhZlfHtwZxaDofx3p4AV8RC8ac6H+vH57ck14o34AGy8n9TcHZWW0msg+nuvasFEV+hTkE+MNq+J7AiLELvri/EEAyQjPS2MHNUTx7h3HRRmKtlhAoGBAODVyjioTs6nG+chTuPYlaRm9rehPOgi8ZYBo85wcIkUTh65m0Qqq8UdNaYLyvHiiwErZclvBbrq8o33C9i9yiTuzwPBY3ErW01ZffsQgOnMAGLqRMfWO8R+KzpOTrDucLz5N+5p0pyFrmuz4AeNH+AmfdGQizPQzxPEjVHsvjjzAoGBAMB4KIDEhk+piYDBK91y5/raxSVY1lJnT+uFq+nmLmFABYK7hAva6Z0e+GHVYRKP4NSdc6i20imMpKncFBwlkmLMgbW2tsPLdtj8qcwdxUGgdVJidiP2WlTqHxot5x+P9FiAFn8g0BsUJqvabUrqOUJtEiKGaniAj66nGClTmO5HAoGBANwIPdxO0rkpD4SVs0gGKQR4g5DYKewgbn9YYX/hyDBO/9jP2iTPAcVpPZyfw+AOzey6FOYaUehIkFHg6OYXbuHQGCZbtz8EqFi9cS4oS5ECxYoILIHJpjo5I4LvKdG7t/coitU3GRd3OGhM471gHkbWRKG3rJNsEYwN+iOn5c2DAoGAQzMw4RS82F+XVM8dY6pU+K8dmlaULh1IODFpvrbRhr71rZmPFxyN6IABtRbtV6/IjjOTSFrcsgkbDPt8Qf+URs6kky+CTIZjros5Nb1HPAysKeGa4h+HlpJP3bIdOlXmuGewcx6fynSUs/gFpNx2tYyjgk5wKjao9A//TZ6UjM0CgYEAz5/5eGNn2qG0GELekNx4BML3lkmNDGTrmrIJl37/uKsIaZaYLk0sEPFa7f+9X+UFMC5JLWdj2nps9wYRBaxaAUZRrDVNB4r13eD2Vj1kpqENHLC8m2hauchiQlak+4YxZbdIL+nq6jjuRNWLv8bVlp1oepHBzXc9HPL2AXgSKdc=";
////    private $Publicstr = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQAB";
//    private $Publicstr = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuwtMOBdwTbS8q6iFO88ngIbvWE2yrqmvsmKZUpO2EhUvMlzOC6bGbFuBxFeFsdqua7Vxi2FOm9nrHmHduVLY6+W+lTPH51NF48dH01mYEjk8EcDyHI9a1igHRmMo5BJlQ9AthUzZKXklRHDLgxDXSGTMfJ+Fzb+Z7Yw4ugxAROvfJxL4w+Skw2Zpno1ZRSpst9YAWDDWzEUNZuWweRr4C5UMiyaooM0Unb/694dQ5ylSU8A8jenZxP9iTh3CHmp3JIchLy5KaU6+NhZ7QQ2I9Hcbnp4eFf+QprOPZvVml2ZZsqFQPM1eqoNjdeOzndlH1wrKhBGrBr58AllclubnIwIDAQAB";
    private $ReturnUrl = "http://api.mall.shaoky.com/common/alipay/returnUrl";

    public function __construct()
    {

        $this->client = new \AopClient();
        $this->client->appId = $this->APPID;
//        $this->appId = $this->APPID;
//        $str = "MIIEpgIBAAKCAQEAxoHJ8qkfqJTC0jU8wDd0dtpaGAP+lDPhq6hLb0YWXKs/MPhY8f3QgT0xkxMXXq+S+V8UF69DIvFRliEIJxiK5AEdv9CLTrKGsMs/m0OLYQbKmaQ1CbQIbC8OzVWe5Ogqf2UcGSirK+B0XCIzMIkeNnIwjRa8BGU/oGsfVX6TUFJSVXYbb4WjWq6BX5V8i5BYsnpKEVOpz185aSHnQKRahP1LABinwdVtlm9r2tJZdSaysrFnM0fvMXG662hqPNDti0XKJ4DyivjpeG2ntQj4cadCMI042PtBn5zhjKw/Dz7dzNBJEjOllIieLzFauVggi2PbfphF5pfNOdJmlUlF3wIDAQABAoIBAQCWFuwqndViEZh8d+RHMabNdqtIHT0PsWqf78+ZgtTFnHVbAWu/DLnp/erN2VGCNh5ajpkSj2GjufKGINOZO+VIH8/uiC/MgQ91/TdKnfd0lT36jT3M6GK16Z63glVxnlkDmd905lsGAd/5CaLKv+IoKI6tHtNqP2OsV1KJwfqAb2f/mmooNHTcbDW3VIUAi86HEHh7NLpgP45iMgjgdmtByoXkbYgXlxeydZMJTt5MM8uRIiJ3rVIK6623Z06s5MO8RX87xkHpIKwcP69HV1z1Denv1dqT/EsTcUjav1Pj2SviqYiycSaTEFBD3KV/sWqcKtmwDFNy3BV9PqSK/scBAoGBAPPVUsH2FvsZmuJS3EyxqAAcB5k+ohW+rLilqJ76t7GVUWt+4ESa/ghFUFpnzDVC3epQ3LjHUJCFoiJQCQGIm0iExNpAYR4uXmp9yM8IYlBnqoFVfg15N9qoABCGzhMgj/Kpl/FM7aJfz9Wb8AzUlcbmkcYBiBGiooIqTDSG+SypAoGBANBpegKVoG8hk9EfrrU9QvJngH9U5gn/Cgwz+QS5+88VQIo0VAORzvfTKR/WiZ18LA8yZTx29hRmOPGd05k0O6z0meyB1jDcttJRIPFvsh/ACFS/SU8IWwG1leRi0Z0+YiDOTLOLcVze95agagHyhxynG1+pVpzrBIrTkuqBMatHAoGBAJ/my2jLW3K77ehx8mqRLt0zhIAopN6htl84tAXd4U0h4M0f3S7gfasq2XEQFMjw2trowHakrt635AWgeud4kGzUNMvz4DyMp/oqFuA+XUrhUN2UFR/82HYaKkCsRsQgXIYEVdI2YrhTINaydo/TcY2IcxxosLTv9KZr2qGvygRRAoGBAMI4KV3TNqmZ7vjJv7RinMrtrY/7/EyAgkqQ/JVtBL8dHQIudcTF0syc2gwzkBdVKnIXEsTgT77+7D4uSIzTYnFxmSgQbx0l8rHYrQySfLqd6KWhhZ3DOZxELqWheZpCEoxj9JK1djWIC88xOtkJvOP0zsu9MVwWoCsHtDgZocHtAoGBAOzEkT6W1UP+IOdYkVYzWyRI05niKv7tbx8RD2KcJO+xh3YhDx6jiIDuKSisnP/5KuAYgFu8cmSzLhB3IM76zeZbb0Q6zoW64rtv8kcrsTqR+BcWvlxpJYQRtKryCfbs/1SF9Ij203zpn1kc4syP2y7Noja/fgDCtNXsHoZ9w2bz";
//        $str = "MIIEpQIBAAKCAQEAqQnfc8fhm0DExcXRhYVEIWv/W7PXeddT/Ug5E4OlVfrSOXpfkx2ccRZTGssH6ZBOve8m6szNfJbG9SX5Y8xv9PFOZtHE5mKZo+hZ0ZvJZB0iIIpUUrbXIEMs6GEKIzXJ6gzf8jzjnd9sLbd1v4WZ1Hgqp7+7OOic8QMdHujUb8D39AX25tHPWRArGiN2+aGAYlG+ySTSVmFq37sLO+dw3z5QSpsST34QE1m4B3YYxZtnulbvqBQT9orKn/4XWylUNtZ2FecLoQbl+GoLHgbirVritlhh5RNsWwDUdh6n+RkIaRE2cjDOADsXTgoNsyBUMS0FVgeUPy6/noAlGvu1ZQIDAQABAoIBAQCICrITdGJVmKLEqJgfZUmXvFsfanWYUXv+9Ak7c0+1Cs/2Ak65D7PAL79iaNRNXR6CLd6cSYd3JcGpuo4ji3zvHOWESn61C9Wq3GytDTvfnrLAjZOfnhc0l9ukibSm8bs26nWxdt+ZRx7BDE1vfg53uN5bcysyecMVFPRy099ksBEER6fKbVvs8j96pATTyXx9xSKejAIm/efGiVmFvgDJ/GYI2jlbUi/WZAq4RhZlfHtwZxaDofx3p4AV8RC8ac6H+vH57ck14o34AGy8n9TcHZWW0msg+nuvasFEV+hTkE+MNq+J7AiLELvri/EEAyQjPS2MHNUTx7h3HRRmKtlhAoGBAODVyjioTs6nG+chTuPYlaRm9rehPOgi8ZYBo85wcIkUTh65m0Qqq8UdNaYLyvHiiwErZclvBbrq8o33C9i9yiTuzwPBY3ErW01ZffsQgOnMAGLqRMfWO8R+KzpOTrDucLz5N+5p0pyFrmuz4AeNH+AmfdGQizPQzxPEjVHsvjjzAoGBAMB4KIDEhk+piYDBK91y5/raxSVY1lJnT+uFq+nmLmFABYK7hAva6Z0e+GHVYRKP4NSdc6i20imMpKncFBwlkmLMgbW2tsPLdtj8qcwdxUGgdVJidiP2WlTqHxot5x+P9FiAFn8g0BsUJqvabUrqOUJtEiKGaniAj66nGClTmO5HAoGBANwIPdxO0rkpD4SVs0gGKQR4g5DYKewgbn9YYX/hyDBO/9jP2iTPAcVpPZyfw+AOzey6FOYaUehIkFHg6OYXbuHQGCZbtz8EqFi9cS4oS5ECxYoILIHJpjo5I4LvKdG7t/coitU3GRd3OGhM471gHkbWRKG3rJNsEYwN+iOn5c2DAoGAQzMw4RS82F+XVM8dY6pU+K8dmlaULh1IODFpvrbRhr71rZmPFxyN6IABtRbtV6/IjjOTSFrcsgkbDPt8Qf+URs6kky+CTIZjros5Nb1HPAysKeGa4h+HlpJP3bIdOlXmuGewcx6fynSUs/gFpNx2tYyjgk5wKjao9A//TZ6UjM0CgYEAz5/5eGNn2qG0GELekNx4BML3lkmNDGTrmrIJl37/uKsIaZaYLk0sEPFa7f+9X+UFMC5JLWdj2nps9wYRBaxaAUZRrDVNB4r13eD2Vj1kpqENHLC8m2hauchiQlak+4YxZbdIL+nq6jjuRNWLv8bVlp1oepHBzXc9HPL2AXgSKdc=";
//        $publicstr = "MIIEpgIBAAKCAQEAxoHJ8qkfqJTC0jU8wDd0dtpaGAP+lDPhq6hLb0YWXKs/MPhY8f3QgT0xkxMXXq+S+V8UF69DIvFRliEIJxiK5AEdv9CLTrKGsMs/m0OLYQbKmaQ1CbQIbC8OzVWe5Ogqf2UcGSirK+B0XCIzMIkeNnIwjRa8BGU/oGsfVX6TUFJSVXYbb4WjWq6BX5V8i5BYsnpKEVOpz185aSHnQKRahP1LABinwdVtlm9r2tJZdSaysrFnM0fvMXG662hqPNDti0XKJ4DyivjpeG2ntQj4cadCMI042PtBn5zhjKw/Dz7dzNBJEjOllIieLzFauVggi2PbfphF5pfNOdJmlUlF3wIDAQABAoIBAQCWFuwqndViEZh8d+RHMabNdqtIHT0PsWqf78+ZgtTFnHVbAWu/DLnp/erN2VGCNh5ajpkSj2GjufKGINOZO+VIH8/uiC/MgQ91/TdKnfd0lT36jT3M6GK16Z63glVxnlkDmd905lsGAd/5CaLKv+IoKI6tHtNqP2OsV1KJwfqAb2f/mmooNHTcbDW3VIUAi86HEHh7NLpgP45iMgjgdmtByoXkbYgXlxeydZMJTt5MM8uRIiJ3rVIK6623Z06s5MO8RX87xkHpIKwcP69HV1z1Denv1dqT/EsTcUjav1Pj2SviqYiycSaTEFBD3KV/sWqcKtmwDFNy3BV9PqSK/scBAoGBAPPVUsH2FvsZmuJS3EyxqAAcB5k+ohW+rLilqJ76t7GVUWt+4ESa/ghFUFpnzDVC3epQ3LjHUJCFoiJQCQGIm0iExNpAYR4uXmp9yM8IYlBnqoFVfg15N9qoABCGzhMgj/Kpl/FM7aJfz9Wb8AzUlcbmkcYBiBGiooIqTDSG+SypAoGBANBpegKVoG8hk9EfrrU9QvJngH9U5gn/Cgwz+QS5+88VQIo0VAORzvfTKR/WiZ18LA8yZTx29hRmOPGd05k0O6z0meyB1jDcttJRIPFvsh/ACFS/SU8IWwG1leRi0Z0+YiDOTLOLcVze95agagHyhxynG1+pVpzrBIrTkuqBMatHAoGBAJ/my2jLW3K77ehx8mqRLt0zhIAopN6htl84tAXd4U0h4M0f3S7gfasq2XEQFMjw2trowHakrt635AWgeud4kGzUNMvz4DyMp/oqFuA+XUrhUN2UFR/82HYaKkCsRsQgXIYEVdI2YrhTINaydo/TcY2IcxxosLTv9KZr2qGvygRRAoGBAMI4KV3TNqmZ7vjJv7RinMrtrY/7/EyAgkqQ/JVtBL8dHQIudcTF0syc2gwzkBdVKnIXEsTgT77+7D4uSIzTYnFxmSgQbx0l8rHYrQySfLqd6KWhhZ3DOZxELqWheZpCEoxj9JK1djWIC88xOtkJvOP0zsu9MVwWoCsHtDgZocHtAoGBAOzEkT6W1UP+IOdYkVYzWyRI05niKv7tbx8RD2KcJO+xh3YhDx6jiIDuKSisnP/5KuAYgFu8cmSzLhB3IM76zeZbb0Q6zoW64rtv8kcrsTqR+BcWvlxpJYQRtKryCfbs/1SF9Ij203zpn1kc4syP2y7Noja/fgDCtNXsHoZ9w2bz";
        $this->seller_id = $this->SellerID;
        $str = $this->rsaStr;
//        $this->client->rsaPrivateKeyFilePath = SVN_ROOT."\application\common\saPrivateKeyFilePath.pem";
        $this->client->rsaPrivateKeyFilePath = $str;
//        $this->alipay_public_key = SVN_ROOT."\application\common\alipay_public_key.pem";
        $this->alipay_public_key = $this->Publicstr;
        $this->client->gatewayUrl = $this->GetWayUrl;
        $this->notifyUrl = config('app.alipay_h5_notify_url');;
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
     * 发起订单
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 订单发起时间
     * @return array
     */
    public function payUrlAlipayDBK($orderNo)
    {
        //业务参数
        $where['orderNo'] = $orderNo;
        $resorder = db("order")->where($where)->find();
        $id = $resorder['userId'];
        $total_amount = $resorder['payableMoney'];
        $subject = $resorder['userName'];
        //请求参数
        $requestConfigs = array(
            'out_trade_no'=>$orderNo,
            'product_code'=>'QUICK_WAP_WAY',
            'total_amount'=>$total_amount, //单位 元
            'subject'=>$subject,  //订单标题
        );
        $commonConfigs = array(
            //公共参数
            'app_id' => $this->appId,
            'method' => $this->Method,             //接口名称
            'format' => 'JSON',
            'return_url' => $this->ReturnUrl,
            'charset'=>$this->CharSet,
            'sign_type'=>$this->SignType,
            'timestamp'=>date('Y-m-d H:i:s'),
            'version'=>'1.0',
            'notify_url' => $this->notifyUrl,
            'biz_content'=>json_encode($requestConfigs),
        );
        $commonConfigs["sign"] = $this->generateSign($commonConfigs, $commonConfigs['sign_type']);
        return $commonConfigs;
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
        $where['orderNo'] = $orderNo;
        $resorder = db("order")->where($where)->find();
        $id = $resorder['userId'];
        $total_amount = $resorder['payableMoney'];
//        $subject = $resorder['userName'];
        $subject = '订单编号：'.$orderNo;
        //公共参数
        $result = $this->basePay($id, $subject, $total_amount, $orderNo,$resorder['userId']);
        $this->output_log_file("\r\n".json_encode($result));
        return resultArray(['data' => $result]);
//        echo json_encode($result);
    }

    public function basePay($id, $subject, $total_amount, $ord_no, $userId,$type = 'd', $balance = 0, $points = 0)
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

        $arr['app_id'] = $this->APPID;
        $arr['biz_content'] = $biz_content;
        $arr['charset'] = $this->CharSet;
        $arr['format'] = "json";
        $arr['method'] = $this->Method;
        $arr['notify_url'] = $this->notifyUrl;
        $arr['sign_type'] = $this->SignType;
        $arr['timestamp'] = $this->timestamp;
        $arr['version'] = "1.0";
        $arr['out_trade_no'] = $ord_no;
        $arr['product_code'] = $product_code;
        $arr['subject'] = $subject;
        $arr['timeout_express'] = '30m';
        $arr['total_amount'] = $total_amount;
        unset($arr2['product_code']);
        unset($arr2['timeout_express']);

//        $arr2['user_id'] = $id;
//        $arr2['type'] = $type;
//        $arr2['ref_no'] = $ord_no ? $ord_no : $id;
//        $arr2['balance'] = $balance ? $balance : 0;
//        $arr2['points'] = $points ? $points : 0;
//        $arr['getWayUrl'] = $this->GetWayUrl;
        $arr3['orderNo'] = $ord_no;
        $arr3['payMoney'] = $total_amount;
        $arr3['payType'] = 1;
        $arr3['payStatus'] = 1;
        $arr3['thirdNo'] = 1;
        $arr3['userId'] = $userId;
        $arr3['createTime'] = time();
        db("pay")->data($arr3)->insert();
        $c = new \AopClient();
        $c->gatewayUrl = $this->GetWayUrl;
        $c->appId = $this->APPID;

        $c->rsaPrivateKey = $this->client->rsaPrivateKeyFilePath;
        $c->alipayPublicKey = $this->Publicstr;
        $c->apiVersion = $this->Version;
        $c->signType = $this->SignType;
        $c->postCharset = $this->CharSet;
        $c->format = $this->format;
        $request = new \AlipayTradeWapPayRequest();
        $request->setBizContent($biz_content);
        $request ->setNotifyUrl($this->notifyUrl);
        $sign = $c->generateSign($arr, $this->SignType);
        $arr['return_url'] = $this->ReturnUrl;
        $arr['sign'] = $sign;
        $from=$c->pageExecute($request);
//        $from = $c->getBody();
//        $result['err_code'] = 0;
//        $result['url'] = $this->createLinkstring($arr);
//        $json_noti = urlencode($this->NotifyUrl);
//        $json_return = urlencode($this->ReturnUrl);
//        $html = "<from name=\"punchout_form\" method=\"post\" action=\"https://openapi.alipay.com/gateway.do?";
//        $html .= "sign=\"$sign\"&timestamp={$arr['timestamp']}&sign_type={$this->SignType}&notify_url={$json_noti}&charset={$this->CharSet}";
//        $html .= "&app_id={$this->APPID}&method={$this->Method}&return_url={$json_return}&version=1.0&format=json>";
//        $html .= "<input type=\"hidden\" name=\"biz_content\" value=\"{&quot;out_trade_no&quot;:&quot;$ord_no&quot;,&quot;product_code&quot;:&quot;QUICK_WAP_PAY&quot;,&quot;seller_id&quot;:&quot;$this->SellerID&quot;,&quot;subject&quot;:&quot;$subject&quot;,&quot;total_amount&quot;:$total_amount}\" /> <input type=\"submit\" value=\"立即支付\" style=\"display:none;\" /> </form>";
//        $html .= "<script>document.forms[0].submit();</script>";
        return $from;
//        return $arr;
    }

    protected function createBiz_content($para)
    {
        $arg = "{";
//        $arg = "";
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

        $this->output_log_file("H5支付宝调用一步验证");
//        $this->output_log('123','支付宝调用一步验证');
        $param = $_REQUEST;
        $this->output_log_file("\r\n".json_encode($param));
        $aop = new \AopClient();
        //支付宝公钥
        $whereStatus['orderNo'] = $param['out_trade_no'];
        $checkOrder = db("pay")->where($whereStatus)->find();
        if ($checkOrder['payStatus'] == 3) {
            $this->output_result_alipay('0', '验证成功，已返回支付宝服务器！', true);
        }else {
            $this->output_log_file("支付宝调用二步验证");
            $aop->alipayrsaPublicKey = $this->alipay_public_key;
            $this->output_log_file("支付宝调用三步验证");
            $result = $aop->rsaCheckV1($param, $this->Publicstr, $this->SignType);
            $this->output_log_file("支付宝调用四步验证");
            $this->output_log_file($result);
            if ($result) {
                $this->output_log_file("支付宝调用⑤步验证");
                //插入trade_status验证
                $this->output_log_file($param['trade_status']);
                if ($param['trade_status'] != 'TRADE_SUCCESS')
                    $this->output_result_alipay('1', '支付状态出现问题。');

                $this->output_log_file("支付宝调用6步验证");
                //插入app_id验证
                if ($param['app_id'] != $this->APPID)
                    $this->output_result_alipay('4', 'app_id验证出现问题。');

                $this->output_log_file("支付宝调用7步验证");
                //插入sell_id验证
                $this->output_log_file($param['seller_id']);
                if ($param['seller_id'] != $this->SellerID)
                    $this->output_result_alipay('2', 'seller_id验证出现问题。');

                $this->output_log_file("支付宝调用8步验证");
//            项目逻辑处理开始
//            支付状态修改
                $this->output_log_file("order调用成功");
                $arrStatus['payStatus'] = 3;
                $arrStatus['thirdOrderNo'] = $param['trade_no'];
                $arrStatus['updateTime'] = time();
//                $whereStatus['orderNo'] = $param['out_trade_no'];
                $orderid = db("order")->where($whereStatus)->find();
                //插入total_amount验证
                if (!$orderid)
                    $this->output_result_alipay('5', '查无此单。');

//            $total_amount = $orderid['payableMoney'];
//
//            if ($param['total_amount'] != $total_amount)
//                $this->output_result_alipay('6', '金额验证出现问题。');

                $this->output_log_file(json_encode($orderid));
                $res = Order::payOrder(['orderNo' => $param['out_trade_no'], 'payMoney' => $param['total_amount'], 'payType' => 1]);
                $this->output_log_file(json_encode($res));
                db("pay")->where($whereStatus)->update($arrStatus);
                /**
                 * 支付记录新增
                 */
                $this->output_log_file("\r\n" . "H5支付宝调用记录新增开始");
                db("log_alipay")->data($param)->insert();
                $this->output_log_file("\r\n" . "支付宝调用记录新增结束");
//            项目逻辑处理结束
                $this->output_result_alipay('0', '验证成功，已返回支付宝服务器！', true);
            } else {
                $arrStatus['payStatus'] = 2;
                $arrStatus['thirdOrderNo'] = $param['trade_no'];
                $arrStatus['updateTime'] = $this->timestamp;
                $whereStatus['orderNo'] = $param['out_trade_no'];
                db("pay")->where($whereStatus)->update($arrStatus);
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

        $params = $this->input->request();

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

        } else
            $this->output_error(2002);


        //插入app_id验证
        if ($param['app_id'] != $this->appId)
            $this->output_result_alipay('4', 'app_id验证出现问题。');

        //插入sell_id验证
        if ($param['seller_id'] != $this->seller_id)
            $this->output_result_alipay('2', 'seller_id验证出现问题。');
        $order = new Order();
        $this->output_log_file("order调用成功");
//            $arrStatus['orderStatus'] = 2;
        $whereStatus['orderNo'] = $param['out_trade_no'];
        $orderid = db("order")->where($whereStatus)->find();
        //插入total_amount验证
        if (!$orderid)
            $this->output_result_alipay('5', '查无此单。');

        $total_amount = $orderid['payableMoney'];

        if ($param['total_amount'] != $total_amount)
            $this->output_result_alipay('6', '金额验证出现问题。');

        return [
            'message' => '支付成功'
        ];

    }

    protected function output_result_alipay($error_num, $errinfo = '', $result = false)
    {
        if ($errinfo) $str = json_encode(array('err_code' => $error_num . '', 'err_info' => $errinfo));
        else $str = json_encode(array('errcode' => $error_num . ''));

//        $str = preg_replace("/\\\u([0-9a-f]{4})/ie", "mb_convert_encoding(pack('V', hexdec('U$1')),'UTF-8','UCS-4LE')", $str);

//        $this->output_log_file($str);

        if ($result)
            echo 'success';
        else
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
    public function output_log_file( $str)
    {
        $date = date('Y-m-d');
        if (PHP_OS == 'Linux') {
            $path =  "/var/log";
//        var_dump($path);
            $filename = $path . '/' . "h5.log";
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
}