<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\comm\model\Sms;
use jiguang\Jgsdk;
use app\admin\model\Common;
/**
 * @apiDefine adminFinanceGroup admin-财务模块
 */

/**
 * @api {post} / 1. 财务主表
 * @apiName Finance
 * @apiGroup adminFinanceGroup
 * @apiSuccess {Number} withdrawalId 主键
 * @apiSuccess {Number} userId 会员Id
 * @apiSuccess {String} userName 会员名称
 * @apiSuccess {Number} withrawalPrice 提现金额
 * @apiSuccess {Number} withdrawalType 提现方式：1支付宝，2微信
 * @apiSuccess {Number} createTime 申请提现时间
 * @apiSuccess {Number} paymentTime 打款时间
 * @apiSuccess {Number} status 状态类型：1处理中,2已完成,3已拒绝
 * @apiSuccess {String} imageUrl 凭证
 * @apiVersion 1.0.0
 */
class UserWithdrawal extends Common {
    // protected $autoWriteTimestamp = true;
    // protected $createTime = 'createTime';

    /**
     * @api {post} /user/withdrawal/list 1.1 提现列表
     * @apiName UserWithdrawalPositionList
     * @apiGroup adminFinanceGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} [begintime]  开始时间时间戳
     * @apiParam {Number} [endtime]  截至时间时间戳
     * @apiParam {Number} [status] 状态类型：1处理中,2已完成,3已拒绝
     * @apiVersion 1.0.0
     */

    public function getUserWithdrawalList($request) {
        $where =[];
        if ($request->has('begintime') && $request->has('endtime')) {
            array_push($where,['createTime', '>=' ,$request->post('begintime')]);
            array_push($where,['createTime', '<=' ,$request->post('endtime')]);
        }

        if ($request->has('status') && $request->status != 0 ){
            array_push($where,['status', '=' ,$request->post('status')]);
        }

        try {
            $data['list'] = $this->where($where)
                ->page($request->post('page',1), $request->post('size',20))
                ->order('withdrawalId', 'desc')
                ->select();
            foreach ($data['list'] as $item){
                $item['statusName'] = getStatusName('UserWithdrawalPositionStatus',$item['status']);
                $item['withdrawalTypeName'] = getStatusName('withdrawalTypeName', $item['withdrawalType']);
            }
            $data['count'] = $this->where($where)->count();
            return $data;
        }catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /user/withdrawal/info 1.2 提现详细查看
     * @apiName UserWithdrawalPositionInfo
     * @apiGroup adminFinanceGroup
     * @apiParam {Number} withdrawalId 提现Id
     * @apiVersion 1.0.0
     */
    public function getUserWithdrawalInfo($request){


        if ($request->has('withdrawalId')){
            try {
                $data['info'] = $this->where('withdrawalId', $request->post('withdrawalId'))->find();
                $data['info']['statusName'] = getStatusName('UserWithdrawalPositionStatus',$data['info']['status']);
                $data['info']['withdrawalTypeName'] = getStatusName('withdrawalTypeName', $data['info']['withdrawalType']);
                return $data;
            }catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }else{
            $this->error = '提现id不存在';
            return false;
        }
    }

    /**
     * @api {post} /user/withdrawal/update 1.3 提现更新
     * @apiName UserWithdrawalPositionUpdate
     * @apiGroup adminFinanceGroup
     * @apiParam {Number} withdrawalId 提现Id
     * @apiParam {Number} status 状态信息 1处理中,2已完成,3已拒绝
     * @apiVersion 1.0.0
     */


    public function updateUserWithdrawal($request)
    {
        Db::startTrans();
        try {
            $withdrawal = Db::name('user_withdrawal')->where('withdrawalId', $request->post('withdrawalId'))->find();
            if ($withdrawal['updateTime']) {
                $this->error = '该订单已经操作过了';
                return;
            }
            if ($request->post('status') == 1) {
                $this->error = '请选择处理状态';
                return;
            }
            $data = Db::name('user_withdrawal')->where('withdrawalId', $request->post('withdrawalId'))->update([
                'status' => $request->post('status'), 
                'imageUrl' => $request->post('imageUrl'), 
                'updateTime' => time()
            ]);
            if ($data == 1) {
                if ($request->post('status') == 2){
                    $info = Db::name('user_withdrawal')->where('withdrawalId', $request->post('withdrawalId'))->find();
                    $user = Db::name('user')->where('userId', $info['userId'])->find();
                    Db::name('user')->where('userId', $info['userId'])->setInc('withdrawalAmountCount',$info['withdrawalPrice']);
                    $userToken = $this->getTokenArray($user['userId']);
                    // 消息推送
                    $push = new Jgsdk();
                    $m_type = 'https';//推送附加字段的类型
                    $m_txt = '';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
                    $m_time = '86400';//离线保留时间
                    $receive = $userToken;
                    $content = '您申请的提现'.$info['withdrawalPrice'].'元，已经转入'.$info['withdrawalAccount'].'具体到账时间以第三方平台到账通知为准。';
                    $message="";//存储推送状态
                    if ($user['userFrom'] == 2) {
                        $extras = [
                            'type' => 1,
                            'page' => ''
                        ];
                    }
                    if ($user['userFrom'] == 3) {
                        $extras = [
                            'type' => 1,
                            'page' => ''
                        ];
                    }
                    $push->push($receive,$content,$m_type,$m_txt,$m_time,$extras);

                    // 短信推送
                    $Sms = new Sms();
                    $smsParams = [
                        'monery' => $info['withdrawalPrice'],
                        'cnt' => $info['withdrawalAccount'],
                        'cnt2' => '第三方平台'
                    ];
                    $response = $Sms->sendSms($user['userPhone'], $smsParams, 'SMS_150183279', 2);
                }
                if ($request->post('status') == 3){
                    $info = Db::name('user_withdrawal')->where('withdrawalId', $request->post('withdrawalId'))->find();
                    Db::name('user')->where('userId', $info['userId'])->setInc('withdrawalAmount',$info['withdrawalPrice']);
                }
                Db::commit();
                return '更新成功';
            } else {
                $this->error = '更新失败';
                return;
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /user/withdrawal/excel 1.4 提现excel
     * @apiName withdrawalExcel
     * @apiGroup adminFinanceGroup
     * @apiParam {Number} withdrawalId 提现Id
     * @apiParam {Number} status 状态信息 1处理中,2已完成,3已拒绝
     * @apiVersion 1.0.0
     */


    public function withdrawalExcel($request)
    {
        $where =[];
        if ($request->has('begintime') && $request->has('endtime')) {
            array_push($where,['createTime', '>=' ,$request->post('begintime')]);
            array_push($where,['createTime', '<=' ,$request->post('endtime')]);
        }

        if ($request->has('status') && $request->status != 0 ){
            array_push($where,['status', '=' ,$request->post('status')]);
        }

        Db::startTrans();
        try {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', '会员名称');
            $sheet->setCellValue('B1', '会员编号');
            $sheet->setCellValue('C1', '提现金额');
            $sheet->setCellValue('D1', '提现方式');
            $sheet->setCellValue('E1', '申请提现时间');
            $sheet->setCellValue('F1', '操作时间');
            $sheet->setCellValue('G1', '状态');
            
            $i = 1;

            
            $data = Db::name('user_withdrawal')->where($where)->select();
            foreach ($data as $item) {
                $i++;
                $sheet->setCellValue('A' . $i, $item['userName']);
                $sheet->setCellValue('B' . $i, $item['userNo']);
                $sheet->setCellValue('C' . $i, $item['withdrawalPrice']);
                $sheet->setCellValue('D' . $i, $item['withdrawalType'] == 1 ? '支付宝' : '微信');
                $sheet->setCellValue('E' . $i, date('Y-m-d H:i:s', $item['createTime']));
                $sheet->setCellValue('F' . $i, date('Y-m-d H:i:s', $item['updateTime']));
                $sheet->setCellValue('G' . $i, getStatusName('UserWithdrawalPositionStatus',$item['status']));
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('excel/提现列表.xlsx');
            
            return 'excel/提现列表.xlsx';
            Db::commit();
        
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /user/finance/list 2.0 用户财务
     * @apiName UserWithdrawalPositionUserList
     * @apiGroup adminFinanceGroup
     * @apiParam {Number} [name] userId或者昵称
     * @apiVersion 1.0.0
     */

    public function getuserList($request){

        $where =[];
        $like = [];
        array_push($where,['userType', '>' ,1]);
        if ($request->has('name'))
        {
            array_push($where,['userId', '=' ,$request->post('name')]);
            array_push($like,['userName', 'like' ,'%'.$request->post('name').'%']);
        }
        try {
            $data['list'] = Db::table('tp_user')->where($where)
                ->whereOr($like)
                ->page($request->post('page',1), $request->post('size',20))
                ->field('userId,userNo,loginName,userName,withdrawalAmount,noWithdrawalAmount,withdrawalAmountCount')
                ->select();
                // ->column('userId,userNo,userName,withdrawalAmount,noWithdrawalAmount');
            // foreach ($data['list'] as $i => $item) {
            //     $user = Db::name('user')->where('superiorNo', $item['userNo'])->find();
            //     $data['list'][$i]['userCount']=Db::table('tp_user')->where('superiorNo',$item['userNo'])->count();
            //     foreach ($item as $key => $item1) {
            //         $data['list'][$i]['userCount1']=Db::table('tp_user')->where(['superiorNo' => $user['userNo'], 'userType' => 2])->count();
            //     }
            // }

            $data['count'] = Db::table('tp_user')
                ->where($where)
                ->whereOr($like)
                ->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

}
