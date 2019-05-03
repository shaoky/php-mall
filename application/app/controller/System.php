<?php
// 用户
namespace app\app\controller;
use think\Controller;
class System extends Controller {
    public function getVersion() {
        $params =  input('post.');
        $model = model('app\app\model\System');
        $data = $model->getVersion($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

}
