<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Db;
use think\Cache;
use think\Request;
/**

 * 制作二维码图片

 * @return [type] [description]

 */

/*

* png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 4, $margin = 4, $saveandprint=false, $back_color = 0xFFFFFF, $fore_color = 0x000000)
* 参数说明:
* $text 就是url参数
* $outfile 默认否，不生成文件，只返回二维码图片，否则需要给出保存路径
* $level 二维码容错率，默认L(7%)、M(15%)、Q(25%)、H(30%)
* $size 二维码图片大小，默认4
* $margin 二维码空白区域大小
* $saveabdprint 二维码保存并显示，$outfile必须传路径
* $back_color 背景颜色
* $fore_color 绘制二维码的颜色
* tip:颜色必须传16进制的色值，并把“#”替换为“0x”; 如 #FFFFFF => 0xFFFFFF
*/
function qrcode($url) {

    //加载第三方类库
    vendor('phpqrcode.phpqrcode');
    $size=4;    //图片大小
    $errorCorrectionLevel = "Q"; // 容错级别：L、M、Q、H
    $matrixPointSize = "8"; // 点的大小：1到10
    //实例化
    $qr = new \QRcode();
    //会清除缓冲区的内容，并将缓冲区关闭，但不会输出内容。
    ob_end_clean();
    //输入二维码
    $qr::png($url, false, $errorCorrectionLevel, $matrixPointSize);
}


/*
 * curl请求
 * @param url 请求接口url
 * @param data 请求post传输数据
 * @return output 执行结果
 */
function curl_post($url,$data){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (! empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    //var_dump(curl_error($curl));
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/*
 * curl请求
 * @param url 请求接口url GET方式
 * @return output 执行结果
 */
function curl_get($url,$data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/*
 * curl 请求
 * @param url 请求接口url POST，json方式
 * @param json 提交的json字符串
 * @return output 执行结果
 */
function curl_json($url,$json){
    $data_string = $json;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: " . strlen($data_string))
    );
    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return array($return_code, $return_content);
}




/*
 * 插入pgsql数据库封装方法
 */
function insert($post = [],$table = null,$buKey = '',$buVal = '')
{
    $keys = array_keys($post);
    $keys = join(',',$keys);
    $values = '\''.join('\',\'',$post).'\'';
    if($buKey == ''){
        $sql = "INSERT INTO {$table} (".$keys.") VALUES (".$values.")";
    }else{
        $sql = "INSERT INTO {$table} (".$keys.",".$buKey.") VALUES (".$values.",'".$buVal."') RETURNING albh";
    }
    $res = $result = Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->execute($sql);
//        dump($sql);
//        var_dump($res);die;
    return $res;
}


/*
  * 打印方法的封装
  * */
function dy($data){
    // 定义样式
    $str='';
    // 如果是boolean或者null直接显示文字；否则print
    if (is_bool($data)) {
        $show_data=$data ? 'true' : 'false';
    }elseif (is_null($data)) {
        $show_data='null';
    }else{
        echo '<pre>';
        $show_data=print_r($data,true);
    }
    $str.=$show_data;
    $str.='';
    echo $str;
    die;
}

/**
 * @param string $code
 * @param string $msg
 * @param array $data
 * 返回json
 */
function return_json($code='', $data=array(), $msg=''){
    $arr = array(
        'code' => $code,
        'data' => $data,
        'msg'  => $msg,
        'version' => 1.0,
        'timestamp' => (int)microtime(true)
    );
    json($arr,200, ['Access-Control-Allow-Origin'=> '*','Access-Control-Allow-Headers'=>'content-type,token,x-requested-with','Access-Control-Expose-Headers'=>'*','Access-Control-Allow-Methods'=>"GET,POST,PUT,DELETE,PATCH,OPTIONS"])->send();
    exit;
}

/*@返回json
 * */
function return_json_2($code,$data,$msg){
    $color = [
        1 => 'gray',
        2 => 'green',
        3 => 'yellow',
        4 => 'red',
    ];
    echo json_encode(['code' => $code, 'data' => $data, 'msg' => $msg, 'color' => $color[substr($code,0,1)]]);die;
}

/*
 * ip访问接口限制
 * @param ip 需要限制访问的ip
 * @param max 每天访问最大次数
 */
function limitRequest($systemId,$reqRate){

    $request = Request::instance();
    //缓存标识
    $cacheKey = md5($systemId);
    //$cacheKey = 'aaaa';
    //读取缓存
    $count = Cache::get($cacheKey );
    if($count>$reqRate - 1){
        return 'Frequent visits';
    }
    //写入缓存
    //更新访问次数或者用Cache::inc('name',1,1);也行
    //Cache::set($cacheKey,$count+1,24*60*60);
    Cache::set($cacheKey,$count+1,10);
}

/*
 * 操作日志记录封装
 * @param data 数据
 * @param
 * */
function operate_Log($data){

    //获取当前时间 即 入库时间
    $now_time = date("Y-m-d H:i:s");

}

/*
 * 调用他人的日志封装
 * @param url 调用url
 * @param request 请求参数
 * @param response 返回参数
 * */
function out_log($url, $request, $response){

    //获取当前时间
    $now_time = date('Y:m:d H:i:s');

    //封装数据
    $data = [
        'url' => $url,
        'request_param' => $request,
        'response_param' => $response,
        'use_time' => $now_time,
        'store_time' => $now_time
    ];

    $res = insert($data, 'out_log');
    if($res){
        return 1;
    }else{
        return null;
    }
}

function in_log($url, $request, $response, $user_ip){
    //获取当前时间
    $now_time = date('Y:m:d H:i:s');
    //封装数据
    $data = [
        'url' => $url,
        'request_param' => $request,
        'response_param' => $response,
        'use_time' => $now_time,
        'store_time' => $now_time,
        'user_ip' => $user_ip
    ];
    $res = insert($data, 'in_log');
    if($res){
        return 1;
    }else{
        return null;
    }
}

/*
     * 查询账号所属的信息
     * */
function user_info($user_id)
{

    //从session获取userid
    //$user_id = session('userid');

    $res = Db::connect('mysql://detuo:DT@pt18cg@172.27.148.98/sip_data_base#utf8')
        ->table('cap_user u')
        //USER_ID 注册服务者id USER_NAME 注册服务者名称
        //ORGID 注册机构编码     ORGNAME 注册机构名称
        ->field('u.USER_ID, u.USER_NAME, o.ORGID, o.ORGNAME')
        ->join('org_employee e', 'u.OPERATORID = e.OPERATORID', 'LEFT')
        ->join('org_organization o', 'o.ORGID = e.ORGID', 'LEFT')
        ->where('$u.USER_ID', $user_id)
        ->find();

    return $res;
}

/**
 * 返回正确的数据
 * @param array $data
 * @return mixed
 */
function success($data = [],$code=200)
{
    return encodeResult($code, 'success', $data);
    // json($arr,200, ['Access-Control-Allow-Origin'=> '*','Access-Control-Allow-Headers'=>'content-type,token,x-requested-with','Access-Control-Expose-Headers'=>'*','Access-Control-Allow-Methods'=>"GET,POST,PUT,DELETE,PATCH,OPTIONS"])->send();
}

/**
 * 返回错误的数据
 * @param array $data
 * @return mixed
 */
function error($data = [])
{
    return encodeResult(300, 'error', $data);
}

/**
 * 返回错误的数据
 * @param array $data
 * @param string $message
 * @return mixed
 */
function error_msg($message, $data = [])
{
    return encodeResult(300, $message, $data);
}

/**
 * 返回错误
 * @param int $code 错误码
 * @param string $message 错误信息
 * @param array $data
 * @return mixed
 */
function unauthorized($code, $message, $data = [])
{
    return encodeResult($code, $message, $data);
}

/**
 * 接口统一返回的JSON格式
 * @param $code
 * @param $message
 * @param null $data
 * @return mixed
 */
function encodeResult($code, $message, $data = null)
{
    $result = [
        'code' => $code,
        'message' => $message,
        'data' => $data,
        'version' => 1.0,
        'timestamp' => (int)microtime(true)
    ];

json($result,200, ['Access-Control-Allow-Origin'=> 'http://localhost:8080','Access-Control-Allow-Credentials'=>'true','Access-Control-Allow-Headers'=>'content-type,token,x-requested-with','Access-Control-Expose-Headers'=>'*','Access-Control-Allow-Methods'=>"GET,POST,PUT,DELETE,PATCH,OPTIONS"])->send();
     //return json_encode($result, JSON_UNESCAPED_UNICODE);
}