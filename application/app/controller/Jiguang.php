<?php 
// 用户
namespace app\app\controller;
use think\Controller;
use wechat\Jssdk;
use jiguang\Jgsdk;
// use think\Request;
// use think\facade\Env;



class Jiguang extends Controller {
    /**组装需要的参数
    $receive = 'all';//全部
    $receive = array('tag'=>array('2401','2588','9527'));//标签
    $receive = array('alias'=>array('93d78b73611d886a74*****88497f501'));//别名
    $content = '这是一个测试的推送数据....测试....Hello World...';
    $m_type = 'http';
    $m_txt = 'http://www.iqujing.com/';
    $m_time = '600';        //离线保留时间
     **/
    //调用推送方法
    public function send(){
 
        $push = new Jgsdk();
        $m_type = 'https';//推送附加字段的类型
        $m_txt = '';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
        $m_time = '86400';//离线保留时间
        $receive = "all";
        $content = '这是什么那';
        $message="";//存储推送状态
        $result = $push->push($receive,$content,$m_type,$m_txt,$m_time);
        if($result){
            $res_arr = json_decode($result, true);
            if(isset($res_arr['error'])){                       //如果返回了error则证明失败
                echo $res_arr['error']['message'];          //错误信息
                $error_code=$res_arr['error']['code'];             //错误码
                switch ($error_code) {
                    case 200:
                        $message= '发送成功！';
                        break;
                    case 1000:
                        $message= '失败(系统内部错误)';
                        break;
                    case 1001:
                        $message = '失败(只支持 HTTP Post 方法，不支持 Get 方法)';
                        break;
                    case 1002:
                        $message= '失败(缺少了必须的参数)';
                        break;
                    case 1003:
                        $message= '失败(参数值不合法)';
                        break;
                    case 1004:
                        $message= '失败(验证失败)';
                        break;
                    case 1005:
                        $message= '失败(消息体太大)';
                        break;
                    case 1008:
                        $message= '失败(appkey参数非法)';
                        break;
                    case 1020:
                        $message= '失败(只支持 HTTPS 请求)';
                        break;
                    case 1030:
                        $message= '失败(内部服务超时)';
                        break;
                    default:
                        $message= '失败(返回其他状态，目前不清楚额，请联系开发人员！)';
                        break;
                }
            }else{
                $message="发送成功！";
            }
        }else{      //接口调用失败或无响应
            $message='接口调用失败或无响应';
        }
        echo  "<script>alert('推送信息:{$message}')</script>";

    }

}