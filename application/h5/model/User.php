<?php  
namespace app\h5\model;  
use think\Db;
use app\h5\model\Common;
use think\Request;
use wechat\Jssdk;
use Naixiaoxin\ThinkWechat\Facade;

/**
 * @apiDefine h5LoginGroup h5-登录/注册/用户
 */
/**
 * @apiDefine h5UserGroup h5-用户
 */

 /**
 * @api {post} / 1. 用户表
 * @apiName user
 * @apiGroup h5LoginGroup
 * @apiSuccess {Number} userId 主键
 * @apiSuccess {String} loginName 账号
 * @apiSuccess {Number} userType 1普通，2会员，3经理，4总监
 * @apiSuccess {String} userName 用户名称
 * @apiSuccess {String} userPhone 用户手机
 * @apiSuccess {String} userPhoto 用户头像
 * @apiSuccess {String} superiorNo 用户上级编号
 * @apiSuccess {String} userNo 用户编号
 * @apiSuccess {String} lastTime 用户最后登录时间
 * @apiVersion 1.0.0
 */

class User extends Common {
    protected $request;

    public function __construct(Request $request)
    {
		$this->request = $request;
    }
    
    /**
     * @api {post} /h5/login 1.1 用户登录
     * @apiName login
     * @apiGroup h5LoginGroup
     * @apiParam {String} loginName 手机号
     * @apiParam {Number} loginSms 短信码
     * @apiSuccess {Boolean} isUser 是否是平台用户，为true登录，为false下一步操作
     * @apiSuccess {Object} userInfo 用户信息
     * @apiVersion 1.0.0
     */
    public function login($params) {
        $headerParams = $this->getHeaderParams();
        $map['loginName'] = $params['loginName'];
        try {
            $smsCode = db('sms')->where([
                ['phone', '=', $params['loginName']],
                ['overdueTime', '>', time()]
            ])->order('smsId', 'desc')->limit(1)->select();
            if (count($smsCode) == 0) {
                $this->error = '请获取验证码';
                return;
            }
            // if (time() > $smsCode[0]['overdueTime']) {
            //     $this->error = '短信已过期，请重新发送';
            //     return;
            // }
            if ($params['loginSms'] != $smsCode[0]['smsCode']) {
                $this->error = '短信验证码错误';
                return;
            }

            $userInfo = db('user')->where($map)->find();
            if (!$userInfo) {
                return [
                    'isUser' => false
                ];
            }
            
            if (!empty($params['code'])) { // 这里解决了，在app注册，不能取到openId，所以这里登录的时候，保存openid
                $jssdk = new Jssdk();
                $weixinInfo = $jssdk->getOpenid($params['code']);
                // return json_encode($weixinInfo);
                if (!empty($weixinInfo->errcode)) {
                    if ($weixinInfo->errcode == '40163') {
                        $this->error = 'code过期了，请重新授权';
                    } else {
                        $this->error = json_encode($weixinInfo);
                    }
                    return;
                }
                $app = Facade::officialAccount();
                $wxInfo = $app->user->get($weixinInfo->openid);
    
                if ($wxInfo['subscribe'] == 0) { // 未关注公众号
                    $wxUserInfo = $jssdk->userInfo([
                        'access_token' => $weixinInfo->access_token,
                        'openid' => $wxInfo['openid']
                    ]);
                    $wxInfo['nickname'] = $wxUserInfo['nickname'];
                    $wxInfo['headimgurl'] = $wxUserInfo['headimgurl'];
                }
            }
            
            
            $token = $this->request->token('__token__', 'jsjh_mall');
            $map = [
                // 'token' => $token,
                'lastTime' => time(),
                'userFrom' => $headerParams['from']
            ];
            if (!empty($wxInfo)) {
                // if ($wxInfo['subscribe'] == 1) { // 关注了
                $map['openid'] = $weixinInfo->openid;
            }

            db('user')->where('userId', $userInfo['userId'])->update($map);
            $add = [
                'token' => $token,
                'createTime' => time(),
                'isUse' => 1,
                'userId' => $userInfo['userId'],
                'userApp' => $headerParams['app'],
                'userFrom' => $headerParams['from']
            ];
            Db::name('user_token')->insert($add);

            // 判断大于5个移除
            $userCount = Db::name('user_token')->where([
                ['userId', '=' , $userInfo['userId']],
                ['isUse', '=', 1]
            ])->count();
            if($userCount > 5) {
                Db::name('user_token')->where('userId', $userInfo['userId'])->limit(1)->update(['isUse' => 0, 'logoutTime' => time()]);
            }

            $data['userInfo'] = [
                'loginName' => $userInfo['loginName'],
                'userType' => $userInfo['userType'],
                'userName' => $userInfo['userName'],
                'userNo' => $userInfo['userNo'],
                'userPhone' => $userInfo['userPhone'],
                'superiorNo' => $userInfo['superiorNo'],
                'weixinAccount' => $userInfo['weixinAccount'],
                'userPhoto' => $userInfo['userPhoto'],
                'token' => $token,
            ];
            $data['isUser'] = true;

        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
        
        return $data;
    }

    /**
     * @api {post} /h5/register 1.2 注册/绑定
     * @apiName register
     * @apiGroup h5LoginGroup
     * @apiParam {String} loginName 手机号
     * @apiParam {Number} userNo 用户编号，绑定对象的编号
     * @apiSuccess {Object} userInfo 用户信息
     * @apiVersion 1.0.0
     */
    public function register($params) {
        $viewFrom = $this->getViewFrom();
        if ($viewFrom == 1) {
            if (empty($params['code'])) {
                $this->error = '缺少code，没有授权';
                return;
            }
        }
        // 查询账号是否存在
        $userInfo = db('user')->where('loginName', $params['loginName'])->find();
        if ($userInfo) {
            $this->error = '该账号已经存在';
            return;
        }

        // 查询上级是否是会员
        $user = db('user')->where('userNo', $params['userNo'])->find();
        if (!$user) {
            $this->error = '该用户不存在，请重新填写';
            return;
        }
        if ($user['userType'] == 1) {
            $this->error = '该用户不是会员，请重新填写';
            return;
        }
        $ids = db('id')->find();
        $superior = db('user')->where('userNo', $params['userNo'])->find();
        $app = Facade::officialAccount();
        // $userInfo = $app->auth->session($params['code']);;
        // dump($userInfo);
        // return;
        if (!empty($params['code'])) {
            $jssdk = new Jssdk();
            $weixinInfo = $jssdk->getOpenid($params['code']);
            // return json_encode($weixinInfo);
            if (!empty($weixinInfo->errcode)) {
                if ($weixinInfo->errcode == '40163') {
                    $this->error = 'code过期了，请重新授权';
                } else {
                    $this->error = json_encode($weixinInfo);
                }
                return;
            }
            $app = Facade::officialAccount();
            $wxInfo = $app->user->get($weixinInfo->openid);

            if ($wxInfo['subscribe'] == 0) { // 未关注公众号
                $wxUserInfo = $jssdk->userInfo([
                    'access_token' => $weixinInfo->access_token,
                    'openid' => $wxInfo['openid']
                ]);
                $wxInfo['nickname'] = $wxUserInfo['nickname'];
                $wxInfo['headimgurl'] = $wxUserInfo['headimgurl'];
                /**
                 * array(9) {
                 *   ["openid"] => string(28) "oVi1Q5uzB5MJ0p7JScgY3AcPcc98"
                 *   ["nickname"] => string(3) "sky"
                 *   ["sex"] => int(1)
                 *   ["language"] => string(5) "zh_CN"
                 *   ["city"] => string(6) "温州"
                 *   ["province"] => string(6) "浙江"
                 *   ["country"] => string(6) "中国"
                 *   ["headimgurl"] => string(129) "http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTLVia94J5onHScuW9QN9JvxCBib283dmzqVYTgE5AVn7PMcgyt8vm3FE5MjsMcEvANh34cYukBnMmXw/132"
                 *   ["privilege"] => array(0) {
                 *   }
                 *   }
                 */
            }

            if ($wxInfo['subscribe'] == 1) { // 关注公众号

            }
            /** 
             * 没有关注公众号的时候，返回的值
             *  $wxInfo
             *   ["subscribe"] => int(0)
             *   ["openid"] => string(28) "oVi1Q5uzB5MJ0p7JScgY3AcPcc98"
             *   ["tagid_list"] => array(0) {
             *   }
             */
            /**
             * 关注公众号，房子的值
             * $wxInfo
             * array(16) {
             *   ["subscribe"] => int(1)
             *   ["openid"] => string(28) "oVi1Q5uzB5MJ0p7JScgY3AcPcc98"
             *   ["nickname"] => string(3) "sky"
             *   ["sex"] => int(1)
             *   ["language"] => string(5) "zh_CN"
             *   ["city"] => string(6) "温州"
             *   ["province"] => string(6) "浙江"
             *   ["country"] => string(6) "中国"
             *   ["headimgurl"] => string(134) "http://thirdwx.qlogo.cn/mmopen/ANr5hZFwPIklmzHUupT4PCh9icZNelasVj68dIqKBbx6r7cZhGRQlSw4eDqxfvxHpXbMfoR7sWYnZXbiavFpd5LUWic6DPbNQS4/132"
             *   ["subscribe_time"] => int(1539762370)
             *   ["remark"] => string(0) ""
             *   ["groupid"] => int(0)
             *   ["tagid_list"] => array(0) {
             *   }
             *   ["subscribe_scene"] => string(16) "ADD_SCENE_SEARCH"
             *   ["qr_scene"] => int(0)
             *   ["qr_scene_str"] => string(0) ""
             *   }
             */
            
            
        }
        $loginSecret = rand(1000,9999);
        $map = [
            'userNo' => $ids['userNo']+1,
            'userType' => 1,
            'superiorId' => (int)$superior['userId'],
            'superiorName' => $superior['userName'],
            'superiorNo' => (int)$params['userNo'],
            'loginName' => $params['loginName'],
            'loginPwd' => user_md5('123456'.$loginSecret),
            'loginSecret' => $loginSecret,
            'userName' => $params['loginName'],
            'userPhone' => $params['loginName'],
            'userPhoto' => 'http://api.mall.shaoky.com/images/user/default-photo.png',
            'createTime' => time(),
            'userFrom' => $viewFrom
            // 'unionid' => $weixinInfo->unionid
        ];
        // dump($wxInfo);
        if (!empty($wxInfo)) {
            // if ($wxInfo['subscribe'] == 1) { // 关注了
                $map['openid'] = $weixinInfo->openid;
                $map['userPhoto'] = $wxInfo['headimgurl'];
                $map['userName'] = $wxInfo['nickname'];
            // } else {

            // }
        }
        $update = [
            'userNo' => $ids['userNo'] + 1
        ];
        
        Db::startTrans();
        try {
            // 自增Id
            $userNo = Db::name('id')->where('id', 1)->update($update);
            // 添加用户数据
            $map['token'] = $this->request->token('__token__', 'jsjh_mall');
            $map['lastTime'] = time();
            $userId = Db::name('user')->insertGetId($map);
            if (!empty($wxInfo)) {
                if ($wxInfo['subscribe'] == 1) {
                    // 设置access_token
                    Db::name('user_access_token')->insert([
                        'userId' => $userId,
                        'accessToken' => $weixinInfo->access_token,
                        'refreshToken' => $weixinInfo->refresh_token,
                        'createTime' => time(),
                        'overdueTime' => strtotime(date("Y-m-d H:i:s", strtotime('+120 minute')))
                    ]);
                }
            }
            Db::commit();
            return [
                'userInfo' => $map
            ];
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e;
            return false;
        }
    }

    /**
     * @api {post} /h5/referee/list 1.3 推荐人列表
     * @apiName getRefereeList
     * @apiGroup h5LoginGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiSuccess {Array} list 见用户表
     * @apiSuccess {String} text 弹窗文字
     * @apiSuccess {Number} count 总条数
     * @apiVersion 1.0.0
     */
    public function getRefereeList($params) {
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        try {
            $where[] = ['userType', '>', 1];
            $where[] = ['userId', 'in', [1,2]];
            $data['list'] = Db::name('user')->where($where)
            ->field('userName, userNo, userPhoto')
            ->page($params['page'], $params['size'])
            ->select();
            $data['count'] = Db::name('user')->where($where)->count();
            $data['text'] = '选择推荐人，可获得一对一的优质导购服务，掌握平台最新最全的优惠信息；并能获得最丰富的商品素材，自购轻松省钱，分享轻松赚钱。';
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /h5/referee/info 1.4 推荐人详情
     * @apiName getRefereeInfo
     * @apiGroup h5LoginGroup
     * @apiParam {Number} userNo 推荐人编号
     * @apiVersion 1.0.0
     */
    public function getRefereeInfo($params) {
        if (empty($params['userNo'])) {
            $this->error = '推荐人编号不能为空';
            return;
        }
        $where[] = ['userNo', '=', $params['userNo']];
        $where[] = ['userType', '>', 1];
        try {
            $data['info'] = db('user')->where($where)->field('userNo, userName, userPhoto, superiorNo')->find();
            if (!$data['info']) {
                $this->error = '该推荐人不存在';
                return;
            }
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /h5/autoLogin 1.5 自动登录
     * @apiName autoLogin
     * @apiGroup h5LoginGroup
     * @apiParam {Number} code 
     * @apiSuccess {Number} isAutoLogin 是否自动登录，1是，0不是
     * @apiSuccess {Object} userInfo 用户信息
     * @apiVersion 1.0.0
     */
    public function autoLogin($params) {
        if (empty($params['code'])) {
            $this->error = 'code不能为空';
            return;
        }

        // $where[] = ['userNo', '=', $params['userNo']];
        // $where[] = ['userType', '>', 1];
        try {
            $jssdk = new Jssdk();
            $weixinInfo = $jssdk->getOpenid($params['code']);
            $userInfo= db('user')->where('openid', $weixinInfo->openid)->find();
            if (!$userInfo) {
                return [
                    'isAutoLogin' => 0
                ];
            }
            $token = $this->request->token('__token__', 'jsjh_mall');
            db('user')->where('userId', $userInfo['userId'])->update(['token' => $token, 'lastTime' => time()]);

            $data['userInfo'] = [
                'loginName' => $userInfo['loginName'],
                'userType' => $userInfo['userType'],
                'userName' => $userInfo['userName'],
                'userNo' => $userInfo['userNo'],
                'userPhone' => $userInfo['userPhone'],
                'superiorNo' => $userInfo['superiorNo'],
                'weixinAccount' => $userInfo['weixinAccount'],
                'userPhoto' => $userInfo['userPhoto'],
                'token' => $token
            ];
            $data['isAutoLogin'] = 1;
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /app/passwordLogin 1.6 用户密码登录
     * @apiName passwordLogin
     * @apiGroup h5LoginGroup
     * @apiParam {String} loginName 手机号
     * @apiParam {String} loginPwd 密码
     * @apiSuccess {Boolean} isUser 用户是否存在，不存在返回false
     * @apiSuccess {String} message 提示信息
     * @apiSuccess {Object} userInfo 用户信息，用户成功登录，返回userInfo
     * @apiVersion 1.0.0
     */
    public function passwordLogin($params) {
        // $viewFrom = $this->getViewFrom();
        $headerParams = $this->getHeaderParams();
        $map['loginName'] = $params['loginName'];
        try {
            $userInfo = db('user')->where($map)->find();
            if (!$userInfo) {
                return [
                    'message' => '账号不存在',
                    'isUser' => false
                ];
            }
            if ($userInfo['loginPwd'] !== user_md5($params['loginPwd'].$userInfo['loginSecret'])) {
                $this->error = '密码错误';
                return false;
            }
           
            $token = $this->request->token('__token__', 'jsjh_mall');
            db('user')->where('userId', $userInfo['userId'])->update(['token' => $token, 'lastTime' => time(), 'userFrom' => $headerParams['from']]);

            $add = [
                'token' => $token,
                'createTime' => time(),
                'isUse' => 1,
                'userId' => $userInfo['userId'],
                'userApp' => $headerParams['app'],
                'userFrom' => $headerParams['from']
            ];
            Db::name('user_token')->insert($add);

            // 判断大于5个移除
            $userCount = Db::name('user_token')->where([
                ['userId', '=' , $userInfo['userId']],
                ['isUse', '=', 1]
            ])->count();
            if($userCount > 5) {
                Db::name('user_token')->where('userId', $userInfo['userId'])->limit(1)->update(['isUse' => 0, 'logoutTime' => time()]);
            }

            $data['userInfo'] = [
                'loginName' => $userInfo['loginName'],
                'userType' => $userInfo['userType'],
                'userName' => $userInfo['userName'],
                'userNo' => $userInfo['userNo'],
                'userPhone' => $userInfo['userPhone'],
                'superiorNo' => $userInfo['superiorNo'],
                'weixinAccount' => $userInfo['weixinAccount'],
                'userPhoto' => $userInfo['userPhoto'],
                'token' => $token
            ];
            $data['isUser'] = true;

        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
        
        return $data;
    }

    public function validationToken($token) {
        if (empty($token)) {
            $this->error = '请登录账号';
            return;
        }
        $data = db('user_token')->where([
            ['token', '=', $token],
            ['isUse', '=', 1]
        ])->find();
        if ($data['token'] == $token) {
            return $data;
        } else {
            $this->error = '登录超时，请重新登录';
        }
    }
    /**
     * @api {post} /h5/user/info 2.1 获取用户信息
     * @apiName getUserInfoData
     * @apiGroup h5LoginGroup
     * @apiHeader {String} Authorization token
     * @apiParam {String} [userName] 用户名称
     * @apiParam {String} [userPhoto] 用户头像
     * @apiParam {String} [weixinAccount] 微信账号
     * @apiVersion 1.0.0
     */
    public function getUserInfoData() {
        // $userId = $this->getUserId();
        // try {
            // $data['userInfo'] = db('user')->where('userId', $userId)->field('loginPwd,loginSecret,token', true)->find();
            // if ($data['userInfo']) {
            //     $data['userInfo']['refundLinkman'] = '李先生';
            //     $data['userInfo']['refundAddress'] = '浙江省温州市方工路15号院内';
            //     $data['userInfo']['refundTel'] = '13587872587';
            //     return $data;
            // } else {
            //     $this->error = '获取用户信息失败';
            // }
        // } catch (\Exception $e) {
        //     $this->error = $e->getMessage();
        //     return false;
        // }

        $data['userInfo'] = $this->getUserInfo();
        $shop = Db::name('shop')->where('userId',$data['userInfo']['userId'])->find();
        if($shop){
            $isShop = 1;
        }else{
            $isShop = 0;
        }
        $data['userInfo']['isShop'] = $isShop;
        return $data;
        
    }
    /**
     * @api {post} /h5/user/update 2.2 更新用户信息
     * @apiName updateUserInfo
     * @apiGroup h5LoginGroup
     * @apiHeader {String} Authorization token
     * @apiParam {String} [userName] 用户名称
     * @apiParam {String} [userPhoto] 用户头像
     * @apiParam {String} [weixinAccount] 微信账号
     * @apiVersion 1.0.0
     */
    public function updateUserInfo($params) {
        $userId = $this->getUserId();
        if (!empty($params['userName'])) {
            $form['userName'] = $params['userName'];
        }

        if (!empty($params['userPhoto'])) {
            $form['userPhoto'] = $params['userPhoto'];
        }

        if (!empty($params['weixinAccount'])) {
            $form['weixinAccount'] = $params['weixinAccount'];
        }

        try {
            $data = db('user')->where('userId', $userId)->update($form);
            if ($data) {
                return [
                    'message' => '修改成功',
                    'info' => $params
                ];
            } else {
                $this->error = '修改用户信息失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        
    }

    /**
     * @api {post} /h5/password/update 2.3 更新用户密码
     * @apiName updatePassword
     * @apiGroup h5LoginGroup
     * @apiHeader {String} Authorization token
     * @apiParam {String} loginPwd 旧密码
     * @apiParam {String} newLoginPwd 新密码，假如需要用户输入2次新密码，客户端本地验证
     * @apiVersion 1.0.0
     */
    public function updatePassword($params) {
        $userId = $this->getUserId();
        if (empty($params['loginPwd'])) {
            $this->error = '请输入旧密码';
            return;
        }
        if (empty($params['newLoginPwd'])) {
            $this->error = '请输入新密码';
            return;
        }
        Db::startTrans();
        try {
            $result = Db::name('user')->where('userId', $userId)->find();
            if ($result['loginPwd'] !== user_md5($params['loginPwd'].$result['loginSecret'])) {
                $this->error = '密码错误';
                return false;
            }
            $loginSecret = rand(1000,9999);
            $loginPwd = user_md5($params['newLoginPwd'].$loginSecret);
            $data = Db::name('user')->where('userId', $userId)->update(['loginPwd' => $loginPwd, 'loginSecret' => $loginSecret]);
            Db::commit();
            if ($data == 1) {
                return [
                    'message' => '修改成功'
                ];
            } else {
                $this->error = '修改失败';
                return;
            }
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        
    }

    /**
     * @api {post} /h5/user/index 2.4 用户中心
     * @apiName getUserIndex
     * @apiGroup h5LoginGroup
     * @apiSuccess {Object} orderNum 个人中心，订单数量的显示
     * @apiSuccess {Number} .payment 待付款
     * @apiSuccess {Number} .delivery 待发货
     * @apiSuccess {Number} .receive 待收货
     * @apiSuccess {Number} .finish 交易完成
     * @apiSuccess {Number} .refund 退款/售后
     * @apiSuccess {Number} serviceTel 服务热线
     * @apiVersion 1.0.0
     */
    public function getUserIndex($params) {
        $user = $this->getUserInfo();
        try {
            $order = Db::name('order')->where('userId', $user['userId'])->field('orderStatus')->select();
            $data['orderNum'] = [
                'payment' => 0,
                'delivery' => 0,
                'receive' => 0,
                'finish' => 0,
                'refund' => 0,
            ];
            foreach($order as $item) {
                switch ($item['orderStatus']){
                    case 1 :{
                        $data['orderNum']['payment']++;
                    }
                    case 2 :{
                        $data['orderNum']['delivery']++;
                    }
                    case 3 :{
                        $data['orderNum']['receive']++;
                    }
                    case 4 :{
                        $data['orderNum']['finish']++;
                    }
                    case 5 :{
                        $data['orderNum']['refund']++;
                    }
                    case 6 :{
                        $data['orderNum']['refund']++;
                    }
                }
            }
            $web = Db::name('web_config')->find();
            $data['serviceTel'] = $web['servicePhone'];
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

     /**
     * @api {post} /h5/user/logout 2.5 用户退出登录
     * @apiName logout
     * @apiGroup h5LoginGroup
     * @apiVersion 1.0.0
     */
    public function setLogout($params) {
        $header = $this->getHeaderParams();
        try {
            $data = Db::name('user_token')->where('token', $header['authorization'])->update(['isUse' => 0, 'logoutTime' => time()]);
            if ($data == 1) {
                return [
                    'message' => '操作成功'
                ];
            } else {
                $this->error = '操作失败';
            }
            
        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
    }

}