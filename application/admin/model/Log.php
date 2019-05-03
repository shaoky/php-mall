<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/23
 * Time: 16:22
 */

namespace app\admin\model;

/**
 * @apiDefine appLogGroup admin-错误日志模块
 */
class Log extends Common
{
    /**
     * @api {post} /admin/log/getdir 1. 错误日志目录
     * @apiName getDir
     * @apiGroup appLogGroup
     * @apiParam {String} dirs 日期文件夹名称（默认不传）
     * @apiSuccess {String} dir 文件夹/文件名称
     * @apiSuccess {String} path 文件夹路径
     * @apiVersion 1.0.0
     */
    protected $LinuxPath = SVN_ROOT."/runtime/logs/";
    protected $WinPath = SVN_ROOT."\\runtime\\logs\\";
    public function getDir($params){
//        $path = $this->path;
        if (PHP_OS == "Linux"){
            $path = $this->LinuxPath;
//            $path = $paths.$dir."/".$files;
        }else{
//            $paths = SVN_ROOT."\\logs\\";
            $path = $this->WinPath;
        }
//        $path = SVN_ROOT."/logs";
        if ($params->has('dirs')){
            $current = $path.$params->post('dirs');
        }else{
            $current = $path;
        }
        $data['list'] = $this->getDirs($current);
//        var_dump($data);
        //增加倒序排序
        rsort($data['list']);
        $dataTmp = array();
        foreach ($data['list'] as $key1 => $vo1) {
            if ($vo1['dir'] == '../' || $vo1['dir'] == '/..' || $vo1['dir'] == '..') {
                $dataTmp[0] = $vo1;
            } else {
                if (strstr($vo1['dir'],"error")){
                    $dataTmp[$key1 + 1] = $vo1;
                }else{
                    continue;
                }
            }
        }
        ksort($dataTmp);
        $data['list'] = $dataTmp;
        return $data;
    }

//      列出目录下所有目录和文件
//      @param $dir '传入绝对路径'
//      @return array '返回目录文件'

    private function getDirs($dir) {

        $handle = opendir($dir);
        $dirArr = array();

        if (!$handle) {
            $dirArr[0]['dir'] = '../';
            $dirArr[0]['path'] = $dir;
        } else {
            $i = 0;
            while (false !== ($file = readdir($handle))) {
                if ($file != "." || $file != "..") {
                    $dirArr[$i]['dir'] = $file;
                    $dirArr[$i]['path'] = $dir;
                    $i++;
                }
            }
        }
        closedir($handle);
        asort($dirArr);
        return $dirArr;
    }
    /**
     * @api {post} /admin/log/getlog 2. 错误日志列表
     * @apiName getLog
     * @apiGroup appLogGroup
     * @apiParam {String} dir 日期文件夹名称（默认不传）
     * @apiParam {String} files 日志文件名称
     * @apiSuccess {String} ip 当前用户ip
     * @apiSuccess {String} method 请求方式
     * @apiSuccess {String} url 访问URL
     * @apiSuccess {String} timestamp 时间格式
     * @apiSuccess {String} error 错误内容
     * @apiVersion 1.0.0
     */
    public function getLog($params){
//        var_dump();die;
        $files = $params->post('files');
        $dir = $params->post('dir');

//        $date = date("Y-m-d");
        if (PHP_OS == "Linux"){
            $paths = $this->LinuxPath;
//            $paths = SVN_ROOT."/logs/";
            $path = $paths.$dir."/".$files;
        }else{
//            $paths = SVN_ROOT."\\logs\\";
            $paths = $this->WinPath;
            $path = $paths.$dir."\\".$files;
        }
//        var_dump($path);
        if (!@fopen($path,'r')){
            $this->error = "文件不存在！";
            return false;
        }
//        var_dump($path);
        $res = file_get_contents($path);
//        var_dump($res);die;
        $res = ltrim($res,"---------------------------------------------------------------");
//        $res = substr($res,0,strlen($res)-3);
        $a = explode("---------------------------------------------------------------",$res);
//        var_dump($a[0]);
//        var_dump(count($a));die;
//        var_dump($res);die;
        $obj = array();
        for($b=0;$b<count($a);$b++){
//            $c = explode(" ",$a[$b]);
//            var_dump($c);
//            echo "<br>";
             /*   for($q=0;$q<count($c);$q++){
                    $obj['list'][$b]['timestamp'] = $c[1];
                    $obj['list'][$b]['ip'] = $c[3];
                    $obj['list'][$b]['method'] = $c[4];
                    $obj['list'][$b]['url'] = $c[5];
                    $obj['list'][$b]['error'] = $c[8].$c[9];
                }*/
            $time = substr($a[$b],3,26);
            $obj['list'][$b]['timestamp'] = $time;
            $ip = substr($a[$b],32,12);
            $obj['list'][$b]['ip'] = $ip;
            $obj['list'][$b]['method'] = substr($a[$b],45,4);
//            $obj['list'][$b]['host'] = substr($res,42,4);
//            $obj['list'][$b]['url'] = substr($a[$b],42,4);
            $obj['list'][$b]['url'] = $this->cut($obj['list'][$b]['method'],'[ error ]',$a[$b]);
//            $obj['list'][$b]['error'] = $this->cut('[ error ]',']',$a[$b]);
            $obj['list'][$b]['error'] = substr($a[$b],strpos($a[$b],' [ error ] ')+1);

//            $times = str_replace(" " , "&nbsp" , $time) ;
//            $times = ltrim($times,'[');
//            var_dump($time);die;
//            var_dump($a[$b]);
//            if(!strstr($a[$b], '{')){
//                $a[$b] = "{\"".$a[$b];
//            }
//            if(!strstr($a[$b], '}')){
//                $a[$b] = $a[$b]."}";
//            }
//            if(!strstr($a[$b], '\"}')){
//                $a[$b] = $a[$b]."\"}";
//            }

//            $a[$b] = "{\"".$a[$b]."\"}";
//            $obj['list'][$b] = json_decode($a[$b],true);
        }
//        die;
//        var_dump($c);die;
        return $obj;
    }
    public function cut($begin,$end,$str){
        $b = mb_strpos($str,$begin) + mb_strlen($begin);
        $e = mb_strpos($str,$end) - $b;

        return mb_substr($str,$b,$e);
    }
}