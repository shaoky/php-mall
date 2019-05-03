<?php  
namespace app\admin\model;  
use think\Db;
use app\admin\model\Common;
/**
 * @apiDefine adminUserGroup admin-用户管理
 */
class User extends Common {
    /**
     * @api {post} / 1. 用户列表
     * @apiName userList
     * @apiGroup adminUserGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiVersion 1.0.0
     */
    public function list($params) {
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }

        if (empty($params['userName'])) {
            $params['userName'] = '';
        }
        if (isset($params['isMember'])) {
            $where[] = ['a.isBuyMemberGoods', '=', $params['isMember']];
        }
        $where[] = ['a.userName|a.userNo|a.loginName', 'like', '%'.$params['userName'].'%'];
       
        
        try {
            $data['list'] = $this->alias('a')
                ->leftJoin('user b', 'a.superiorNo = b.userNo')
                ->field('a.*, b.userName as superiorName')
                ->where($where)
                ->page($params['page'], $params['size'])
                ->order('userId', 'desc')
                ->select();
            foreach($data['list'] as $item) {
                if ($item['auditStatus'] == 2) {
                    $item['auditStatusName'] = '会员';
                } else {
                    $item['auditStatusName'] = '非会员';
                }
            } 
            $data['count'] = $this->alias('a')
                ->where($where)
                ->page($params['page'], $params['size'])
                ->order('userId', 'desc')
                ->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} / 2.1 会员列表
     * @apiName memberList
     * @apiGroup adminUserGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} [userType] 用户级别，2会员，3经理，4总监
     * @apiVersion 1.0.0
     */
    public function memberList($params) {
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }

        if (empty($params['userName'])) {
            $params['userName'] = '';
        }
        if (isset($params['isMember'])) {
            $where[] = ['a.isMember', '=', $params['isMember']];
        }
        if (!empty($params['userType'])) {
            $where[] = ['a.userType', '=', $params['userType']];
        } 
        $where[] = ['a.userName|a.userNo|a.loginName', 'like', '%'.$params['userName'].'%'];
        $where[] = ['a.auditStatus', '>', 0];
        // $where[] = ['a.userStatus', '>', 0];
        // $where[] = ['isMemberGoods', '=', 1];
        try {
            $data['list'] = $this->alias('a')
                // ->join('order c', 'a.userId = c.userId')
                ->leftJoin('user b', 'a.superiorNo = b.userNo')
                ->field('a.*, b.userName as superiorName')
                ->where($where)
                ->page($params['page'], $params['size'])
                ->order('userId', 'desc')
                ->select();
            $data['count'] = $this->alias('a')
                // ->join('order c', 'a.userId = c.userId')
                ->where($where)
                ->count();
            foreach($data['list'] as $item) {
                // $where1[] = ['userId', '=', $item['userId']];
                // $where[] = ['isMemberGoods', '=', 1];
                // $where1[] = ['userId', '=', $item['userId']];
                $order = db('order')->where(['userId' => $item['userId'], 'isMemberGoods' => 1])->field('orderStatus, orderNo, paymentTime')->select();
                $item['orderList'] = $order;
                // if ($item['isMember'] == 1) {
                //     $item['auditStatusName'] = '会员';
                // } else {
                //     $item['auditStatusName'] = '非会员';
                // }
                if ($item['auditStatus'] == 1) {
                    $item['auditStatusName'] = '待审核';
                } else if($item['auditStatus'] == 2){
                    $item['auditStatusName'] = '审核通过';
                } else {
                    $item['auditStatusName'] = '审核不通过';
                }
            } 
           
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} / 2.2 会员列表-审核
     * @apiName memberAudit
     * @apiGroup adminUserGroup
     * @apiParam {Number} userId 会员id
     * @apiParam {Number} auditStatus 审核状态：1待审核，2审核通过，3审核不通过
     * @apiVersion 1.0.0
     */
    public function memberAudit($params) {
        if ($params['auditStatus'] !== 1 || $params['auditStatus'] !== 2 || $params['auditStatus'] !== 3) {
            $this->error = '无效的auditStatus';
        }
        if ($params['auditStatus'] == 1) {
            $update = [
                'userType' => 1,
                'auditStatus' => $params['auditStatus'],
                'auditTime' => ''
            ];
        }
        if ($params['auditStatus'] == 2) {
            $update = [
                'userType' => 2,
                'auditStatus' => $params['auditStatus'],
                'auditTime' => time()
            ];
        }
        if ($params['auditStatus'] == 3) {
            $update = [
                'userType' => 1,
                'auditStatus' => $params['auditStatus']
            ];
        }
        try {
            
            $data = db('user')->where('userId', $params['userId'])->update($update);
            if($data) {
                return '操作成功';
            } else {
                return '操作失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} / 2.3 会员详情
     * @apiName memberInfo
     * @apiGroup adminUserGroup
     * @apiParam {Number} userId 会员id
     * @apiSuccess {Number} memberCount 会员个数
     * @apiSuccess {Number} managerCount 经理个数
     * @apiSuccess {Number} majordomoCount 总监个数
     * @apiSuccess {Object} commission 佣金
     * @apiSuccess {Object} .shop 商品佣金
     * @apiSuccess {Object} .invitation 邀请佣金
     * @apiSuccess {Object} .share 分享佣金
     * @apiSuccess {Object} .team 团队佣金
     * @apiVersion 1.0.0
     */
    public function memberInfo($params) {

        try {
            $data['info'] = Db::name('user')->where('userId', $params['userId'])->find();
            $commission = Db::name('commission')->where([['beneficiaryUserId', '=', $params['userId']], ['commissionStatus', 'IN' ,[1,2]]])->select();
            $data['commission'] = [
                'shop' => 0,
                'invitation' => 0,
                'share' => 0,
                'team' => 0,
                'count' => 0
            ];

            // 计算佣金类型
            foreach($commission as $item) {
                if ($item['commissionType'] == 1) {
                    $data['commission']['shop'] += $item['commissionMoney'];
                }
                if ($item['commissionType'] == 2) {
                    $data['commission']['invitation'] += $item['commissionMoney'];
                }
                if ($item['commissionType'] == 3) {
                    $data['commission']['share'] += $item['commissionMoney'];
                }
                if ($item['commissionType'] == 4) {
                    $data['commission']['team'] += $item['commissionMoney'];
                }
            }
            $data['commission']['count'] = $data['commission']['shop'] + $data['commission']['invitation'] + $data['commission']['share'] + $data['commission']['team'];

            $userList = Db::name('user')->where([
                ['userType', '>=', 2],
                ['superiorId', '=', $params['userId']],
                ['userId', '<>', $params['userId']],
            ])->select();
            $data['memberCount'] = 0;
            $data['managerCount'] = 0;
            $data['majordomoCount'] = 0;
            $memberCount = 0;
            $memberTeamCount = 0;
            $data['userList'] = [
                'name' => $data['info']['userName'],
                'userId' => $data['info']['userId']
            ];
            foreach($userList as $key => $item) {
                $data['userList']['children'][$key]['name'] = $item['userName'];
                $data['userList']['children'][$key]['userId'] = $item['userId'];
                if ($item['userType'] == 2) {
                    $memberCount += 1;
                }
                if ($item['userType'] == 3) {
                    $data['managerCount'] += 1;
                }
                if ($item['userType'] == 4) {
                    $data['majordomoCount'] += 1;
                }
                $userList1 = Db::name('user')->where([['superiorId', '=', $item['userId']],['userType', '=', 2]])->select();
                foreach($userList1 as $key1 => $item1) {
                    $data['userList']['children'][$key]['children'][$key1]['name'] = $item1['userName'];
                    $data['userList']['children'][$key]['children'][$key1]['userId'] = $item1['userId'];
                    if ($item1['userType'] == 2) {
                        $memberTeamCount += 1;
                    }
                }
            }

            $data['memberCount'] = $memberCount;
            $data['memberTeamCount'] = $memberTeamCount + $memberCount;

            
            if ($data) {
                return $data;
            } else {
                return '操作失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} / 2.4 会员下级详情
     * @apiName childrenMemberInfo
     * @apiGroup adminUserGroup
     * @apiParam {Number} userId 会员id
     * @apiVersion 1.0.0
     */
    public function childrenMemberInfo ($params) {
        try {
            $data['user'] = Db::name('user')->where('userId', $params['userChildrenId'])->find();
            $data['orderList'] = Db::name('order')->where([
                ['userId', '=', $params['userChildrenId']],
                ['orderStatus', '<>', 7]
                // ['b.beneficiaryUserId', '=', $params['userId']],
                // ['b.commissionStatus', 'in', [1,2]]
            ])->select();
            // ->leftJoin('commission b', 'a.orderId = b.orderId')
            // ->field('a.*, b.commissionMoney, b.commissionStatus, b.isSettlement, b.commissionType, b.beneficiaryUserId, b.orderId')->select();
            $data['commission'] = [
                'shop' => 0,
                'invitation' => 0,
                'share' => 0,
                'team' => 0,
                'count' => 0
            ];
            // 计算佣金类型
            foreach($data['orderList'] as &$item) {
                $commission = Db::name('commission')->where([
                    ['orderId', '=', $item['orderId']],
                    ['beneficiaryUserId', '=', $params['userId']],
                    ['commissionStatus', 'in', [1,2]]
                ])->find();
                $item['commissionMoney'] = $commission['commissionMoney'];
                if ($commission['commissionType'] == 1) {
                    $data['commission']['shop'] += $commission['commissionMoney'];
                }
                if ($commission['commissionType'] == 2) {
                    $data['commission']['invitation'] += $commission['commissionMoney'];
                }
                if ($commission['commissionType'] == 3) {
                    $data['commission']['share'] += $commission['commissionMoney'];
                }
                if ($commission['commissionType'] == 4) {
                    $data['commission']['team'] += $commission['commissionMoney'];
                }
            }
            $data['commission']['count'] = $data['commission']['shop'] + $data['commission']['invitation'] + $data['commission']['share'] + $data['commission']['team'];
            
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}