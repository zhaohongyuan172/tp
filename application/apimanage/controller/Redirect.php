<?php
/*
 * 登录控制器
 * 2019/12/3 16:58:03
 */

namespace app\apimanage\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;
use Firebase\JWT\JWT;

class Redirect extends Controller
{
    //初始化配置
    protected $redirectModel;


    /**
     * 构造函数
     * AbnormalController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->redirectModel = new \app\apimanage\model\Redirect;
    }

    /*
    * 二次封装接口
    * @param url 封装前api的url
    * @return url_new 封装后api的url
    */
    public function encapsulation($url){
        //查询重定向前的api url在数据库中的主键id
        $res = $this->redirectModel->encapsulation($url);

        //重新组织封装后url地址
        $url_new = 'http://127.0.0.1:80/project/public/Api/id/' . $res[0]['id'];
        return $url_new;
    }


    /*
     * 重定向接口
     * @param id 重定向api url的主键id
     * @return
     */
    public function Api(){

        $data = input();
        if(empty($data['id']) || empty($data['token'])){
            return '参数不完整';
        }

        $id = $data['id'];
        $token = $data['token'];

        $data = file_get_contents('php://input');


        //token验证
        $result = $this->checkToken($token);

        $code = json_decode($result,true)['code'];
        //var_dump($code);
        if($code != '200'){
            return 'Token验证失败';
        }

        $url = json_decode(json_decode($result,true)['data']['data'],true)['url'];
        $api = "http://127.0.0.1:80/project/public/Api?id=" . $id;

        if($url != $api){
            return 'Token验证失败';
        }


        //记录此接口被调用的情况
        $user = $_SERVER;
        $rrr = $this->redirectModel->recode_call_api($user['SERVER_NAME'],$id);

        if(!$rrr){
            $this->error('发生意外错误');
        }

        //查询数据库获取重定向后的url,
        $url = $this->redirectModel->Api_url($id);

        //在通过url查询数据库获取访问iph和访问限制
        $rows = $this->redirectModel->Api_ip_max($url);

        if($rows){
            $systemId = $rows[0]['systemId'];
            $reqRate = $rows[0]['reqRate'];
        }

        //判断是否有访问限制
        if(empty($systemId) && empty($reqRate)){
            //查询重定向后的api url的id的对应的重定向前的api url
            $res = curl_post($url,$data);
            return $res;
            //var_dump($res);
        }
        else{
            //存在访问限制，执行限制操作
            $ddd = limitRequest($systemId,$reqRate);
            if($ddd == 'Frequent visits'){
                return '访问频繁';
            }
           // $res = curl_post($url,$data);
            $res = 123;
            return $res;
        }
    }


    /*
     * 获取api申请信息接口
     */
    public function limit(){
        $json = file_get_contents('php://input');
        $data = json_decode($json,true);

        if(empty($data['code']) || empty($data['url']) || empty($data['lifetime']) || empty($data['applyPeopleId']) || empty($data['systemId']) || empty($data['reqRate'])){
            $res['code'] = 400;
            $res['message'] = '参数不完整';
            return json_encode($res,JSON_UNESCAPED_UNICODE);
        }

        $data['store_time'] = date('Y-m-d H:i:s',time());

        //将获取到的封装信息更新至数据库
        $ddd = $this->redirectModel->limit($data);


        //将用户申请信息插入数据库
        $api_apply['userid'] = $data['applyPeopleId'];
        $api_apply['api'] = $data['url'];
        $api_apply['lifetime'] = $data['lifetime'];
        $api_apply['store_time'] = $data['store_time'];
        $rrr = $this->redirectModel->insert_user_apply($api_apply);




        //执行结果判断是否操作成功
        if($ddd && $rrr){
            //插入成功
            //生成token
            $data1 = json_encode($data,JSON_UNESCAPED_UNICODE);
            $token = $this->createToken($data1,$data['lifetime']);
            $arr['token'] = $token;
            $arr['data'] = json_encode($data,JSON_UNESCAPED_UNICODE);
            $arr['store_time'] = date('Y-m-d H:i:s',time());
            $this->redirectModel->insert_token($arr);


            //查询api请求方式
            $type = $this->redirectModel->get_api_type($data['code']);


            $res['code'] = 200;
            $res['message'] = '接收成功';
            $res['token'] = $token;
            $res['param_info'] = $type[0]['param_info'];
            $res['url'] = $data['url'];
            $res['api_desc'] = $type[0]['api_desc'];
            $res['agreement'] = $type[0]['agreement'];
            $res['request_mode'] = $type[0]['request_mode'];
            $res['back_type'] = $type[0]['back_type'];

            return json_encode($res,JSON_UNESCAPED_UNICODE);
        }
        else{
            //插入失败，返回失败
            $res['code'] = 400;
            $res['message'] = '发生意外错误';
            return json_encode($res,JSON_UNESCAPED_UNICODE);
        }
    }

    /*
     * token解码
     */
    public function decode_token(){
        $data = input();
        $token = $data['token'];
        $res = JWT::decode($token,'TokenKey', ["HS256"]);
        var_dump($res);
    }

    /**
     * 创建 token
     * @param array $data 必填 自定义参数数组
     * @param integer $exp_time 必填 token过期时间 单位:秒 例子：7200=2小时
     * @param string $scopes 选填 token标识，请求接口的token
     * @return string
     */
    public function createToken($data = "", $exp_time = 0, $scopes = "")
    {

        //JWT标准规定的声明，但不是必须填写的；
        //iss: jwt签发者
        //sub: jwt所面向的用户
        //aud: 接收jwt的一方
        //exp: jwt的过期时间，过期时间必须要大于签发时间
        //nbf: 定义在什么时间之前，某个时间点后才能访问
        //iat: jwt的签发时间
        //jti: jwt的唯一身份标识，主要用来作为一次性token。
        //公用信息
        try {
            $key = 'TokenKey';
            $time = time(); //当前时间
            $token['iss'] = 'Datatom'; //签发者 可选
            $token['aud'] = ''; //接收该JWT的一方，可选
            $token['iat'] = $time; //签发时间
            $token['nbf'] = $time+3; //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            if ($scopes) {
                $token['scopes'] = $scopes; //token标识，请求接口的token
            }
            if (!$exp_time) {
                $exp_time = 7200;//默认=2小时过期
            }
            $token['exp'] = $time + $exp_time; //token过期时间,这里设置2个小时
            if ($data) {
                $token['data'] = $data; //自定义参数
            }

            $json = JWT::encode($token, $key);
            //Header("HTTP/1.1 201 Created");
            //return json_encode($json); //返回给客户端token信息
            return $json; //返回给客户端token信息

        } catch (\Firebase\JWT\ExpiredException $e) {  //签名不正确
            $returndata['code'] = "104";//101=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return json_encode($returndata); //返回信息
        } catch (Exception $e) {  //其他错误
            $returndata['code'] = "199";//199=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return json_encode($returndata); //返回信息
        }
    }


    /**
     * 验证token是否有效,默认验证exp,nbf,iat时间
     * @param string $jwt 需要验证的token
     * @return string $msg 返回消息
     */
    public function checkToken($jwt)
    {
        $key = 'TokenKey';
        try {
            JWT::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($jwt, $key, ['HS256']); //HS256方式，这里要和签发的时候对应
            $arr = (array)$decoded;

            $returndata['code'] = "200";//200=成功
            $returndata['msg'] = "成功";//
            $returndata['data'] = $arr;//返回的数据
            return json_encode($returndata); //返回信息

        } catch (\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            //echo "2,";
            //echo $e->getMessage();
            $returndata['code'] = "101";//101=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return json_encode($returndata); //返回信息
        } catch (\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            //echo "3,";
            //echo $e->getMessage();
            $returndata['code'] = "102";//102=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return json_encode($returndata); //返回信息
        } catch (\Firebase\JWT\ExpiredException $e) {  // token过期
            //echo "4,";
            //echo $e->getMessage();
            $returndata['code'] = "103";//103=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return json_encode($returndata); //返回信息
        } catch (Exception $e) {  //其他错误
            //echo "5,";
            //echo $e->getMessage();
            $returndata['code'] = "199";//199=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return json_encode($returndata); //返回信息
        }
        //Firebase定义了多个 throw new，我们可以捕获多个catch来定义问题，catch加入自己的业务，比如token过期可以用当前Token刷新一个新Token
    }



}