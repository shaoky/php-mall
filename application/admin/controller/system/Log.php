<?php 
// 用户
namespace app\admin\controller\system;
use app\admin\controller\ApiCommon;

class Log extends ApiCommon {
  
    public function loginList() {
        $model = model('SystemLog');
        $params =  input('post.');
        $data = $model->getAdminLoginList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function operationList() {
        $model = model('SystemLog');
        $params =  input('post.');
        $data = $model->getAdminOperationList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

}