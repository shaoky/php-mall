<?php
// 用户
namespace app\h5\model;
use think\Model;
use think\Db;
use think\facade\Request;
use app\admin\model\WebConfig;
use app\comm\model\Common as Base;

class Common extends Base {

    public function getViewFrom() {
        $header = Request::instance()->header();
        if (empty($header['from'])) {
            exit(json_encode([
                'code' => 500,
                'error' => '缺少请求头参数:from'
            ], JSON_UNESCAPED_UNICODE));
        }
        $from = $header['from'];
        return $from;
    }

    public function getHeaderParams() {
        $header = Request::instance()->header();
        if (empty($header['from'])) {
            exit(json_encode([
                'code' => 500,
                'error' => '缺少请求头参数:from'
            ], JSON_UNESCAPED_UNICODE));
        }
        if (empty($header['app'])) { 
            $header['app'] = 1;
        }
        return $header;
    }

    public function getUserId() {
        $header = Request::instance()->header();
        $authorization = $header['authorization'];
        $user = db('user_token')->where([
            ['token', '=' ,$authorization],
            ['isUse', '=', 1]
        ])->find();
        return $user['userId'];
    }

    public function getUserInfo() {
        $header = Request::instance()->header();
        if (empty($header['authorization'])) {
            return false;
        }
        $authorization = $header['authorization'];
        $user = db('user_token')
            ->alias('a')
            ->join('user b', 'a.userId = b.userId')
            ->where([
                ['a.token', '=', $authorization],
                ['a.isUse', '=', 1],
            ])
            ->field('b.userId, b.userNo, b.superiorId, b.superiorNo, b.loginName, b.userName, b.userPhone, b.lastTime, b.userType, b.userPhoto, b.weixinAccount, b.superiorName, b.openid')->find();
        if ($user) {
            
        } else {
            header("Access-Control-Allow-Origin: *");
            // if (empty($header['authorization'])) {
                exit(json_encode([
                    'code' => 401,
                    'error' => '请登录账号'
                ], JSON_UNESCAPED_UNICODE));
            // }
        }
        
        return $user;
    }
    
    public function getUserLevel($userType) {
        // $user1 = db('user')->where('userId', $userId)->select();
        // return $user1;
        $userLevel = Db::name('user_level')->where('userType', $userType)->find();
        return $userLevel;
    }

    // 设置订单确认后收益
    public function setOrderConfirm($orderId) {
        $webInfo = WebConfig::info();
        if (isset($header['authorization'])){
            $user = $this->getUserInfo();
            $where = [
                'userId' => $user['userId'],
                'orderId' => $orderId
            ];
        }
        else{
            $where = [
                'orderId' => $orderId
            ];
        }

        Db::startTrans();
        try {
            $order = Db::name('order')->where($where)->find();
            if ($order['orderStatus'] == 3) {
                $orderUpdate = [
                    'orderStatus' => 4,
                    'receiveTime' => time(),
                    'refundAppleTime' => strtotime(date("Y-m-d H:i:s", strtotime('+'.$webInfo['refundAppleCycle'] .'day')))
                ];
                $data = Db::name('order')->where($where)->update($orderUpdate);
                if (!$data) {
                    $this->error = '操作失败';
                    return;
                }
            } else {
                $this->error = '订单只有在待收货的时候，才能确认订单';
                return;
            }
            $commissionList = Db::name('commission')->where('orderId', $orderId)->select();
            foreach($commissionList as $item) {
                if ($item['isSettlement'] == 0) {
                    $beneficiaryUser = Db::name('user')->where('userId', $item['beneficiaryUserId'])->find();
                    $update = [
                    //     'userExpenseAmount' => $beneficiaryUser['userExpenseAmount'] + $item['orderMoney'], // 总消费金额，一直累加
                    //     'userCashBackAmount' => $beneficiaryUser['userCashBackAmount'] + $item['commissionMoney'], // 总返现金额，一直累加
                        'noWithdrawalAmount' => $beneficiaryUser['noWithdrawalAmount'] + $item['commissionMoney'], // 用户确认收货，资金设置xx天不能提现
                        'freezeAmount' => $beneficiaryUser['freezeAmount'] - $item['commissionMoney'] // 用户确认收货，减去冻结金额，放在不可提现金额里
                    ];
                    Db::name('user')->where('userId', $item['beneficiaryUserId'])->update($update);
                    $commissionData = Db::name('commission')
                        ->where(['beneficiaryUserId' => $item['beneficiaryUserId'], 'orderId' => $order['orderId']])
                        ->update(['commissionStatus' => 2, 'receiveTime' => time()]);
                } else {
                    $this->error = '该订单已经结算佣金了';
                    return;
                }
            }

            Db::commit();
            return '操作成功';
            // }
        } catch (\Exception $e) {
            echo $e->getMessage();
            Db::rollback();
            $this->error = $e->getMessage();
            return 1;
        }
    }
}
