<?php

use Common\ORG\Util\Logger;
use think\Db;
/**
 * 获取数据字典
 * @param string $key //键值，方便查找数据
 * @param string $fileName //字典文件名 目录Common/Dict/
 * @return mixed
 */
function dict($key = '', $fileName = 'Setting')
{
    static $_dictFileCache = array();
    $file = ADMIN_PATH . 'Common' . DS . 'Dict' . DS . $fileName . '.php';
    if (!file_exists($file)) {
        unset($_dictFileCache);
        return null;
    }
    if (!$key && !empty($_dictFileCache))
        return $_dictFileCache;
    if ($key && isset($_dictFileCache[$key]))
        return $_dictFileCache[$key];
    $data = require_once $file;
    $_dictFileCache = $data;
    return $key ? $data[$key] : $data;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pk id主键
 * @param string $pid parent标记字段
 * @param string $child level标记字段
 * @return array
 * @author ycj <1518140867@qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'parentid', $child = 'children', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param int $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param bool $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = false)
{
    return Org\Util\String::msubstr($str, $start, $length, $charset, $suffix);
}

/**
 * 检测输入的验证码是否正确
 * @param string $code 为用户输入的验证码字符串
 * @param string $id 其他参数
 * @return bool
 */
function check_verify($code, $id = '')
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 对用户的密码进行加密
 * @param string $password
 * @param string $encrypt //传入加密串，在修改密码时做认证
 * @return array/string
 */
function password($password, $encrypt = '')
{
    $pwd = array();
    $pwd['encrypt'] = $encrypt ? $encrypt : getNonceStr(6);
    $pwd['password'] = md5(md5(trim($password)) . $pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 解析多行sql语句转换成数组
 * @param string $sql
 * @return array
 */
function sql_split($sql)
{
    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$num] .= $query;
        }
        $num++;
    }
    return ($ret);
}

/**
 * 格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++)
        $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 取得文件扩展
 * @param string $filename 文件名
 * @return string
 */
function file_ext($filename)
{
    return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}

/**
 * 文件是否存在
 * @param string $filename 文件名
 * @param string $type 其他参数
 * @return boolean
 */
function file_exist($filename, $type = '')
{
    switch (strtoupper(C('FILE_UPLOAD_TYPE'))) {
        case 'SAE':
            $arr = explode('/', ltrim($filename, './'));
            $domain = array_shift($arr);
            $filePath = implode('/', $arr);
            $s = new SaeStorage();
            return $s->fileExists($domain, $filePath);
            break;

        case 'FTP':
            $storage = new \Common\Plugin\Ftp();
            return $storage->has($filename);
            break;

        default:
            return \Think\Storage::has($filename, $type);
    }
}

/**
 * 文件内容读取
 * @param string $filename 文件名
 * @param string $type 其他参数
 * @return bool
 */
function file_read($filename, $type = '')
{
    switch (strtoupper(C('FILE_UPLOAD_TYPE'))) {
        case 'SAE':
            $arr = explode('/', ltrim($filename, './'));
            $domain = array_shift($arr);
            $filePath = implode('/', $arr);
            $s = new SaeStorage();
            return $s->read($domain, $filePath);
            break;

        case 'FTP':
            $storage = new \Common\Plugin\Ftp();
            return $storage->read($filename);
            break;

        default:
            return \Think\Storage::read($filename, $type);
    }
}

/**
 * 文件写入
 * @param string $filename 文件名
 * @param string $content 文件内容
 * @param string $type 其他参数
 * @return bool
 */
function file_write($filename, $content, $type = '')
{
    switch (strtoupper(C('FILE_UPLOAD_TYPE'))) {
        case 'SAE':
            $s = new SaeStorage();
            $arr = explode('/', ltrim($filename, './'));
            $domain = array_shift($arr);
            $save_path = implode('/', $arr);
            return $s->write($domain, $save_path, $content);
            break;

        case 'FTP':
            $storage = new \Common\Plugin\Ftp();
            return $storage->put($filename, $content);
            break;

        default:
            return \Think\Storage::put($filename, $content, $type);
    }
}

/**
 * 文件删除
 * @param string $filename 文件名
 * @param string $type 其他参数
 * @return bool
 */
function file_delete($filename, $type = '')
{
    switch (strtoupper(C('FILE_UPLOAD_TYPE'))) {
        case 'SAE':
            $arr = explode('/', ltrim($filename, './'));
            $domain = array_shift($arr);
            $filePath = implode('/', $arr);
            $s = new SaeStorage();
            return $s->delete($domain, $filePath);
            break;

        case 'FTP':
            $storage = new \Common\Plugin\Ftp();
            return $storage->unlink($filename);
            break;

        default:
            return \Think\Storage::unlink($filename, $type);
    }
}

/**
 * 获取文件URL
 * @param string $filename 文件名
 * @return string
 */
function file_path_parse($filename)
{
    $config = C('TMPL_PARSE_STRING');
    return str_ireplace(UPLOAD_PATH, '', $filename);
}

/**
 * 验证远程链接地址是否正确
 * @param string $url
 * @return bool
 */
function file_exist_remote($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_REFERER, $url); //伪造来路
    curl_setopt($curl, CURLOPT_USERAGENT, 'Alexa (IA Archiver)');
    curl_setopt($curl, CURLOPT_NOBODY, true);
    $result = curl_exec($curl);
    $found = false;
    if ($result !== false) {
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($statusCode == 200)
            $found = true;
    }
    curl_close($curl);
    return $found;
}

/**
 * 远程文件内容读取
 * @param string $url
 * @return string
 */
function file_read_remote($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_REFERER, $url); //伪造来路
    curl_setopt($curl, CURLOPT_USERAGENT, 'Alexa (IA Archiver)');
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_NOBODY, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * 文件名加后缀
 * @param string $string
 * @param string $subfix
 * @return string
 */
function file_subfix($string, $subfix = '')
{
    return preg_replace("/(\.\w+)$/", "{$subfix}\\1", $string);
}

/**
 * 发送邮件
 * @param string $to 收件人
 * @param string $subject 主题
 * @param string $body 内容
 * @param array $config
 * @return bool
 */
function send_email($to, $subject, $body, $config = array())
{
    $email = new \Common\Plugin\Email($config);
    $email->send($to, $subject, $body);
    return $email->result;
}

/**
 * 生成签名
 * @param array $param
 * @return string
 */
function sign($param = array())
{
    return md5(base64_encode(hash_hmac('sha1', http_build_query($param), C('API_SIGN'), true)));
}

/**
 * xml转数组
 * @param string $xml
 * @param bool $isFile
 * @return null|array
 */
function xml2array($xml, $isFile = false)
{
    if ($isFile && file_exist($xml))
        $xml = file_read($xml);
    $xml = @simplexml_load_string($xml);

    if (is_object($xml)) {
        $xml = json_encode($xml);
        $xml = @json_decode($xml, true);
    }
    if (!is_array($xml))
        return null;

    return $xml;
}


/**
 * 扫描目录所有文件，并生成treegrid数据
 * @param string $path 目录
 * @param string $filter 过滤文件名
 * @return array
 */
function scan_dir($path, $filter = SITE_DIR)
{
    $result = array();
    $path = realpath($path);

    $path = str_replace(array('/', '\\'), DS, $path);
    $filter = str_replace(array('/', '\\'), DS, $filter);

    $list = glob($path . DS . '*');

    foreach ($list as $key => $filename) {
        $result[$key]['path'] = str_replace($filter, '', $filename);
        $result[$key]['name'] = basename($filename);
        $result[$key]['mtime'] = date('Y-m-d H:i:s', filemtime($filename));

        if (is_dir($filename)) {
            $result[$key]['type'] = 'dir';
            $result[$key]['size'] = '-';
        } else {
            $result[$key]['type'] = 'file';
            $result[$key]['size'] = format_bytes(filesize($filename), ' ');
        }
    }
    return $result;
}

/**
 * 上传目录列表
 * @param string $path 目录名
 * @return array
 */
function file_list_upload($path)
{
    $config = C('TMPL_PARSE_STRING');
    switch (strtoupper(C('FILE_UPLOAD_TYPE'))) {
        case 'SAE':
            $path = str_replace(DS, '/', rtrim($path, DS));
            $arr = explode('/', ltrim($path, './'));
            $domain = array_shift($arr);
            $filePath = implode('/', $arr);
            $s = new SaeStorage();
            $list = $s->getListByPath($domain, $filePath);
            $res = array();
            while (isset($list['dirNum']) && $list['dirNum']) {
                $list['dirNum']--;
                array_push($res, array(
                    'type' => 'dir',
                    'name' => $list['dirs'][$list['dirNum']]['name'],
                    'path' => ltrim($list['dirs'][$list['dirNum']]['fullName'], 'upload/'),
                    'size' => '-',
                    'mtime' => '-',
                    'url' => '#',
                ));
            }
            while (isset($list['fileNum']) && $list['fileNum']) {
                $list['fileNum']--;
                array_push($res, array(
                    'type' => 'file',
                    'name' => $list['files'][$list['fileNum']]['Name'],
                    'path' => ltrim($list['files'][$list['fileNum']]['fullName'], 'upload/'),
                    'size' => format_bytes($list['files'][$list['fileNum']]['length'], ' '),
                    'mtime' => date('Y-m-d H:i:s', $list['files'][$list['fileNum']]['uploadTime']),
                    'url' => ltrim($list['files'][$list['fileNum']]['fullName'], 'upload/'),
                ));
            }
            return $res;
            break;

        case 'FTP':
            $storage = new \Common\Plugin\Ftp();
            $list = $storage->ls($path);
            foreach ($list as &$item) {
                $item['path'] = ltrim($item['path'], UPLOAD_PATH);
                $item['url'] = str_replace('\\', '/', $item['path']);
            }
            return $list;
            break;

        default:
            $path = realpath($path);
            $path = str_replace(array('/', '\\'), DS, $path);
            $list = glob($path . DS . '*');
            $res = array();
            foreach ($list as $key => $filename) {
                array_push($res, array(
                    'type' => (is_dir($filename) ? 'dir' : 'file'),
                    'name' => basename($filename),
                    'path' => ltrim(str_replace(realpath(UPLOAD_PATH), '', $filename), DS),
                    'size' => format_bytes(filesize($filename), ' '),
                    'mtime' => date('Y-m-d H:i:s', filemtime($filename)),
                    'url' => ltrim(str_replace(array(realpath(UPLOAD_PATH), '\\'), array('', '/'), $filename), '/'),
                ));
            }
            return $res;
    }
}


/**
 * 生成弹出层上传链接
 * @param $callback
 * @param string $ext
 * @return string
 */
function url_upload($callback, $ext = 'jpg|jpeg|png|gif|bmp')
{
    $query = array('callback' => $callback, 'ext' => $ext);
    $query['sign'] = sign($query);
    return U('Storage/public_dialog', $query);
}

/**
 * @info 管理员操作日志记录
 * @return mixed
 */
 function adminLog($info,$action='',$time)
{

//     控制器名称
     $controller = Request::instance()->controller();
//    获取方法名
     $action=request()->action();
    if (session('userid')) {
        $add['userid'] = session('userid');
        $add['username'] = session('username');
        $add['ip'] = get_client_ip(0, true);
        $add['log_url'] = $controller."/".$action;
        $add['class'] = $controller;
        $add['action'] = $action;
        $add['method'] = $_SERVER['REQUEST_METHOD'];
//        $add['log_info'] = adminGetControllerNote($controller, $action);
        $add['log_info'] = $info;
        $add['time'] = date('Y-m-d H:i:s');
        $add['input'] = adminRequestMethodParam();
        $add['usetime'] = microtime(time())-$time;
        $add['token'] = session('token');
        $add['from'] = "后台";

        return Db::name('admin_log')
            ->data($add)
            ->insert();
//        return D('AdminLog')->add($add);
    }
}

/**
 * 根据访问类型获取请求参数
 * @return array    返回POST/GET请求参数
 */
function adminRequestMethodParam()
{
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
    if ($REQUEST_METHOD == 'POST') {
        $ret = $_POST;
    } else if ($REQUEST_METHOD == 'GET') {
        $ret = $_GET;
    }
    if (isset($ret['password'])) {
        $ret['password'] = md5(sha1($ret['password'] . time() . randString()));
    }
    $ret = empty($ret) ? '' : json_encode($ret, JSON_UNESCAPED_UNICODE);
    return $ret;
}

/**
 * 获取函数的注释  请在注释中使用@info
 * @param $controller   控制器名称
 * @param $action       函数名
 * @return string
 */
function adminGetControllerNote($controller, $action)
{
    $annotation = '';
    try {
        $desc = 'Admin\Controller\\' . $controller . 'Controller';
        $func = new \ReflectionMethod($desc, $action);
        $annotation = $func->getDocComment();
        preg_match_all('/@info(.*?)\n/', $annotation, $annotation);
        if ($annotation && $annotation[1]) {
            $annotation = trim($annotation[1][0]);
        }
    } catch (\Exception $e) {
        Logger::error($e);
    }
    return $annotation;
}
/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
// function get_client_ip($type = 0) {
//     $type       =  $type ? 1 : 0;
//     static $ip  =   NULL;
//     if ($ip !== NULL) return $ip[$type];
//     if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//         $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
//         $pos    =   array_search('unknown',$arr);
//         if(false !== $pos) unset($arr[$pos]);
//         $ip     =   trim($arr[0]);
//     }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
//         $ip     =   $_SERVER['HTTP_CLIENT_IP'];
//     }elseif (isset($_SERVER['REMOTE_ADDR'])) {
//         $ip     =   $_SERVER['REMOTE_ADDR'];
//     }
//     // IP地址合法验证
//     $long = sprintf("%u",ip2long($ip));
//     $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
//     return $ip[$type];
// }