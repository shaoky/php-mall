<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 14:54
 */
namespace app\admin\controller\group;
use app\admin\controller\ApiCommon;

class Index extends ApiCommon
{
    public function getGroupList(){
        $params = input('post.');
        $model = model('group');
        $result = $model->getGroupList($params);
        if (!$result){
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function addGroup(){
        $params = input('post.');
        $model = model('group');
        $result = $model->addGroup($params);
        if (!$result){
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function updateGroup(){
        $params = input('post.');
        $model = model('group');
        $result = $model->updateGroup($params);
        if (!$result){
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }public function deleteGroup(){
        $params = input('post.');
        $model = model('group');
        $result = $model->deleteGroup($params);
        if (!$result){
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
}