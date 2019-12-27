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
use think\Session;


class Login extends Controller
{
    //初始化配置
    protected $loginModel;

    /**
     * 构造函数
     * AbnormalController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->loginModel = new \app\apimanage\model\Login;
    }

    /*
     * 判断用户是否已经登录
     */
    public function is_login(){
        @session_start();
        if(!isset($_SESSION['userid'])) {
            return error('未登录');
        }
        return success('已登录');

    }




    /*
     * 显示登录页面
     */
    public function index(){
//        echo 111;die;
        return $this->fetch('Api_Manage/index');
    }





    /*
     * 生成验证码
     */
    public function make_Verification_code(){
        //验证码配置
        $config =    [
            // 验证码字体大小
            'fontSize'    =>    30,
            // 验证码位数
            'length'      =>    4,
            // 关闭验证码杂点
            'useNoise'    =>    false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

    /*
     * 验证验证码
     */
    public function  check_Verification_code($code){
        $captcha = new Captcha();
        return $captcha->check($code);
    }

    /*
     * 登录验证
     * @param username用户名
     * @param password密码
     * @return
     */
    public function login(){
        //$data = $_POST;
        $data = input();

//echo 100;die;
//        $data['userid'] = '1234';
//        $data['password'] = '56789';

        //用户名,密码,验证码不能为空
        if(empty($data['userid']) && empty($data['password'])){

            return error('用户名，密码不能为空');
        }

        //过滤用户登录信息
        $data['userid'] = stripslashes($data['userid']);
        $data['userid'] = htmlspecialchars($data['userid']);

        //验证码验证
//        if(!$this->check_Verification_code($data['vcode'])){
//            $this->error('验证码验证失败');
//        }

        //验证用户
        //连接数据库并查询用户
//        $sql = 'select OPERATOR_ID from cap_user where  USER_ID = ' . $data['userid'];
//        $result = Db::connect('mysql://detuo:DT@pt18cg@172.27.148.98:3306/sip_data_base#utf8')->query($sql);
//        if(!$result){
//            $this->error('用户不存在，登录失败');
//        }
        //查询的机构编码和机构名称
//        $opeID = "select OPERATOR_ID, from cap_user where USER_ID = '$data[userid]'";
//        $orgID = "select ORGID from org_employee where OPERATORID = '$opeID'";
//        $ddd = "select ORGID,ORGNAME from org_organization ORGID = '$orgID'";

        //------使用封装的公共方法 查询用户的机构编码和机构名称-------------
        //$user_data = user_info($data['userid']);
        //------使用封装的公共方法 查询用户的机构编码和机构名称-------------

        //登录成功，记录session
        @session_start();
        $_SESSION['userid'] = $data['userid'];
        //$_SESSION['Orgid'] = $ddd[0]['ORGID'];
       // $_SESSION['Orgname'] = $ddd[0]['ORGNAME'];
        $_SESSION['Orgid'] = 'a1b2c3';
        $_SESSION['Orgname'] = '大数据中心';

//print_r($_SESSION);die;
        //登录日志记录
        $insert['userid'] = $data['userid'];
        $insert['log_time'] = date('Y-m-d H:i:s',time());
        $insert['store_time'] = date('Y-m-d H:i:s',time());
        $ddd = $this->loginModel->test($insert);



        if($ddd){
            //跳转到展示页面
            return success('success');
        }
        else{
            return error('error');
        }
    }


    /*
     * 用户退出，清除session
     */

    public function logout(){
        session_start();
        session_destroy();
        return success('success');
    }

}