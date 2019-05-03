<?php 
// 用户
namespace app\admin\controller\userwithdrawal;
use app\admin\controller\ApiCommon;
use think\Request;

class Index extends ApiCommon {



    public function list(Request $request) {


        $UserWithdrawalModel = model('UserWithdrawal');

        $data = $UserWithdrawalModel->getUserWithdrawalList($request);

        if (!$data) {
            return resultArray(['error' => $UserWithdrawalModel->getError()]);
        }

        return resultArray(['data' => $data]);

    }

    public function info(Request $request)
    {
        $UserWithdrawalModel = model('UserWithdrawal');
        $data = $UserWithdrawalModel->getUserWithdrawalInfo($request);
        if (!$data) {
            return resultArray(['error' => $UserWithdrawalModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update(Request $request)
    {
        $validate = $this->validate($request->param(), 'app\admin\validate\UserWithdrawal');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $UserWithdrawalModel = model('UserWithdrawal');
        $data = $UserWithdrawalModel->updateUserWithdrawal($request);
        if (!$data) {
            return resultArray(['error' =>  $UserWithdrawalModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }


    public function userList(Request $request)
    {
        $UserWithdrawalModel = model('UserWithdrawal');

        $data = $UserWithdrawalModel->getuserList($request);

        if (!$data) {
            return resultArray(['error' => $UserWithdrawalModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }


}