<?php
/*
 * Api注册控制器
 * 2019/12/3 16:58:03
 */

namespace app\apimanage\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;
use think\Session;


class Registered extends Controller
{
    private $database_url ;
    private $sendPY_url;

    protected function _initialize()
    {
        parent::_initialize();
        $this->database_url = Config::get('url')['database_url'];
        $this->sendPY_url = Config::get('url')['sendPY_url'];
    }

    public function test()
    {

        return view('Api_Manage/test');
        //return $this->show_index('apiList');

    }

    /*
     * 显示页面
     * @param $index 页面名称
     * */
    public function show_index($index){

        if(empty($index)){
            $res = input('index');
        }else{
            $res = $index;
        }
        $url = "Api_Manage/" . $res;
        return view($url);
    }

    /*
     * 注册提交
     */
    public function add_request(){
        //用户登录验证
        @session_start();
        $res = $_SESSION;
//        print_r($res);die;

        if(!isset($res['userid'])){
          return error('请先登录');
        }
        $user_id = $res['userid'];
        //获取前台收集的信息
        $data = input();
        //获取当前时间
        $now_time = date('Y:m:d H:i:s');

        $data['store_time'] = $now_time;
        $data['registerId'] = $user_id;
        $data['deptId'] = $res['Orgid'];
        $data['dept'] = $res['Orgname'];
        $data['isDBService'] = false;
        $data['DBColumn'] = ' ';
        $data['code'] = uniqid();

        //获取用户信息

        //判断该API是否已在数据库中存在
        $res = Db::connect($this->database_url)
            ->table('dt_apimanage_api_register')
            ->where('api_path',$data['api_path'])
            ->find();
        if($res){   //已存在
            //弹出操作失败信息，并跳转到原页面
            //$this->error('该服务已存在');
            return error_msg('该服务已存在');
        }else{      //不存在，为新信息

            //将收集信息存入数据库
            $res = insert($data, 'dt_apimanage_api_register');

            //api录入成功，将其他信息存入dt_apimanage_api_encapsulate表
            if($res){
                //封装新的API URL 调用encapsulation接口
                $api_new = $this->encapsulation($data['api_path'], $user_id);
                $api_data = [
                    'api_old' => $data['api_path'],
                    'api_new' => $api_new,
                    'store_time' => $data['store_time']
                ];
                //将封装信息存入 表 fz_api
                $res =insert($api_data, 'dt_apimanage_api_encapsulate');

                if($res){   //dt_apimanage_api_encapsulate表插入成功
                            //继续插入dt_apimanage_api_encapsulate_limit表
                    $new_api = ['api' => $api_data['api_new']];
                    $res = insert($new_api, 'dt_apimanage_api_encapsulate_limit');
                    if($res){
                        //$this->success('封装API信息录入成功','ApiInformation/show_api_detail');
                        return success();
                    }else{
                        //$this->error('dt_apimanage_api_encapsulate_limit 插入失败');
                        return error_msg('dt_apimanage_api_encapsulate_limit 插入失败');
                    }
                }else{
                    //$this->error('dt_apimanage_api_encapsulate 插入失败');
                    return error_msg('dt_apimanage_api_encapsulate 插入失败');
                }
            }else{  //api录入失败
                //弹出操作失败信息，并跳转到原页面
                //$this->error('API信息录入失败');
                return error_msg('API信息录入失败');
            }
        }
    }

    /*
     * 注册页面
     */
    public function add_index()
    {
        //用户登录验证
        @session_start();
//        if(!isset($_SESSION['userid'])){
//            $this->error('请先登录');
//        }
        $user_id = $_SESSION['userid'];

        $registerId = $user_id;
        $deptId = $_SESSION['Orgid'];
        $dept = $_SESSION['Orgname'];


        //返回用户信息
        //$user_info = $this->user_info($user_id);

        $user_info = [
            'registerId' => $registerId,
            'deptId' => $deptId,
            'dept' => $dept
        ];

        /*        $user_info = [
            'registerId' => 'HBGBM',
            'register' => 'HBGBM',
            'deptId' => '1665',
            'dept' => '上海中国航海博物馆'
        ];*/

        return view('Api_Manage/add', ['user_info' => $user_info]);

        //return view('Api_Manage/add');
    }

    /*
     * 二次封装接口
     * @param url 封装前api的url
     * @return url_new 封装后api的url
     */
    public function encapsulation($url,$userid){
        //查询重定向前的api url在数据库中的主键id
        $sql = 'select id from dt_apimanage_api_register where api_path = \'' . $url . '\'' . "and registerId = '$userid'";
        //dy($sql);
        $res = Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql);
        //dy(Db::connect(db_config2));
        //dy($res);
        $url_new = 'http://127.0.0.1:80/project/public/Api?id=' . $res[0]['id'];
        return $url_new;
    }

    /*
     * 列表展示接口
     *
     *  */
    public function api_list(){

        @session_start();
        session('userid','1231412');
       // $user_id = session('userid');
        $user_id = 1560014588;
        //登录验证
        //$this->check_user_id($user_id);

        //获取当前账户下的API接口信息列表
        $res = Db::connect($this->database_url)
            ->table('api')
            ->field('id, api_name, api_path, api_desc, dept')
            ->where('registerId', $user_id)
            ->select();


        return view('Api_Manage/apiList', $res);

    }



    /*
     * 详情展示接口
     * */
    public function apiDetail(){

        $user_id = session('userid');

        //$user_id = 1560014588;
        //登录验证
        $this->check_user_id($user_id);

        //获取需要查看的API id
        $data = $_POST;

        //根据API id 查出API详情
        $res = Db::connect($this->database_url)
            ->table('api')
            ->field('api_path, api_name, api_desc, agreement, request_mode, back_type, 
                              call_times_limit, safe_group, serviceId, code, chnName, cataId, serviceDesc,
                              version, registerId, register, dept, registerDate, publishDate, validityDate, 
                              isPublic,requireFile,isDBService, DBColumn')
            ->where('id', $data['id'])
            //->where('id', 1)
            ->find();

        return view('apiDetail', $res);
    }

    /*
     * 登录验证
     * @param $user_id 用户id
     * */
    public function check_user_id($user_id){

        //@session_start();
        if(empty($user_id)) {
            $this->error('请先登录', 'show_index?index=login');
        }else{
            return 1;
        }
    }

    /*
     * 查询账号所属的信息
     * */
    public function user_info($user_id){

        //获取userid
        //$user_id = session('userid');


        $res = Db::connect('mysql://detuo:DT@pt18cg@172.27.148.98/sip_data_base#utf8')
            ->table('cap_user u')
            //USER_ID 注册服务者id USER_NAME 注册服务者名称
            //ORGID 注册机构编码     ORGNAME 注册机构名称
            ->field('u.USER_ID as registerId, u.USER_NAME as register, o.ORGID as deptId, o.ORGNAME as dept ')
            ->join('org_employee e','u.OPERATOR_ID = e.OPERATORID','LEFT')
            ->join('org_organization o', 'o.ORGID = e.ORGID', 'LEFT')
            ->where('u.USER_ID', $user_id)
            ->find();

        if($res) {
            return $res;
        }else{
            return 0;
        }
    }

    /*
     * 提交注册api信息给普元
     * @param api_path,绑定api地址 userid 用户id
     * @return
     * @author xsx
     * 2019/12/4 14:15
     */
    public function add($userid,$api_path,$cataid){
        //验证数据是否存在
        if(empty($userid) || empty($api_path)){
            $this->error('绑定参数不完整');
        }

        $sql = "select id,code,chnName,cataId,serviceDesc,version,registerId,register,deptId,dept,registerDate,publishDate,validityDate,isPublic,requireFile from dt_apimanage_api_register 
                where registerId = '$userid' and url = '$api_path'";
        $ddd = Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql);

        $sql_url = "select api_new from dt_apimanage_api_encapsulate_limit where api_old = '{$api_path}'";
        $url = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql_url))[0]['api_new'];

        //id code cnName cataId serviceDesc version registerId register deptId dept registerDate
        $data['id'] = $ddd[0]['id'];
        $data['code'] = $ddd[0]['code'];
        $data['cnName'] = $ddd[0]['chnName'];
        $data['cataId'] = $cataid;
        $data['serviceDesc'] = $ddd[0]['serviceDesc'];
        $data['version'] = $ddd[0]['version'];
        $data['registerId'] = $ddd[0]['registerId'];
        $data['register'] = $ddd[0]['register'];
        $data['deptId'] = $ddd[0]['deptId'];
        $data['dept'] = $ddd[0]['dept'];
        $data['registerDate'] = $ddd[0]['registerDate'];
        $data['publishDate'] = $ddd[0]['publishDate'];
        $data['validityDate'] = $ddd[0]['validityDate'];
        $data['status'] = '8';
        $data['optype'] = 'add';
        $data['isPublic'] = $ddd[0]['isPublic'];
        $data['requireFile'] = $ddd[0]['requireFile'];
        $data['url'] = $url;
        $data['isDBService '] = 'false';
        $data['DBColumn'] = ' ';


        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        //调取普元资源注册接口
        $py_url = 'http://172.26.16.2:8080/catalog/rest/services/catalogService/mountService';
//        $rrr = curl_post($py_url,$data);
        $rrr = '{"code":"0","message":"接口调用成功"}';
        $code = json_decode($ddd,true)['code'];
        if($code == 0){
            //记录调用普元的接口记录
            $insert['userid'] = $userid;
            $insert['url'] = $py_url;
            $insert['request'] = $data;
            $insert['response'] = $rrr;
            $insert['call_time'] = date('Y-m-d H:i:s',time());
            $insert['store_time'] = date('Y-m-d H:i:s',time());
            insert($insert,'dt_apimanage_log_callinterface');
            $this->success('绑定成功','ApiInformation/show_api_detail');
        }
        else{
            $this->error('绑定失败');
        }


    }

}