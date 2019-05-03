<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:22
 */

namespace app\app\model;
use think\Db;
use think\Model;
use app\h5\model\Common;
/**
 * @apiDefine appSystemGroup app-系统
 */
class System extends Common
{
    /**
     * @api {post} /app/system/version/info 1. 获取版本号
     * @apiName getVersion
     * @apiGroup appSystemGroup
     * @apiParam {String} versionNo 版本号
     * @apiSuccess {Number} isUpdate 是否更新，0没有，1有
     * @apiSuccess {String} versionNo 当前最新的版本号
     * @apiSuccess {String} AndroidUrl 安卓下载地址
     * @apiSuccess {String} packageSize 安装包大小
     * @apiSuccess {String} versionContent 更新内容
     * @apiSuccess {Number} isMandatory 是否强制更新，0不是，1是
     * @apiVersion 1.0.0
     */
    public function getVersion($params){
        $viewFrom = $this->getViewFrom();
        $headerParams = $this->getHeaderParams();
        $paramsArr = explode('.', $params['versionNo']);
        try {
            $version = Db::name('version')->where('softwareId', $headerParams['app'])->limit(1)->order('versionId', 'desc')->find();
            $versionArr = explode('.', $version['versionNo']);

            $next = false;
            $version['isUpdate'] = 0;

            if ($version['isAndroid'] == 1 && $viewFrom == 2) {
                $next = true;
            }

            if ($version['isIos'] == 1 && $viewFrom == 3) {
                $next = true;
            }
            
            if ($next) {
                if ($paramsArr[0] < $versionArr[0]) {
                    $version['isUpdate'] = 1;
                }
                if ($paramsArr[1] < $versionArr[1]) {
                    $version['isUpdate'] = 1;
                }
                if (isset($paramsArr[2]) && isset($versionArr[2])) {
                    if ($paramsArr[2] < $versionArr[2]) {
                        $version['isUpdate'] = 1;
                    }
                }
                
            }
            
            
            // if ($version['versionNo'] == $params['versionNo']) {
            //     $version['isUpdate'] = 0;
            // } else {
            //     $version['isUpdate'] = 1;
            // }
            return $version;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /app/system/error 2. 获取错误
     * @apiName getAppError
     * @apiGroup appSystemGroup
     * @apiParam {String} versionNo 版本号
     * @apiVersion 1.0.0
     */
    public function getAppError($params) {

    }
}