<?php
namespace app\admin\model;
use think\Db;
use app\admin\model\Common;
use app\admin\model\WebConfig;
use app\comm\model\Sms;
use Naixiaoxin\ThinkWechat\Facade;
use jiguang\Jgsdk;
use app\h5\model\Common as h5Common;


/**
 * @apiDefine adminOrderGroup admin-订单
 */

class Crontab extends Common {

    public function orderSettlement()
    {
        $webInfo = WebConfig::info();
        // $time = time() - $webInfo['orderSettlementCycle'] * 24 * 60 * 60;//计算结算时间
        $time = time() - 20;
        /**
         * 获取未结算订单以beneficiaryUserId进行分组累加计算
         * commissionMoney佣金金额
         * orderMoney 订单金额
         */
        // $data = Db::table('tp_commission')
        //     ->where([['isSettlement','=','0'],['receiveTime','<',$time],['commissionStatus','=',2]])
        //     ->group('beneficiaryUserId')
        //     ->field('SUM(commissionMoney) as commissionMoney,SUM(orderMoney) as orderMoney,userId,commissionId,beneficiaryUserId,orderId')
        //     ->select();
        $data = Db::name('commission')->where([
            ['isSettlement', '=', 0],
            ['receiveTime','<', $time],
            ['commissionStatus', '=', 2]
        ])->select();
        
        
        Db::startTrans();
        try {
            // $commissionIds=[];
            $users = [];//收益人id数组
            $orders = []; // 订单id数组，用于后面，查询订单
            foreach ($data as $item)
            {
                $users[] = $item['beneficiaryUserId'];
                $orders[] = $item['orderId'];
                // array_push($commissionIds,$item['commissionId']);
                // array_push($users,$item['beneficiaryUserId']);
                // array_push($orders,$item['orderId']);

                Db::name('commission')->where('commissionId', $item['commissionId'])->update(['isSettlement' => 1, 'settlementTime' => time()]);
                Db::name('user')->where('userId',$item['beneficiaryUserId'])
                    ->dec('noWithdrawalAmount',$item['commissionMoney'])//不可提现佣金
                    ->inc('withdrawalAmount',$item['commissionMoney'])//可提现佣金
                    ->inc('userExpenseAmount',$item['orderMoney'])//用户总消费
                    ->inc('userCashBackAmount',$item['commissionMoney'])//返现总金额
                    ->update();
            }
            // Db::table('tp_commission')->where('orderId','IN',$orders)->update(['isSettlement' => 1, 'settlementTime' => time()]);
            
            // $benefitList = [] // 受益者的用户，看他们是否符合升级
            $userList = Db::name('user')->where([['userId','IN',$users]])->field('userId, userType, openid, userName, superiorId, userPhone')->select();
            foreach($userList as $key => $item) {
                if ($item['superiorId']) { // 查询上级是否存在
                    $userList1 = Db::name('user')->where([['userId','=',$item['superiorId']]])->field('userId, userType, openid, userName, userPhone, superiorId')->select();
                    foreach($userList1 as $key => $item1) {
                        if (array_search($item1, $userList)) { // 防止查到已经有了的数据
                            $userList[] = $item1;
                        }
                    }
                }
            }
            // 多个下级，同个上级，会出现重复的问题
            // $userMoney = Db::table('tp_user')
            //     ->where([['userId','IN',$users]])
            //     ->select();
            $updateUsers = [];//可以升级的用户
            foreach ($userList as $key => $item){
                $level = $item['userType'] + 1;
                $commandModel = model('app\h5\model\Common');
                $userLevel =  $commandModel->getUserLevel($level);
                if (isset($userLevel)){
                    $isSend = false;
                    // 是否满足升级金额
                    // if ($item['userType'] == 2 && ($item['userExpenseAmount'] >= $userLevel['needMoney'])) {
                    //     array_push($updateUsers, $item['userId']);
                    //     $isSend = true;
                    // }
                    // if ($item['userType'] == 3 && ($item['userExpenseAmount'] >= $userLevel['needMoney'])) {
                    //     array_push($updateUsers, $item['userId']);
                    //     $isSend = true;
                    // }
                    
                    // 是否满足人数要求
                    if ($item['userType'] == 2) { // 黄金升白金
                        // 根据用户查，黄金团队总人数
                        $goldMemberNumber = $this->goldMemberCount($item['userId']);
                        echo $item['userName'].'用户当前直属黄金人数'.$goldMemberNumber['count'].'，黄金团队人数：'.$goldMemberNumber['teamCount'].'，';
                        // $goldMemberNumber = Db::name('user')->where(['superiorId' => $item['userId'], 'userType' => 2])->count();
                        // echo '用户id：'.$item['userId'].'，下级的黄金人数有：'.$goldMemberNumber.'个';
                        if ($goldMemberNumber['count'] >= $userLevel['needGoldPeople'] && $goldMemberNumber['teamCount'] >= $userLevel['needGoldTeamPeople']) {
                            array_push($updateUsers, $item['userId']);
                            $isSend = true;
                        }
                    } else if ($item['userType'] == 3) { // 白金升钻石
                        // $platinumMemberNumber = Db::name('user')->where(['superiorId' => $item['userId'], 'userType' => 3])->count();
                        $goldMemberNumber = $this->goldMemberCount($item['userId']);
                        echo $item['userName'].'用户当前直属黄金人数'.$goldMemberNumber['count'].'，黄金团队人数：'.$goldMemberNumber['teamCount'].'，';
                        // echo $item['userId'].'下级的铂金人数有：'.$platinumMemberNumber.'个<br/>';
                        if ($goldMemberNumber['count'] >= $userLevel['needGoldPeople'] && $goldMemberNumber['teamCount'] >= $userLevel['needGoldTeamPeople']) {
                            array_push($updateUsers, $item['userId']);
                            $isSend = true;
                        }
                    }
                    
                    $userTypeName = getStatusName('userType', $item['userType'] + 1);
                    if ($isSend) {
                        if ($item['openid']) {
                            $app = Facade::officialAccount();
                            $app->template_message->send([
                                'touser' => $item['openid'],
                                'template_id' => '9wnABpWyKGnS3dC5uTX5bDg4Di3p5i4nGSdqQ9NmWFw',
                                'data' => [
                                    'first' => '恭喜您的等级升级了',
                                    'keyword1' => $item['userName'],
                                    'keyword2' => $userTypeName,
                                    'keyword3' => date('Y-m-d H:i:s', time()),
                                    'remark' => '请保持你的努力'
                                ],
                            ]);
                        }
                        $userToken = $this->getTokenArray($item['userId']);
                        // 消息推送
                        $push = new Jgsdk();
                        $m_type = 'https';//推送附加字段的类型
                        $m_txt = '';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
                        $m_time = '86400';//离线保留时间
                        $receive = $userToken;
                        $content = '恭喜您的等级升级了，您目前等级是'.$userTypeName;
                        $message="";//存储推送状态
                        $extras = [
                            'type' => 0
                        ];
                        $result = $push->push($receive,$content,$m_type,$m_txt,$m_time,$extras);
                        
                        
                        // 短信推送
                        // $order = Db::name('order')->where('orderId', $orders[$key])->field('userPhone')->find();\
                        // 2018/11/30，下面注释代码有问题，升级短信发给他上级的了，应该通知他本人。
                        // $superiorUser = Db::name('user')->where('userId', $item['superiorId'])->field('userId')->find();
                        // $beneficiaryUser = Db::name('user')->where('userId', $superiorUser['userId'])->field('userPhone')->find();
                        // $beneficiaryUser = Db::name('user')->where('userId', $item['userId'])->field('userPhone')->find();
                        // dump($beneficiaryUser);
                        $Sms = new Sms();
                        $smsParams = [
                            'name' => $item['userName'],
                            'identity' => $userTypeName
                        ];
                        // $response = $Sms->sendSms($beneficiaryUser['userPhone'], $smsParams, 'SMS_147970282', 2);
                        $response = $Sms->sendSms($item['userPhone'], $smsParams, 'SMS_147970282', 2);
                    }
                }
            }
            Db::table('tp_user')->where('userId','IN',$updateUsers)
                ->inc('userType')
                ->update();
            Db::commit();
            echo '结算订单列表：'. json_encode($orders);
            echo '结算用户列表：'. json_encode($updateUsers);    
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo $e;
            // echo json_encode($e->message());
        }
    }

    public function userLevelUpgrade() {
        
    }

    public function getGoldMemberCount() {
        $data = $this->goldMemberCount1(1);
        echo json_encode($data);
    }
    
    public function goldMemberCount ($userId) {
        $count = 0;
        $teamCount = 0;
        $where[] = ['a.superiorId', '=', $userId];
        $where[] = ['a.userType', '>=', 2];
        $where[] = ['a.userId', '<>', $userId];
        $where[] = ['b.orderStatus', '=', 4];
        $where[] = ['b.refundAppleTime', '<=', time()];
        $where[] = ['b.isMemberGoods', '=', 1];
        $list = Db::name('user')->alias('a')->group('userId')
            ->join('order b', 'a.userId = b.userId')
            ->where($where)->field('a.userId, b.orderStatus, b.refundAppleTime, b.isMemberGoods')->select();
        $count = count($list);
        
        foreach($list as $key => $item) {
            $teamCount += Db::name('user')->alias('a')->group('userId')
                ->join('order b', 'a.userId = b.userId')
                ->where([
                    ['a.userType', '>=', 2], 
                    ['a.superiorId', '=', $item['userId']],
                    ['b.orderStatus', '=', 4],
                    ['b.refundAppleTime', '<=', time()],
                    ['b.isMemberGoods', '=', 1]
                ])->field('a.userId, a.superiorId, b.orderStatus, b.refundAppleTime, b.isMemberGoods')->count();
        }
        return [
            'count' => $count,
            'teamCount' => $teamCount + $count
        ];
    }


    public function goldMemberCount1 ($userId) {
        $count = 0;
        $teamCount = 0;
        $where[] = ['a.superiorId', '=', $userId];
        $where[] = ['a.userType', '>=', 2];
        $where[] = ['a.userId', '<>', $userId];
        $where[] = ['b.orderStatus', '=', 4];
        $where[] = ['b.isMemberGoods', '=', 1];
        $list = Db::name('user')->alias('a')->group('userId')
            ->join('order b', 'a.userId = b.userId')
            ->where($where)->field('a.userId, b.orderStatus, b.refundAppleTime, b.isMemberGoods')->select();
        $count = count($list);
        
        foreach($list as $key => $item) {
            $teamCount += Db::name('user')->alias('a')->group('userId')
                ->join('order b', 'a.userId = b.userId')
                ->where([
                    ['a.userType', '>=', 2], 
                    ['a.superiorId', '=', $item['userId']],
                    ['b.orderStatus', '=', 4],
                    ['b.refundAppleTime', '<=', time()],
                    ['b.isMemberGoods', '=', 1]
                ])->field('a.userId, a.superiorId, b.orderStatus, b.refundAppleTime, b.isMemberGoods')->count();
        }
        return [
            'count' => $count,
            'teamCount' => $teamCount + $count,
            'list' => $list
        ];
    }

    /*public static function orderConfirm()
    {
        $webInfo = WebConfig::info();
        $time = time() - $webInfo['autoConfirmDelivery'] * 24 * 60 * 60;
        $data = Db::table('tp_order')
            ->where([['orderStatus','=',3],['deliveryTime', '<', $time]])
            ->update(['orderStatus'=>4]);

        return $data;
    }*/

    /**
     * 订单自动取消
     */
    public function orderClose(){
        
        Db::startTrans();
        try {
            $time = time();
            // $count = Db::name('order')->where([['orderStatus','=',1],['remainingTime', '<', $time]])->count();
            $orderList = Db::name('order')->where([['orderStatus','=',1],['remainingTime', '<', $time]])->select();
            $data = Db::name('order')->where([['orderStatus','=',1],['remainingTime', '<', $time]])->update(['orderStatus'=>7]);
            $couponIdList = [];
            $orderIdList = [];
            foreach ($orderList as $key => $item) {
                // 如果使用的优惠券，就返回
                $orderIdList[] = $item['orderId'];
                if ($item['couponId']) {
                    $couponIdList[] = $item['couponId'];
                }
            }
            if (count($couponIdList) > 0) {
                Db::name('couuser')->where([['cuId','IN',$couponIdList]])->update(['cuStatus' => 2]);
            }
            
            Db::commit();
            echo '关闭订单列表：'. json_encode($orderIdList);    
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo $e;
            // echo json_encode($e->message());
        }
    }

    /**
     * 订单自动确认收货
     */
    public function orderAutoConfirm() {
        Db::startTrans();
        try {
            $time = time();
            $orderList = Db::name('order')->where([['orderStatus','=',3],['confirmTime', '<', $time]])->select();
            // $common = model('h5/model/common');
            $h5Common = new h5Common();
            foreach($orderList as $key => $item) {
                echo $item['orderId'].'-'. $h5Common->setOrderConfirm($item['orderId']).'，';
            }
            Db::commit();
            echo '执行成功';
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo $e;
            // echo json_encode($e->message());
        }
    }
}
