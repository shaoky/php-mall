<?php
namespace app\app\model;
use think\Db;
use think\Model;
use app\h5\model\common;
use app\app\model\Statistics;
use app\comm\model\Sms;

/**
 * @apiDefine appBuyerGroup app-我是买手
 */

class Buyer extends common {

    /**
     * @api {post} /app/buyer/index 1 我是买手
     * @apiName appBuyerIndex
     * @apiSuccess {String} userInfo 用户信息查看用户表
     * @apiSuccess {String} todayData 今日数据
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */


    public function getIndexData()
    {


        try {
            $data['userInfo'] = $this->getUserInfo();
            $shop = Db::name('shop')->where('userId',$data['userInfo']['userId'])->find();
            if($shop){
                $isShop = 1;
            }else{
                $isShop = 0;
            }
            $data['userInfo']['isShop'] = $isShop;
            $data['todayData'] = Statistics::getTodayData($data['userInfo']['userId']);
            if (!$data['todayData']){
                $data['todayData']['orderMoney'] = 0;
                $data['todayData']['number'] = 0;
                $data['todayData']['commissionMoney'] = 0;
            }
            return $data;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /app/buyer/invitation 1 邀请店主
     * @apiName appBuyerStatisticstInvitation
     * @apiSuccess {String} title 标题
     * @apiSuccess {String} content 内容
     * @apiSuccess {String} icon 图标
     * @apiSuccess {String} url 链接
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function invitation()
    {
       $user = $this->getUserInfo();
       $data['isOpen'] = 1;
       $data['info']=['title'=>'邀你开启尊尚生活之旅','content'=>'送你一封邀请函','icon'=>config('app.app_host').'/images/common/logo.jpg','url'=>config('app.h5_host').'/sp/activity/invitation/index?userNo='.$user['userNo']];
       return $data;
    }

    /**
     * @api {post} /app/buyer/share 1.1 分享店铺
     * @apiName appBuyerStatisticstShare
     * @apiSuccess {Number} isOpen 是否可以分享，0不可以，1可以
     * @apiSuccess {Object} info
     * @apiSuccess {String} .title 标题
     * @apiSuccess {String} .content 内容
     * @apiSuccess {String} .icon 图标
     * @apiSuccess {String} .url 链接
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function share()
    {
        $user = $this->getUserInfo();
        $data['isOpen'] = 1;
        $data['info']=['title'=> $user['userName'].'的店铺，低价好货推介给你','content'=>'如果让我推荐一款最适合你的产品','icon'=>config('app.app_host').'/images/common/logo.jpg','url'=>config('app.h5_host').'/index?userNo='.$user['userNo']];
        return $data;
    }
    /**
     * @api {post} /app/buyer/statistics/data 2 数据中心
     * @apiName appBuyerStatisticstData
     * @apiSuccess {Object} todayData 今日数据
     * @apiSuccess {String} .orderMoney 今天销售额
     * @apiSuccess {String} .number 今日订单
     * @apiSuccess {String} .commissionMoney 佣金收益
     * @apiSuccess {Object} allData 全部数据
     * @apiSuccess {String} ..orderMoney 累计销售额
     * @apiSuccess {String} ..commissionMoney 累计收益
     * @apiSuccess {String} ..noSettlement 未结算奖金
     * @apiSuccess {String} list 数据列表
     * @apiSuccess {Object} withdrawal 提现
     * @apiSuccess {String} .withdrawalAmount 可提现余额
     * @apiSuccess {String} .withdrawalAmountCount 已提现总金额
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    /**
     * @api {post} /app/buyer/order/list 3 我的客户
     * @apiName appBuyerStatisticstMyClient
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} userNo 客户Id
     * @apiSuccess {Number} count 直属人数
     * @apiSuccess {Number} teamCount 团队人数（包括直属会员）
     * @apiSuccess {Array} list
     * @apiSuccess {String} .userPhone 用户手机
     * @apiSuccess {String} .userName 用户名称
     * @apiSuccess {String} .userPhoto 用户头像
     * @apiSuccess {Number} .createTime 邀请时间
     * @apiSuccess {String} .weixinAccount 微信号
     * @apiSuccess {String} .userExpenseAmount 消费金额
     * @apiSuccess {Number} .teamCount 团队人数
     * @apiSuccess {String} .orderStatusName 订单状态
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */



    /**
     * @api {post} /app/buyer/order/list 4 我的订单
     * @apiName appBuyerOrderList
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} userNo 客户Id
     * @apiParam {Number} orderStatus 订单状态1未付款,6已退款,4已经完成
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */



    /**
     * @api {post} /app/buyer/contact/index 5 联系我们
     * @apiName appBuyerContactIndex
     * @apiGroup appBuyerGroup
     * @apiSuccess {Array} list 客服列表数据
     * @apiSuccess {String} .weixinAccount 微信账号
     * @apiSuccess {String} .userNo 用户编号
     * @apiSuccess {String} .userName 用户昵称
     * @apiSuccess {String} .userPhoto 用户头像 
     * @apiVersion 1.0.0
     */

    public function getContactData()
    {
        try {
            $user = $this->getUserInfo();
            $data['list'][0] = Db::table('tp_user')->where('userNO',$user['superiorNo'])->field('weixinAccount,userNo,userName,userPhoto')->find();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /app/buyer/contact/update 5.1 联系我们提交微信
     * @apiName appBuyerContactUpdate
     * @apiParam {String} weixinAccount 微信号
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function updateContact($request)
    {
        $userId = $this->getUserId();
        if (!$request->has('weixinAccount')) {
            $this->error = '微信号没有值';
            return false;
        }
        try {
            $data = Db::table('tp_user')->where('userId', $userId)->update(['weixinAccount' => $request->post('weixinAccount')]);
            if ($data == 1) {
                return [
                    'message' => '更新成功'
                ];
            } else {
                $this->error = '更新失败';
                return;
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /app/buyer/cash/index 6 提现
     * @apiName appBuyerCashIndex
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function getCashData() {
        $userId = $this->getUserId();
        $data['info'] = Db::table('tp_user')->where('userId',$userId)->field('withdrawalAmount,userCashBackAmount')->find();
        $data['text'] = '提现将于3-7个工作日转入您的提现账号里';
        $data['info']['CashWithdrawed'] = Db::table('tp_user_withdrawal')->where(['userId'=>$userId,'status'=>2])->sum('withdrawalPrice');
        return $data;
    }

    /**
     * @api {post} /app/buyer/cash/list 6.1 提现记录
     * @apiName appBuyerCashList
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function getCashList($request)
    {
        $userId = $this->getUserId();
        $data['list'] = Db::table('tp_user_withdrawal')
            ->where('userId',$userId)
            ->page($request->post('page',1), $request->post('size',20))
            ->order('withdrawalId', 'desc')
            ->select();
        foreach($data['list'] as $key => $item) {
            $data['list'][$key]['statusName'] = getStatusName('UserWithdrawalPositionStatus', $item['status']);
            $data['list'][$key]['withdrawalTypeName'] = getStatusName('withdrawalTypeName', $item['withdrawalType']);
        }
        return $data;
    }

    /**
     * @api {post} /app/buyer/cash/info 6.2 提现记录详细
     * @apiName appBuyerCashInfo
     * @apiParam {Number} withdrawalId 提现Id
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function getCashInfo($request)
    {
        if ($request->has('withdrawalId')){
        $data['info'] = Db::table('tp_user_withdrawal')->where('withdrawalId',$request->withdrawalId)->find();
        $data['info']['statusName'] = getStatusName('UserWithdrawalPositionStatus', $data['info']['status']);
        $data['info']['withdrawalTypeName'] = getStatusName('withdrawalTypeName', $data['info']['withdrawalType']);
        $data['info']['imageUrl'] = [$data['info']['imageUrl']];
     return $data;
        }else{
            $this->error = '提现id不存在';
            return false;
        }
    }

    /**
     * @api {post} /app/buyer/cash/add 6.3 添加提现
     * @apiName appBuyerCashAdd
     * @apiParam {Number} withdrawalType 提现方式1微信,2支付宝,3银行卡
     * @apiParam {Number} withdrawalAccount 账号
     * @apiParam {String} withdrawalName 提现人姓名
     * @apiParam {String} withdrawalBank 提现银行卡
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function AddCash($request)
    {


        if ($request->has('withdrawalType') && $request->has('withdrawalAccount'))
        {
            $user = $this->getUserInfo();
            if ($user['withdrawalAmount']<=0){
                $this->error = '没有可以提现余额';
                return false;
            }
            if (!$request->has('withdrawalName')) {
                $request->withdrawalName = '';
            }
            if (!$request->has('withdrawalBank')) {
                $request->withdrawalBank = '';
            }
            $data = [
                'userId' => $user['userId'],
                'userNo' => $user['userNo'],
                'userName' => $user['userName'],
                'withdrawalPrice' => $user['withdrawalAmount'],
                'withdrawalType' => $request->withdrawalType,
                'withdrawalAccount' => $request->withdrawalAccount,
                'withdrawalName' => $request->withdrawalName,
                'withdrawalBank' => $request->withdrawalBank,
                'createTime' => time(),
                'status' => 1,
                'imageUrl' => ''
            ];

            Db::startTrans();
            try {
                $data = Db::table('tp_user_withdrawal')->insert($data);
                if ($request->withdrawalType == 3) {
                    $bank = Db::name('user_bank')->where('userId', $user['userId'])->find();
                    if ($bank) {
                        Db::name('user_bank')->where('userId', $user['userId'])->update([
                            'userId' => $user['userId'],
                            'bankName' => $request->withdrawalBank,
                            'userName' => $request->withdrawalName,
                            'bankAccount' => $request->withdrawalAccount,
                            'updateTime' => time()
                        ]);
                    } else {
                        Db::name('user_bank')->insert([
                            'userId' => $user['userId'],
                            'bankName' => $request->withdrawalBank,
                            'userName' => $request->withdrawalName,
                            'bankAccount' => $request->withdrawalAccount,
                            'createTime' => time()
                        ]);
                    }
                }
                if ($data) { 
                    Db::table('tp_user')->where('userId',$user['userId'])->update(['withdrawalAmount' => '0']);
                    $Sms = new Sms();
                    $smsParams = [
                        'name' => $user['userName'],
                        'money' => $user['withdrawalAmount']
                    ];
                    // $response = $Sms->sendSms($beneficiaryUser['userPhone'], $smsParams, 'SMS_147970282', 2);
                    $response = $Sms->sendSms('15067718999', $smsParams, 'SMS_153331444', 2);
                }
                
                Db::commit();
                if ($data) {
                    return '申请成功';
                }
                else {
                    $this->error = '申请失败';
                    return false;
                }
               
            } catch (\Exception $e) {
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        } else {
            $this->error = '必填项未填';
        }

    }

    /**
     * @api {post} /app/buyer/cash/bankInfo 6.4 获取提现的银行卡信息
     * @apiName bankInfo
     * @apiParam {String} bankName 银行名字
     * @apiParam {String} bankAccount 银行账号
     * @apiParam {String} userName 提现人姓名
     * @apiGroup appBuyerGroup
     * @apiVersion 1.0.0
     */

    public function bankInfo($params)
    {
        $user = $this->getUserInfo();
        Db::startTrans();
        try {
            $data['info'] = Db::name('user_bank')->where('userId', $user['userId'])->find();
            Db::commit();
            return $data;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }


}
