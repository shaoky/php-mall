<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/24
 * Time: 11:43
 */
namespace app\admin\controller\auth;
use app\admin\controller\ApiCommon;
use think\Controller;
use think\Request;

class Index extends ApiCommon
{
    public function getList(Request $request){
        $model = model('auth');
        $result = $model->getList($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }

    public function addColumn(Request $request){
        $model = model('auth');
        $result = $model->addColumn($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function deleteColumn(Request $request){
        $model = model('auth');
        $result = $model->deleteColumn($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function updateColumn(Request $request){
        $model = model('auth');
        $result = $model->updateColumn($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function addAuth(Request $request){
        $model = model('auth');
        $result = $model->addAuth($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function addColumnUserAuth(){
        $params = input('post.');
        $model = model('auth');
        $result = $model->addColumnUserAuth($params);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function addUserAuth(){
        $request = input('post.');
        $model = model('auth');
        $result = $model->addUserAuth($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function getUserColumnList(){
        $params = input('post.');
        $model = model('auth');
        $result = $model->getUserColumnList($params);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function getUserColumn(){
//        $request = input('post.');
//        $this->initialize();
        $model = model('auth');
        $result = $model->getUserColumn();
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
}