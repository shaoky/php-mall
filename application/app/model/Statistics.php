<?php
namespace app\app\model;
use think\Db;
use think\Model;
use app\h5\model\common;
use think\helper\Time;



class Statistics extends common {




    public function getDataList($request)
    {


        try {
            $userId = $this->getUserId();
            $data['todayData'] = $this->getTodayData($userId);
            $data['allData'] = $this->getAllData($userId);
            $data['monthData'] = $this->sameMonth($userId);
            $data['list'] = $this->getList($userId);
            $data['withdrawal'] = Db::name('user')->where('userId', $userId)->field('withdrawalAmount, withdrawalAmountCount')->find();
            // output_log_file(json_encode($data));
            return $data;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public static function getTodayData($userId)
    {
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $where = [
          ['beneficiaryUserId','=',$userId],
          ['createTime','>',$beginToday],
          ['createTime','<',$endToday]
        ];


        $data = Db::table('tp_commission')->where($where)
            ->alias('a')
            ->group("DAY(FROM_UNIXTIME(createTime))")
            ->field("SUM(orderMoney) as orderMoney,COUNT(1) as number,createTime,SUM(commissionMoney) as commissionMoney")
            ->find();
            if (!$data) {
                $data['orderMoney'] = 0;
                $data['number'] = 0;
                $data['commissionMoney'] = 0;
            }
        return $data;
    }

    /**
     * 数据中心-销售概览
     */
    public function getAllData($userId){
        $data = Db::table('tp_commission')->where([
                ['beneficiaryUserId', '=', $userId],
                ['commissionStatus', 'in', [1,2]]
            ])
            ->alias('a')
            ->field("SUM(orderMoney) as orderMoney,SUM(commissionMoney) as commissionMoney")
            ->find();
        $data['orderMoney'] ? '' : $data['orderMoney'] = 0;
        $data['commissionMoney'] ? '' : $data['commissionMoney'] = 0;

        $result = Db::table('tp_commission')->where(['beneficiaryUserId' => $userId, 'isSettlement' => 0])
            ->field('SUM(commissionMoney) as commissionMoney')
            ->find();
        $result['commissionMoney'] ? '' : $result['commissionMoney'] = 0;
        $data['noSettlement'] = $result['commissionMoney'];

        return $data;
    }

    /**
     * 当月预估佣金，已结算+未结算
     */
    public function sameMonth($userId) {
        list($start, $end) = Time::month();
        $where[] = ['createTime', '>', $start];
        $where[] = ['createTime', '<', $end];
        $where[] = ['beneficiaryUserId', '=', $userId];
        $where[] = ['commissionStatus', 'IN', [1,2]];
        $result = Db::name('commission')->where($where)->field('SUM(commissionMoney) as commissionMoney')->select();

        if ($result[0]['commissionMoney']) {
            $data['commissionMoney'] = $result[0]['commissionMoney'];
        } else {
            $data['commissionMoney'] = 0;
        }
        return $data;
    }

    public function getList($userId)
    {
        $where = [
            ['beneficiaryUserId','=',$userId]
        ];
        $data = Db::table('tp_commission')->where($where)
            ->alias('a')
            ->group("MONTH(FROM_UNIXTIME(createTime))")
            ->field("SUM(orderMoney) as orderMoney,COUNT(1) as number,createTime,SUM(commissionMoney) as commissionMoney")
            ->order('createTime', 'desc')
            ->select();
        return $data;
    }


    //我的客户
    public function getMyClient($params)
    {  
        $user = $this->getUserInfo();
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        // $where[] = ['userType','>=', 2];
        $where[] = ['superiorNo','=',$user['userNo']];
        $where[] = ['userId', '<>', $user['userId']]; // 特殊情况，没有上级的人，排除自己
        if (!empty($params['userNo'])){
            $where[] = ['userNo','like','%'.$params['userNo'].'%'];
        }
        try {
            // 获取全部，不被size,page影响
            $data['nonmemberCount'] = Db::name('user')->where(['userType' => 1, 'superiorId' => $user['userId']])->count();
            $data['teamCount'] = 0;
            $zhishuList = $data['list'] = Db::name('user')->where($where)->select();
            $data['count'] = Db::name('user')->where([
                ['superiorId', '=', $user['userId']],
                ['userType', 'in', [2,3,4]]
            ])->count();
            foreach($zhishuList as $key => $item) {
                $data['list'][$key]['teamCount'] = Db::name('user')->where([
                    ['superiorId', '=', $item['userId']],
                    ['userType', 'in', [2,3,4]]
                ])->count();
                $data['teamCount'] = $data['teamCount'] + $data['list'][$key]['teamCount'];
                // 统计直属会员的非会员人数
                $data['list'][$key]['nonmemberCount'] = Db::name('user')->where([
                    ['superiorId', '=', $item['userId']],
                    ['userType', '=', 1]
                ])->count();
                $data['nonmemberCount'] = $data['nonmemberCount'] + $data['list'][$key]['nonmemberCount'];
            }

            
            // 根据page，size获取
            $data['list'] = Db::name('user')->where($where)
                ->page($params['page'], $params['size'])
                ->field('userId, userNo, userPhone, userName, userPhoto, createTime, weixinAccount')
                ->order('userId', 'desc')
                ->select();
            foreach($data['list'] as $key => $item) {
                $order = Db::name('order')
                    ->where([['userId','=', $item['userId']],['orderStatus', 'IN', [2,3,4]]])
                    ->field('SUM(payMoney) as payMoneySum')
                    ->find();
                if ($order['payMoneySum']) {
                    $data['list'][$key]['userExpenseAmount'] = $order['payMoneySum'];
                } else {
                    $data['list'][$key]['userExpenseAmount'] = 0;
                }
                // 统计直属会员的团队人数
                $data['list'][$key]['teamCount'] = Db::name('user')->where([
                    ['superiorId', '=', $item['userId']],
                    ['userType', 'in', [2,3,4]]
                ])->count();
                // $data['teamCount'] = $data['teamCount'] + $data['list'][$key]['teamCount'];
                // 统计直属会员的非会员人数
                $data['list'][$key]['nonmemberCount'] = Db::name('user')->where([
                    ['superiorId', '=', $item['userId']],
                    ['userType', '=', 1]
                ])->count();
                // $data['nonmemberCount'] = $data['nonmemberCount'] + $data['list'][$key]['nonmemberCount'];
                // 获取购买会员产品的订单状态
                $orderMember = Db::name('order')->where([
                    // ['isMemberGoods', '=', 1],
                    ['userId', '=', $item['userId']],
                    ['orderStatus', 'IN', [2, 3, 4]]
                ])->limit(1)->select();
                if ($orderMember) {
                    $data['list'][$key]['orderStatusName'] = getStatusName('orderStatus', $orderMember[0]['orderStatus']);
                } else {
                    $data['list'][$key]['orderStatusName'] = '无';
                }
                // 手机号码中间改成****
                $data['list'][$key]['userPhone'] = substr_replace($item['userPhone'], '****', 3, 4);
                
            }
            // $data['count'] = $count;
            // $data['nonmemberCount'] = $data[''];
            $data['teamCount'] = $data['teamCount'] + $data['count'];
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }



    }


    public function getOrderList($request)
    {
        $user = $this->getUserInfo();
        // echo $user['userNo'];
        // $usersId = Db::table('tp_user')->where('superiorNo',$user['userNo'])->column('userId');
        // echo $userId;
        // $where1 = "";
        $where = [];
        $where['a.beneficiaryUserId'] = $user['userId'];
        if ($request->has('orderStatus') && $request->orderStatus != 0){
            $orderStatus = $request->orderStatus;
            if ($orderStatus == 2) {
                $orderStatus = 3;
            } else if ($orderStatus == 3) {
                $orderStatus = 2;
            }
            $where['a.commissionStatus'] = $orderStatus;
                // $where1 .= " (a.orderStatus = $request->orderStatus";
//            $where1=[['a.orderStatus','=',$request->orderStatus]];
//            $where1['a.orderStatus'] = $request->orderStatus;
//            array_push($where,['a.orderStatus','=',$request->orderStatus]);
//            $where2=['a.orderStatus','=',$request->orderStatus];
        }

        if ($request->has('userNo') && $request->userNo != ''){
            $where['c.userNo|b.orderNo'] = $request->userNo;
            // $where1 .= " and a.orderNo like '%{$request->userNo}%' ";
//            $where3['a.orderNo'] = ['like','%'.$request->userNo.'%'];
//            array_push($where,['a.orderNo','like','%'.$request->userNo.'%']);
//            $where3=[['a.orderNo','like','%'.$request->userNo.'%']];
//            array_push($WhereOr,['b.userNo','like','%'.$request->userNo.'%']);
            // $where1 .= " or b.userNo like '%{$request->userNo}%')";
        }
//        $where2=array_merge($where1,$where3);
//        var_dump($where);die;
//        var_dump($WhereOr);
        try {
            $data['list'] = Db::name('commission')
                ->alias('a')
                ->where($where)
                ->leftJoin('order b', 'a.orderId = b.orderId')
                ->join('user c', 'a.userId = c.userId')
                ->field('a.*, a.orderMoney as totalMoney, b.orderNo, b.orderStatus, c.userName')
                ->order('a.commissionId', 'desc')
                ->page($request->page, $request->size)
                ->select();
            foreach($data['list'] as $index => $item) {
                $data['list'][$index]['orderStatusName'] = getStatusName('commissionStatus', $item['commissionStatus']);
            }
//             $data['list'] = Db::table('tp_order')
//                 ->alias('a')
//                 ->where($where)
//                 ->where($where1)
// //                ->whereOr($WhereOr)
//                 ->join('user b','a.userId = b.userId')
//                 ->field('a.*,b.userNo')
//                 ->order("a.orderId","desc")
//                 ->select();
            return $data;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


}
