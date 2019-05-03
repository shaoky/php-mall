<?php
namespace app\admin\model;
use think\Db;
use app\admin\model\Common;
use app\admin\model\WebConfig;
use app\comm\model\Sms;
use Naixiaoxin\ThinkWechat\Facade;
use jiguang\Jgsdk;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/**
 * @apiDefine adminOrderGroup admin-订单
 */

class Order extends Common {
    /**
     * @api {post} /admin/user/order/list 1.1 订单列表
     * @apiName getList
     * @apiGroup adminOrderGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiVersion 1.0.0
     */
    public function getList($params) {
        $where = [];
        if (!empty($params['timeType'])) {
            if ($params['timeType'] == 2) {
                $where[] = ['a.paymentTime', '>=', $params['startTime']];
                $where[] = ['a.paymentTime', '<=', $params['endTime']];
            }
            if ($params['timeType'] == 3) {
                $where[] = ['a.deliveryTime', '>=', $params['startTime']];
                $where[] = ['a.deliveryTime', '<=', $params['endTime']];
            }
        }
        if (!empty($params['orderStatus'])) {
            $where[] = ['a.orderStatus', '=', $params['orderStatus']];
        }
        if (!empty($params['orderNo'])) {
            $where[] = ['a.orderNo', '=', $params['orderNo']];
        }
        if (!empty($params['userName'])) {
            $where[] = ['b.userName|b.userNo', 'like', '%'.$params['userName'].'%'];
        }
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        
        try {
            $data['list'] = db('Order')->alias('a')
            ->join('user b', 'a.userId = b.userId')
            ->field('a.*, b.userNo')
            ->where($where)
            ->page($params['page'], $params['size'])
            ->order('orderId', 'desc')->select();
            $data['count'] = db('Order')->alias('a')->join('user b', 'a.userId = b.userId')->field('a.*, b.userNo')->where($where)->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/order/info 1.2 订单详情
     * @apiName getInfo
     * @apiGroup adminOrderGroup
     * @apiParam {Number} orderId 订单id
     * @apiVersion 1.0.0
     */
    public function getInfo($params) {
        try {
            $order = db('order')->where('orderId', $params['orderId'])->find();
            $order['goodsList'] = db('order_goods')->where('orderId', $params['orderId'])->select();
            $order['payTypeName'] = getStatusName('payType', $order['payType']);
            $order['orderStatusName'] = getStatusName('orderStatus', $order['orderStatus']);
            $order['orderFromName'] = getStatusName('orderFrom', $order['orderFrom']);


            if ($order['orderStatus'] === 5 || $order['orderStatus'] === 6) {
                $refundInfo = db('order_refund')->where('orderId', $order['orderId'])->find();
                foreach($order['goodsList'] as $item) {
                    $item = [
                        'goodsName' => $item['goodsName'],
                        'goodsPrice' => $item['goodsPrice'],
                        'goodsNum' => $item['goodsNum'],
                        'refundNo' => $refundInfo['refundNo'],
                        'createTime' => $refundInfo['createTime'],
                        'refundStatusName' => getStatusName('refundStatus', $refundInfo['refundStatus']),
                    ];
                    $order['refund'][] = $item;
                }
            } else {
                foreach($order['goodsList'] as &$item) {
                    $goods = Db::name('goods')->where('goodsId', $item['goodsId'])->field('goodsSource, goodsSourceUrl')->find();
                    $item['goodsSource'] = $goods['goodsSource'];
                    $item['goodsSourceUrl'] = $goods['goodsSourceUrl'];
                }
            }
            return [
                'info' => $order
            ];
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/order/delivery 1.3 订单发货
     * @apiName getInfo
     * @apiGroup adminOrderGroup
     * @apiParam {Number} orderId 订单id
     * @apiParam {String} courierName 快递名称
     * @apiParam {String} ordcourierNoerId 快递单号
     * @apiVersion 1.0.0
     */
    public function setDelivery($params) {
        header('Content-Type: text/plain; charset=utf-8');
        $webInfo = WebConfig::info();
        
        // dump($webInfo['autoConfirmDelivery']);

        Db::startTrans();
        try {
            $update = [
                'orderStatus' => 3,
                'courierName' => $params['courierName'],
                'courierNo' => $params['courierNo'],
                'deliveryTime' => time(),
                'confirmTime' => strtotime(date("Y-m-d H:i:s", strtotime('+'.$webInfo['autoConfirmDelivery'] .'day')))
            ];
            $data = $this->where('orderId', $params['orderId'])->update($update);
            
            $order = Db::name('order')->where('orderId', $params['orderId'])->find();
            $user = Db::name('user')->where('userId', $order['userId'])->find();
            if ($user['openid']) {
                $app = Facade::officialAccount();
                $app->template_message->send([
                    'touser' => $user['openid'],
                    'template_id' => 'H0VrlM_1UoQFtqtU3_pijGHO_HsS9gNcIlH5Q53C5c0',
                    'data' => [
                        'first' => '亲，您购买的商品已于今日登上飞船，向你处出发！',
                        'keyword1' => $params['courierName'],
                        'keyword2' => $params['courierNo'],
                        'keyword3' => $order['userName'],
                        'keyword4' => $order['userPhone'],
                        'keyword5' => $order['provinceName'].$order['cityName'].$order['countyName'].$order['userAddress'],
                        'remark' => '如航班有延误，请及时联系塔台。'
                    ],
                ]);
                $templateList = $app->template_message->getPrivateTemplates();
            }

            // $order['isMemberGoods'] == 1 ? $orderFrom = 2 : $orderFrom = 1;
            $userToken = $this->getTokenArray($order['userId']);
            // 消息推送
            if ($order['orderFrom'] == 2 || $order['orderFrom'] == 3) {
                $push = new Jgsdk();
                $m_type = 'https';//推送附加字段的类型
                $m_txt = '';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
                $m_time = '86400';//离线保留时间
                $receive = $userToken;
                $content = '您的订单已成功发货，点击查看';
                $message="";//存储推送状态
                if ($order['orderFrom'] == 2) {
                    $extras = [
                        'type' => 1,
                        'page' => ''
                    ];
                }
                if ($order['orderFrom'] == 3) {
                    $extras = [
                        'type' => 1,
                        'page' => ''
                    ];
                }
                
                $result = $push->push($receive,$content,$m_type,$m_txt,$m_time,$extras,$order['orderApp']);
            }
            
            
            // 短信推送
            $orderGoodsList = Db::name('order_goods')->where('orderId', $order['orderId'])->select();
            if (count($orderGoodsList) > 1) {
                $str = '等商品';
            } else {
                $str = '';
            }
            $Sms = new Sms();
            $smsParams = [
                'name' => $order['userName'],
                // 'order' => $order['orderGoodsList'][0]['goodsName'].$str,
                'order' => replaceSpecialChar(mb_substr($orderGoodsList[0]['goodsName'], 0 , 15)).$str,
                // 'express' => $order['courierName'].'-'.$order['courierNo']
                'exname' => $order['courierName'],
                'exno' => $order['courierNo']
            ];
            
            $response = $Sms->sendSms($order['userPhone'], $smsParams, 'SMS_151575440', $order['orderApp']);
            if ($response->Code == 'OK') {
                // return [
                //     'message' => '发送成功'
                // ];
            } else {
                $this->error = $response;
                return;
            }
            Db::commit();   
            return '发货成功';
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/order/cancel 1.3 订单取消
     * @apiName getInfo
     * @apiGroup adminOrderGroup
     * @apiParam {Number} orderId 订单id
     * @apiVersion 1.0.0
     */
    public function setCancel($params) {
        Db::startTrans();
        try {
            $where = [
                'orderId' => $params['orderId']
            ];
            $order = Db::name('order')->where($where)->find();
            if ($order['orderStatus'] == 2 || $order['orderStatus'] == 3) {
                $data = Db::name('order')->where($where)->update(['orderStatus' => 7]);
                if ($data) {
                    Db::commit();
                    return '取消成功';
                } else {
                    $this->error = '取消失败';
                    return;
                }
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getOrderExcel($params) {
        $where = [];
        if (!empty($params['timeType'])) {
            if ($params['timeType'] == 2) {
                $where[] = ['a.paymentTime', '>=', $params['startTime']];
                $where[] = ['a.paymentTime', '<=', $params['endTime']];
            }
            if ($params['timeType'] == 3) {
                $where[] = ['a.deliveryTime', '>=', $params['startTime']];
                $where[] = ['a.deliveryTime', '<=', $params['endTime']];
            }
        }
        if (!empty($params['orderStatus'])) {
            $where[] = ['a.orderStatus', '=', $params['orderStatus']];
        }
        if (!empty($params['orderNo'])) {
            $where[] = ['a.orderNo', '=', $params['orderNo']];
        }
        if (!empty($params['userName'])) {
            $where[] = ['b.userName|b.userNo', 'like', '%'.$params['userName'].'%'];
        }

        Db::startTrans();
        try {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', '订单号');
            $sheet->setCellValue('B1', '订单类型');
            $sheet->setCellValue('C1', '收货人');
            $sheet->setCellValue('D1', '手机号');
            $sheet->setCellValue('E1', '收货地址');
            $sheet->setCellValue('F1', '商品名称');
            $sheet->setCellValue('G1', '商品单价');
            $sheet->setCellValue('H1', '商品数量');
            $sheet->setCellValue('I1', '商品金额');
            $sheet->setCellValue('J1', '订单金额');
            $sheet->setCellValue('K1', '应付金额');
            $sheet->setCellValue('L1', '实付金额');
            $sheet->setCellValue('M1', '下单时间');
            $sheet->setCellValue('N1', '支付时间');
            $sheet->setCellValue('O1', '发货时间');
            $sheet->setCellValue('P1', '快递名称');
            $sheet->setCellValue('Q1', '快递单号');
            
            $i = 1;

            $data = Db::name('order')->alias('a')
            ->join('user b', 'a.userId = b.userId')
            ->field('a.*, b.userNo')
            ->where($where)
            ->select();
            
            // $data = Db::name('order')->where($where)->order('orderId', 'desc')->select();
            foreach ($data as $item) {
                $orderGoodsList = Db::name('order_goods')->where('orderId', $item['orderId'])->select();
                foreach($orderGoodsList as $item1) {
                    $i++;
                    $sheet->setCellValue('A' . $i, $item['orderNo']);
                    $sheet->setCellValue('B' . $i, getStatusName('orderStatus', $item['orderStatus']));
                    $sheet->setCellValue('C' . $i, $item['userName']);
                    $sheet->setCellValue('D' . $i, $item['userPhone']);
                    $sheet->setCellValue('E' . $i, $item['provinceName'].$item['cityName'].$item['countyName'].$item['userAddress']);
                    $sheet->setCellValue('F' . $i, $item1['goodsName']);
                    $sheet->setCellValue('G' . $i, $item1['goodsPrice']);
                    $sheet->setCellValue('H' . $i, $item1['goodsNum']);
                    $sheet->setCellValue('I' . $i, $item1['goodsPrice'] * $item1['goodsNum']);
                    $sheet->setCellValue('J' . $i, $item['totalMoney']);
                    $sheet->setCellValue('K' . $i, $item['payableMoney']);
                    $sheet->setCellValue('L' . $i, $item['payMoney']);
                    $sheet->setCellValue('M' . $i, date('Y-m-d H:i:s', $item['createTime']));
                    $sheet->setCellValue('N' . $i, date('Y-m-d H:i:s', $item['paymentTime']));
                    $sheet->setCellValue('O' . $i, date('Y-m-d H:i:s', $item['deliveryTime']));
                    $sheet->setCellValue('P' . $i, $item['courierName']);
                    $sheet->setCellValue('Q' . $i, $item['courierNo']);
                }
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('excel/订单列表.xlsx');
            Db::commit();
            return 'excel/订单列表.xlsx';
        
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    

    public function refundStatus() {
        $pay = Db::name('pay')->where(['orderNo' => $params['orderNo']])->field('thirdOrderNo')->find();
        WxPay::refundStatus($pay['thirdOrderNo']);
    }

}
