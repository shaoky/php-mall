<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/23
 * Time: 16:21
 */
namespace app\admin\controller\log;
use app\admin\controller\ApiCommon;
use think\Request;

class Index extends ApiCommon {
    public function getDir(Request $request){
        $model = model('Log');
        $result = $model->getDir($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function getLog(Request $request){
        $model = model('Log');
        $result = $model->getLog($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
}