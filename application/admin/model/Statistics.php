<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Cache;
use Carbon\Carbon;
use app\admin\model\Common;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @apiDefine adminDataGroup admin-数据报表
 */

class Statistics extends Common {


    private  $firstOfMonth;//本月第一天
    private  $lastOfMonth;//本月最后一天
    private  $startOfWeek;//本周第一天
    private  $endOfWeek;//本周最后一天
    private  $startOfDay;//本日开始
    private  $endOfDay;//本日结束

    public function __construct()
    {
        $this->firstOfMonth = Carbon::now()->firstOfMonth()->timestamp;
        $this->lastOfMonth = Carbon::now()->lastOfMonth()->timestamp;
        $this->startOfWeek = Carbon::now()->startOfWeek()->timestamp;
        $this->endOfWeek = Carbon::now()->endOfWeek()->timestamp;
        $this->startOfDay = Carbon::now()->startOfDay()->timestamp;
        $this->endOfDay = Carbon::now()->endOfDay()->timestamp;
    }

    /**
     * {post} /statistics/goods 1 商品销售排行
     * @apiGroup adminDataGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} [begintime]  开始时间时间戳
     * @apiParam {Number} [endtime]  截至时间时间戳
     * @apiParam {String} [orderBy]  排序 desc或asc
     * @apiVersion 1.0.0
     */


    public function getgoodsList($request)
    {
        $where =[];
        
        if ($request->has('timeType')) {
            if ($request->post('timeType')) {
                $where[] = ['b.orderStatus', 'in', $request->post('timeType')];
            } else {
                $where[] = ['b.orderStatus', 'in', [2,3,4]];
            }
            // if ($request->post('timeType') == 4) {
            //     $where[] = ['b.orderStatus', '=', 4];
            // }
            if ($request->has('begintime')) {
                $where[] = ['b.paymentTime', '>=' ,$request->post('begintime')];
                $where[] = ['b.paymentTime', '<=' ,$request->post('endtime')];
            }
        }
        try {
           /* $data['list'] = Db::table('tp_order_goods')->where($where)
                ->alias('a')
                ->join('goods b','a.goodsId = b.goodsId')
                ->group('a.goodsId')
                ->field('b.goodsName,count(*) as saleCount')
                ->select();*/
           $data['list'] = Db::name('order_goods')->alias('a')
           ->where($where)
               ->join('order b', 'a.orderId = b.orderId')
               ->field('b.orderStatus, a.goodsName, SUM(a.goodsNum) as saleCount')
               ->group('goodsId')
               ->page($request->post('page',1), $request->post('size',20))
               ->order('saleCount',$request->post('orderBy','desc'))
               ->select();

            $data['count'] = Db::name('order_goods')->alias('a')
                ->join('order b', 'a.orderId = b.orderId')
                ->field('b.orderStatus, a.goodsName, SUM(a.goodsNum) as saleCount')
                ->group('goodsId')
                ->where($where)
                ->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * {post} /statistics/orders 1.1 平台流水
     * @apiName UserWithdrawalPositionList
     * @apiGroup adminDataGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} [begintime]  开始时间时间戳
     * @apiParam {Number} [endtime]  截至时间时间戳
     * @apiVersion 1.0.0
     */

    public function getordersList($request)
    {
        $where = [];
        $orderWhere = [];
        if ($request->has('begintime') && $request->has('endtime')) {
            array_push($where,['createTime', '>=' ,$request->post('begintime')]);
            array_push($where,['createTime', '<=' ,$request->post('endtime')]);
            $orderWhere[] = ['paymentTime', '>=' ,$request->post('begintime')];
            $orderWhere[] = ['paymentTime', '<=' ,$request->post('endtime')];
        }
        $where[] = ['commissionStatus', 'in' , [1, 2]];
        $orderWhere[] = ['orderStatus', 'in', [2,3,4]];
        // $orderWhere[] = ['commissionStatus', 'in' , [1, 2]];
        // else{
        //     array_push($where,['a.createTime', '>=' ,1519833600]);
        //     array_push($where,['a.createTime', '<=' ,time()]);
        // }


        try {
            $data['list'] = Db::name('commission')->where($where)
                // ->page($request->post('page',1), $request->post('size',20))
                //->join('commission b','b.orderId = a.orderId')
                ->group("DAY(FROM_UNIXTIME(createTime))")
                ->field("createTime,SUM(commissionMoney) as commissionMoney, SUM(orderMoney) as orderMoney,orderId")
                ->order('createTime', 'desc')
                ->select();
            $order = Db::name('order')->where($orderWhere)
                ->group("DAY(FROM_UNIXTIME(paymentTime))")
                ->field('count(orderId) as number, paymentTime')
                ->order('paymentTime', 'desc')
                ->select();
            $number = 0;
            foreach ($data['list'] as $key => &$item){
                $item['number'] = $order[$key]['number'];
                $number += $item['number'];
                $item['receiveTime'] = strtotime(date("Y-m-d",$item['createTime']));
            }
            $data['count'] =  Db::name('commission')->where($where)
                ->group("DAY(FROM_UNIXTIME(createTime))")
                ->count();
            $data['allmoney'] = $this->getAllmoney($request);
            $data['allmoney']['number'] = $number;
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    public function getAllmoney($request)
    {
        $where = [];
        if ($request->has('begintime') && $request->has('endtime')) {
            array_push($where,['createTime', '>=' ,$request->post('begintime')]);
            array_push($where,['createTime', '<=' ,$request->post('endtime')]);
            array_push($where, ['commissionStatus', 'in', [1,2]]);
        }
        $commissions = Db::name('commission')->where($where)
            // ->group("orderId")
            // ->field("*")
            // ->field("SUM(orderMoney) as orderMoney,COUNT(1) as number,SUM(commissionMoney) as commissionMoney")
            ->select();
        $data['orderMoney'] = 0;
        $data['commissionMoney'] = 0;
        foreach ($commissions as $commission){
            $data['orderMoney'] += $commission['orderMoney'];
            $data['commissionMoney'] += $commission['commissionMoney'];
        }
        return $data;
    }

    // 打印平台流水
    public function getOrdersListExcel($request) {
        $where = [];
        $where[] = ['a.commissionStatus', 'in', [1,2]];
        if ($request->has('begintime') && $request->has('endtime')) {
            array_push($where,['a.createTime', '>=' ,$request->post('begintime')]);
            array_push($where,['a.createTime', '<=' ,$request->post('endtime')]);
        }
        else{
            array_push($where,['a.createTime', '>=' ,1519833600]);
            array_push($where,['a.createTime', '<=' ,time()]);
        }


        try {
            $data['list'] = Db::name('commission')->where($where)
                ->alias('a')
                ->page($request->post('page',1), $request->post('size',20))
                //->join('commission b','b.orderId = a.orderId')
                ->group("DAY(FROM_UNIXTIME(a.createTime))")
                ->field("COUNT(a.commissionId) as number,createTime,SUM(a.commissionMoney) as commissionMoney, SUM(a.orderMoney) as orderMoney")
                // ->field("*")
                ->select();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', '时间');
            $sheet->setCellValue('B1', '订单数');
            $sheet->setCellValue('C1', '销售流水金额');
            $sheet->setCellValue('D1', '返现金额');
            
            $i = 1;

            foreach ($data['list'] as $item){
                $i++;
                $sheet->setCellValue('A' . $i, date('Y-m-d H:i:s', $item['createTime']));
                $sheet->setCellValue('B' . $i, $item['number']);
                $sheet->setCellValue('C' . $i, $item['orderMoney']);
                $sheet->setCellValue('D' . $i, $item['commissionMoney']);
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('excel/平台流水.xlsx');
            return 'excel/平台流水.xlsx';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    

    /**
     * {post} /index 1.主页
     * @apiName UserWithdrawalPositionList
     * @apiGroup adminDataGroup
     * @apiVersion 1.0.0
     */
    public function index()
    {
        // 当月数据
        $data['pending']['order'] = Db::table('tp_order')->where('orderStatus',2)->count();
        $data['pending']['refund'] = Db::table('tp_order_refund')->where('refundStatus',1)->count();
        $data['pending']['withdrawal'] = Db::table('tp_user_withdrawal')->where('status',1)->count();
        $data['pending']['user'] = Db::table('tp_user_withdrawal')->where('status',1)->count();

        $array = [
            ['dbname'=>'tp_user','where'=>[['userType','in',[1,2,3,4]]],'timeField'=>'createTime','prefix'=>'user','sumquery'=>false,'sumField' => null],
            ['dbname'=>'tp_user','where'=>[['userType','>=',2]],'timeField'=>'auditTime','prefix'=>'vip','sumquery'=>false,'sumField' => null],
            ['dbname'=>'tp_order','where'=>[['orderStatus','in',[2,3,4]]],'timeField'=>'createTime','prefix'=>'order','sumquery'=>false,'sumField' => null],
            ['dbname'=>'tp_user_withdrawal','where'=>[['status','=',2]],'timeField'=>'updateTime','prefix'=>'withdrawal','sumquery'=>true,'sumField' => 'withdrawalPrice'],
            ['dbname'=>'tp_commission','where'=>[['commissionStatus','in',[1,2]]],'timeField'=>'createTime','prefix'=>'commission','sumquery'=>true,'sumField' => 'commissionMoney'],
            ['dbname'=>'tp_order','where'=>[['orderStatus','in',[2,3,4]]],'timeField'=>'createTime','prefix'=>'orderMoney','sumquery'=>true,'sumField' => 'payableMoney'],
        ];
        foreach ($array as $item){
            $data[$item['prefix']] = $this->cache_field($item['dbname'],$item['where'],$item['timeField'],$item['prefix'],$item['sumquery'],$item['sumField']);
        }

        // 平台数据总览
        // 1、总有效订单
        // $data['all']['orderCount'] = Db::name('order')->where([
        //     ['orderStatus', 'in', [2,3,4]]
        // ])->count();
        // 2、总有效金额
        // $orderPriceCount = Db::name('order')->where([
        //     ['orderStatus', 'in', [2,3,4]]
        // ])->field('SUM(payMoney) as orderPriceCount')->find();
        // $data['all']['orderPriceCount'] = $orderPriceCount['orderPriceCount'];
        // 3、总返利金额
        // $commissionPriceCount = Db::name('commission')->where([
        //     ['commissionStatus', 'in', [1,2]]
        // ])->field('SUM(commissionMoney) as commissionPriceCount')->find();
        // $data['all']['commissionPriceCount'] = $commissionPriceCount['commissionPriceCount'];
        // 已结算待提现
        $user = Db::name('user')->where([
            ['userType', '>=', 1]
        ])->field('SUM(withdrawalAmount) as withdrawalAmount, SUM(freezeAmount) as freezeAmount, SUM(noWithdrawalAmount) as noWithdrawalAmount')->find();
        $data['all']['withdrawalAmount'] = $user['withdrawalAmount'];
        $data['all']['freezeAmount'] = $user['freezeAmount'];
        $data['all']['noWithdrawalAmount'] = $user['noWithdrawalAmount'];
        // 提现中
        $withdrawal = Db::name('user_withdrawal')->field('SUM(withdrawalPrice) as withdrawalPrice')->where('status', 1)->find();
        $data['all']['withdrawaling'] = $withdrawal['withdrawalPrice'];

        return $data;
    }

    public function getRelationMap () {
        try {
            $list = Db::name('user')->where('userType', '>', 1)->field('userId, superiorId, userName as name')->select();
            $data['userList']['name'] = '系统';
            $data['userList']['children'] = $this->getTree($list);
            return $data;
        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getTree ($array, $pid = null, $level = 0) {
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        $list = [];
        foreach ($array as $key => $value){
            if ($value['superiorId'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $value['children'] = $this->getTree($array, $value['userId'], $level+1);
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                // unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                

            }
        }
        return $list;
    }


    public function cache_field($dbname,$where,$timeField,$prefix,$sumquery = false,$sumField=null)
    {
        $prefix_all = 'all' . $prefix;
        $prefix_month = 'month' . $prefix;
        $prefix_week = 'week' . $prefix;
        $prefix_day = 'day'. $prefix;
        $monthWhere = $where;
        array_push($monthWhere,[$timeField,'>',$this->firstOfMonth]);
        array_push($monthWhere,[$timeField,'<',$this->lastOfMonth]);
        $weekWhere = $where;
        array_push($weekWhere,[$timeField,'>',$this->startOfWeek]);
        array_push($weekWhere,[$timeField,'<',$this->endOfWeek]);
        $dayWhere = $where;
        array_push($dayWhere,[$timeField,'>',$this->startOfDay]);
        array_push($dayWhere,[$timeField,'<',$this->endOfDay]);

        $array = [
            ['prefix_name' => 'month' . $prefix,'where'=>$monthWhere],
            ['prefix_name' => 'week' . $prefix,'where'=>$weekWhere],
            ['prefix_name' => 'day' . $prefix,'where'=>$dayWhere],
        ];


        foreach ($array as $item)
        {
            // if (!Cache($item['prefix_name'])){
            //     if ($sumquery){
            //         $number = Db::table($dbname)->where($item['where'])->sum($sumField);
            //     }else{
            //         $number = Db::table($dbname)->where($item['where'])->count();
            //     }
            //     Cache($item['prefix_name'],$number,86400);
            // }
            
            if ($sumquery){
                $number = Db::table($dbname)->where($item['where'])->sum($sumField);
            }else{
                $number = Db::table($dbname)->where($item['where'])->count();
            }
            $data[$item['prefix_name']] = $number;

        }




        if ($sumquery) {
            $data[$prefix_all] = Db::table($dbname)->where($where)->sum($sumField);
        }else{
            $data[$prefix_all] = Db::table($dbname)->where($where)->count();
        }

        // $data[$prefix_month] = Cache($prefix_month);
        // $data[$prefix_week] = Cache($prefix_week);
        // $data[$prefix_day] = Cache($prefix_day);

        return $data;
    }

    /**
     * {post} /admin/statistics/transaction/profile 3.交易概况
     * @apiName TransactionProfile
     * @apiGroup adminDataGroup
     * @apiParam {Number} timeStart 开始时间
     * @apiParam {Number} timeStop 结束时间
     * @apiSuccess {Number} allValidOrder 总有效订单
     * @apiSuccess {Float} allValidMoeny 总有效金额
     * @apiSuccess {Number} allValidMember 总会员数 三个总数在数组外层
     * @apiSuccess {String} time 日期
     * @apiSuccess {Float} validMoney 有效交易金额
     * @apiSuccess {Number} validOrder 有效交易订单
     * @apiSuccess {Number} newMember 新增会员数
     * @apiSuccess {Float} commissionMoney 返现金额
     * @apiVersion 1.0.0
     */
    public function TransactionProfile($params){

//        $timestart = $this->firstOfMonth;
//        $timestop = $this->endOfDay;

//        if ($params->has('timeStart')){
            $timestart = $params->post('timeStart');
            $timestop = $params->post('timeStop');
            $timestop = $timestop+60*60*24;
//        }
        $times = $timestop-$timestart;
        $days = $times/60/60/24;
        $whereOrder = "paymentTime >= $timestart and paymentTime <= $timestop and (orderStatus = 2 or orderStatus = 3 or orderStatus=4)";
        // 有效订单 validOrder
        // 有效交易金额 validMoney
        $order = Db::name('order')->field('FROM_UNIXTIME(paymentTime,\'%Y-%m-%d\') as paymentTime,payMoney')->where($whereOrder)->select();
        // 有效交易金额 validMoney
        $commission = Db::name('commission')->field('FROM_UNIXTIME(createTime,\'%Y-%m-%d\') as createTime,commissionMoney')->where([
            ['commissionStatus', 'in', [1,2]],
            ['createTime', '>=', $timestart],
            ['createTime', '<=', $timestop]
        ])->select();
        // 新增会员数 newMember
        $whereMem = "userType >= 2 and memberTime >= $timestart and memberTime <= $timestop";
        $user = Db::name('user')->field('FROM_UNIXTIME(memberTime,\'%Y-%m-%d\') as memberTime')->where($whereMem)->select();
        // 总提现金额
        $allWithdrawal = Db::name('user_withdrawal')->where([
            ['status', '=', 2],
            ['updateTime', '>=', $timestart],
            ['updateTime', '<=', $timestop]
        ])->field('SUM(withdrawalPrice) as withdrawalPrice')->find();

        // 未确认收货返利金额
        $allNoCommission = Db::name('commission')->where([
            ['commissionStatus', '=', 1],
            ['createTime', '>=', $timestart],
            ['createTime', '<=', $timestop]
        ])->field('SUM(commissionMoney) as commissionMoney')->find();
        // 总返利金额
        $allCommission = Db::name('commission')->where([
            ['commissionStatus', 'in', [1,2]],
            ['createTime', '>=', $timestart + 60*60*24],
            ['createTime', '<=', $timestop]
        ])->field('SUM(commissionMoney) as commissionMoney')->find();
        // 总有效订单 allValidOrder
        // $allValidOrder = 0;
        // 总有效金额 allValidMoeny
        $allValidMoeny = 0;
        // 总返利金额
        // 总会员数 allValidMember
        $allValidMember = 0;
        $allReapMoeny = 0;
        $result = array();
        $obj = array();
        $nextTime = $timestop-60*60*24;


        for($i=0;$i<$days-1;$i++){
            $time = date("Y-m-d",$nextTime);
            $result[$i]['time'] = $time;
            $result[$i]['validMoney'] = 0;
            $result[$i]['validOrder'] = 0;
            $result[$i]['newMember'] = 0;
            $result[$i]['commissionMoney'] = 0.00;
            for($o=0;$o<count($order);$o++){
                if ($time == $order[$o]['paymentTime']){
                    // $allValidOrder =  $allValidOrder + 1;
                    $allValidMoeny =  $allValidMoeny + $order[$o]['payMoney'];
                    $result[$i]['validMoney'] = $result[$i]['validMoney'] + $order[$o]['payMoney'];
                    $result[$i]['validOrder'] = $result[$i]['validOrder'] + 1;
                }
            }
            for($u=0;$u<count($user);$u++){
                if($time == $user[$u]['memberTime']){
                    $result[$i]['newMember'] = $result[$i]['newMember'] + 1;
                    $allValidMember = $allValidMember + 1;
                }
            }
            for($c=0;$c<count($commission);$c++) {
                if($time == $commission[$c]['createTime']){
                    $result[$i]['commissionMoney'] += $commission[$c]['commissionMoney'];
                }
            }
            $nextTime = $nextTime - 60*60*24;
        }
        $obj['list'] = $result;
        $obj['allNoCommission'] = $allNoCommission['commissionMoney'];
        $obj['allWithdrawal'] = $allWithdrawal["withdrawalPrice"];
        $obj['allCommission'] = $allCommission['commissionMoney'];
        // $obj['allValidOrder'] = $allValidOrder;
        $obj['allValidMoeny'] = $allValidMoeny;
        $obj['allReapMoeny'] = $allReapMoeny;
        $obj['newMember'] = $allValidMember;
        return $obj;
    }
    /**
     * {post} /admin/statistics/comprehensive/overview 4.综合概况
     * @apiName ComprehensiveOverview
     * @apiGroup adminDataGroup
     * @apiParam {Number} timeStart 开始时间
     * @apiParam {Number} timeStop 结束时间
     * @apiSuccess {Number} allTotalOrder 交易订单数
     * @apiSuccess {Float} allTotalMoney 交易总金额
     * @apiSuccess {Number} totalMember 累计会员数
     * @apiSuccess {String} time 日期
     * @apiSuccess {Float} validMoney 有效交易金额
     * @apiSuccess {Number} validOrder 有效交易订单
     * @apiSuccess {Number} newMember 新增会员数
     * @apiSuccess {Number} newRegisterMember 新增注册数
     * @apiSuccess {Number} totalRegisterMember 累计注册数
     * @apiSuccess {Number} drawalMoeny 提现金额
     * @apiVersion 1.0.0
     */
    public function ComprehensiveOverview($params){
        $timeStart = $this->firstOfMonth;
        $timeStop = $this->endOfDay;
        if($params->has('timeStart')){
            $timeStart = $params->post('timeStart');
            $timeStop = $params->post('timeStop');
            $timeStop = $timeStop+60*60*24;
        }
        $whereOrder = "paymentTime >= $timeStart and paymentTime <= $timeStop and (orderStatus = 2 or orderStatus = 3 or orderStatus=4)";
        //        有效订单 validOrder
        //        有效交易金额 validMoney
        $ordervalid = Db::name('order')->field('FROM_UNIXTIME(paymentTime,\'%Y-%m-%d\') as paymentTime,payMoney')->where($whereOrder)->select();
        //        交易订单数 allTotalOrder
        //        交易金额 allTotalMoney
        $whereAllOrder = "paymentTime >= $timeStart and paymentTime <= $timeStop";
        $order = Db::name('order')->field('FROM_UNIXTIME(paymentTime,\'%Y-%m-%d\') as paymentTime,payMoney')->where($whereAllOrder)->select();
        //        新增会员数 newMember
        //        累计会员数 totalMember
        $whereMem = "userType >= 2 and memberTime >= $timeStart and memberTime <= $timeStop";
        $user = Db::name('user')->field('FROM_UNIXTIME(memberTime,\'%Y-%m-%d\') as memberTime')->where($whereMem)->select();
        //        新增注册数 newRegisterMember
        //        累计注册数 totalRegisterMember
        $whereReg = "createTime >= $timeStart and createTime <= $timeStop";
        $userReg = Db::name('user')->field('FROM_UNIXTIME(createTime,\'%Y-%m-%d\') as createTime')->where($whereReg)->select();
//        提现 drawalMoeny

        $whereW = "createTime >= $timeStart and createTime <= $timeStop";
        $withdrawal = Db::name('user_withdrawal')->field('FROM_UNIXTIME(updateTime,\'%Y-%m-%d\') as updateTime,FROM_UNIXTIME(createTime,\'%Y-%m-%d\') as createTime,withdrawalPrice')->where($whereW)->select();
        $times = $timeStop-$timeStart;
        $days = $times/60/60/24;
        $result = array();
        $obj = array();
        $nextTime = $timeStop-60*60*24;
        for($i=0;$i<$days-1;$i++) {
            $time = date("Y-m-d", $nextTime);
            $result[$i]['time'] = $time;
//            订单
            $result[$i]['validMoney'] = 0;
            $result[$i]['validOrder'] = 0;
            $result[$i]['allTotalOrder'] = 0;
            $result[$i]['allTotalMoney'] = 0;
            for($v=0;$v<count($ordervalid);$v++){
                if ($time == $ordervalid[$v]['paymentTime']){
                    $result[$i]['validMoney'] = $result[$i]['validMoney'] + $ordervalid[$v]['payMoney'];
                    $result[$i]['validOrder'] = $result[$i]['validOrder'] + 1;
                }
            }
            for($o=0;$o<count($order);$o++){
                if ($time == $order[$o]['paymentTime']){
                    $result[$i]['allTotalMoney'] = $result[$i]['allTotalMoney'] + $order[$o]['payMoney'];
                    $result[$i]['allTotalOrder'] = $result[$i]['allTotalOrder'] + 1;
                }
            }
//会员
            $result[$i]['newMember'] = 0;
            $result[$i]['totalMember'] = 0;
            $result[$i]['newRegisterMember'] = 0;
            $result[$i]['totalRegisterMember'] = 0;
           
            // 用户
            for($u=0;$u<count($user);$u++){
                if($time == $user[$u]['memberTime']){
                    $result[$i]['newMember'] = $result[$i]['newMember'] + 1;
//                    $result[$i]['totalMember'] = $result[$i]['totalMember'] +1;
                    // $whereMemAll = "userType >= 2 and memberTime <= $nextTime";
                    // $whereMemAll = "userType >= 2";
                    // $result[$i]['totalMember'] = Db::name('user')->where($whereMemAll)->count();
                }
            }
            $whereMemAll = "userType >= 2 and memberTime <= $nextTime";
            $result[$i]['totalMember'] = Db::name('user')->where($whereMemAll)->count();

            for($r=0;$r<count($userReg);$r++){
                if($time == $userReg[$r]['createTime']){
                    $result[$i]['newRegisterMember'] = $result[$i]['newRegisterMember'] + 1;
//                    $result[$i]['totalRegisterMember'] = $result[$i]['totalRegisterMember'] +1;
                    // $whereRegAll = "createTime <= $nextTime";
                    // $result[$i]['totalRegisterMember'] = Db::name('user')->where($whereRegAll)->count();
                }
            }
            $whereRegAll = "createTime <= $nextTime";
            $result[$i]['totalRegisterMember'] = Db::name('user')->where($whereRegAll)->count();
//            提现
            $result[$i]['drawalMoeny'] = 0;
            for($w=0;$w<count($withdrawal);$w++){
                if($time == $withdrawal[$w]['updateTime'] || $time == $withdrawal[$w]['createTime']){
                    $result[$i]['drawalMoeny'] = $result[$i]['drawalMoeny'] + $withdrawal[$w]['withdrawalPrice'];
                }
            }
            $nextTime = $nextTime - 60*60*24;
        }
        $obj['list'] = $result;
        
        return $obj;
    }


}
