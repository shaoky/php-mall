<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/27
 * Time: 13:57
 */

namespace app\comm\model;


use think\Model;
require_once '../public/example/phpqrcode/phpqrcode.php';
class Qrcode extends Model
{
    /**
     * 功能：生成二维码
     * @param string $qrLevel 默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
     * @param string $qrSize 二维码图大小，1－10可选，数字越大图片尺寸越大
     * @param string $savePath 图片存储路径
     * @param string $savePrefix 图片名称前缀
     */
    public function makeQrcode($shopId,$logo,$savePath, $qrLevel = 'L', $qrSize = 8, $savePrefix = 'qrcode') {
        $qr = new \QRcode();
        $value = $shopId;
        if (!isset($savePath)) return '';
        //设置生成png图片的路径
        $PNG_TEMP_DIR = $savePath;

        //检测并创建生成文件夹
        if (!file_exists($PNG_TEMP_DIR)) {
            mkdir($PNG_TEMP_DIR);
        }
//        $errorCorrectionLevel = 'L';//容错级别
//        $matrixPointSize = 6;//生成图片大小
//        $logo = "/qrcode/".microtime().".jpg";
//        $oldlogo = $_SERVER['DOCUMENT_ROOT']."/images/qrcode.jpg";
        if (isset($qrLevel) && in_array($qrLevel, ['L', 'M', 'Q', 'H'])) {
            $errorCorrectionLevel = $qrLevel;
        }

        if (isset($qrSize)) {
            $matrixPointSize = min(max((int)$qrSize, 1), 10);
        }
//        $filename = $PNG_TEMP_DIR . $savePrefix . md5($errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        $filename1 = "\\qrcode\\".time().".jpg";
        $filename = $_SERVER['DOCUMENT_ROOT'].$filename1;
        //生成二维码图片
        $qr::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        $QR = $filename;//已经生成的原始二维码图
        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $logo_width, $logo_height);
            $qr::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

        }
//        var_dump($filename);
//        var_dump($QR);
        imagepng($QR, $filename);
//        var_dump()
        $array['name'] = 'image';
        $array['file'] = new \CURLFile($filename1);
        $image = $this->UploadImage($filename);
        if($image){
            unlink($filename);
        }
        return $image;

    }

    //图片上传
    public function UploadImage($file,$header = array()){
        header('content-type:multipart/form-data;charset=utf8');
        $data['file'] = new \CurlFile($file);
        $url = 'http://api.mall.shaoky.com/commom/image/add';
        $header = array(
            'Content-type'=>'form-data'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $result = curl_exec($ch);
//        var_dump( curl_error($ch) );
        curl_close($ch);
        return $result;
    }
}