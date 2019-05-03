<?php  
namespace app\comm\model;  
use think\Db;
use think\Model;

use OSS\OssClient;
use OSS\Core\OssException;
/**
 * @apiDefine commonGroup 通用接口
 */

/**
 * @api {post} /common/region/all 2.1 获取地区
 * @apiName regionAll
 * @apiGroup commonGroup
 * @apiParam {Number} [id] 地区编号
 * @apiVersion 1.0.0
 */
class Image extends Model {
    public function add($params) {
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
        $accessKeyId = "LTAIyMyg1MkPjUez";
        $accessKeySecret = "z4p9S0FEsliCRJeYcyVVzIPCdik7wF";
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = "http://oss-cn-hangzhou.aliyuncs.com";
        // 存储空间名称
        $bucket= "shaoky-images";
        // 文件名称
        $params['path'] = str_replace('\\', '/', $params['path']);
        $object = $params['path']; 
        $url = 'upload/'.$params['path'];

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $data = $ossClient->uploadFile($bucket, $object, $url);
            return [
                'url' => $data['info']['url']
            ];
        } catch (OssException $e) {
            // return $e->getMessage();
            print $e->getMessage();
        }
    }
}