<?php 
// 用户
namespace app\admin\controller\setting;
use app\admin\controller\ApiCommon;
use think\Request;

class Index extends ApiCommon {
    public function site(Request $request)
    {


        $WebConfigModel = model('WebConfig');

        $data = $WebConfigModel->getsite($request);

        if (!$data) {
            return resultArray(['error' => $WebConfigModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function siteUpdate(Request $request)
    {
        $validate = $this->validate($request->param(), 'app\admin\validate\WebConfig.php');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }


        $WebConfigModel = model('WebConfig');
        $data = $WebConfigModel->siteUpdate($request);
        if (!$data) {
            return resultArray(['error' => $WebConfigModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}