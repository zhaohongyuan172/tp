<?php


namespace app\apimanage\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;

class ApiManage extends Controller
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

        return $this->show_index('apiList');

    }

    /*
     * 显示登录页面
     */
    public function index(){
        return $this->fetch('login');
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
        $data = $_POST;

        //用户名,密码,验证码不能为空
        if(empty($data['userid']) && empty($data['password']) && empty($data['vcode'])){
            $this->error('登录失败');
        }
        //过滤用户登录信息
        $data['userid'] = stripslashes($data['userid']);
        $data['userid'] = htmlspecialchars($data['userid']);

        //验证码验证
        if(!$this->check_Verification_code($data['vcode'])){
            $this->error('验证码验证失败');
        }

        //验证用户
        //连接数据库并查询用户
//        $sql = 'select OPERATOR_ID from cap_use where  USER_ID = ' . $data['userid'];
//        $result = Db::connect('mysql://detuo:DT@pt18cg@172.27.148.98:3306/sip_data_base#utf8')->query($sql);
//        if(!$result){
//            $this->error('用户不存在，登录失败');
//        }

        //登录成功，记录session
        @session_start();
        $_SESSION['userid'] = $data['userid'];
        //登录日志记录
        $insert['userid'] = $data['userid'];
        $insert['log_time'] = date('Y-m-d H:i:s',time());
        $insert['store_time'] = date('Y-m-d H:i:s',time());
        $ddd = insert($insert,'login_log');

        if($ddd){
            //跳转到展示页面
            $this->success('登录成功','Api_Manage/api_list');
        }
        else{
            $this->error('发生意外错误');
        }


    }

    /*
     * 获取普元目录
     * @param userid
     */
    public function getPY($userid){

        //调用普元接口
        $url = 'http://10.81.67.54:8080/catalog/rest/services/catalogService/getCatalogDataByAll';
        $data['page'] = 1;
        $data['pageSize'] = 1;
        $data['status'] = 1;
        $data['user_id'] = $userid;
        $res = curl($url,$data);
        return $res;
    }

    /*
     * 注册页面
     */
    public function add_index()
    {

        //登录验证
        //$this->check_user_id();

        //显示页面
        //$this->show_index('add');

        return view('Api_Manage/add');
    }

    /*
     * 注册提交
     */
    public function add_request(){

        //获取前台收集的信息
        $data = $_POST;

        //获取当前时间
        $now_time = date('Y:m:d H:i:s');

        $data['store_time'] = $now_time;

        //判断该API是否已在数据库中存在
        $res = Db::connect($this->database_url)
            ->table('add_api')
            ->where('serviceId', $data['serviceId'])
            ->find();
        if($res){   //已存在
            //弹出操作失败信息，并跳转到原页面
            $this->error('该服务已存在');
        }else{      //不存在，为新信息
            //将收集信息存入数据库
            $res = insert($data, 'add_api');
            //add_api录入成功，将其他信息存入fz_api表
            if($res){
                //封装新的API URL 调用encapsulation接口
                $api_new = $this->encapsulation($data['api_path']);
                $api_data = [
                    'api_old' => $data['api_path'],
                    'api_new' => $api_new,
                    'store_time' => $data['store_time']
                ];
                $res =insert($api_data, 'fz_api');

                /*if(!empty($data['cataId'])){
                    //fz_api录入成功,将接口信息同步给普元
                    $res = curl($this->sendPY_url,$data);
                    if("0" == $res['code']){   //普元同步成功
                        $this->success('操作成功');
                    }else{      //普元同步失败
                        $this->error('普元录入失败','show_index?index=add');
                    }
                }*/
                if($res){
                    $this->success('封装API信息录入成功');
                }else{
                    $this->error('封装API信息录入失败');
                }
            }else{  //add_api录入失败
                //弹出操作失败信息，并跳转到原页面
                $this->error('API信息录入失败');
            }
        }
    }



    /*
     * 二次封装接口
     * @param url 封装前api的url
     * @return url_new 封装后api的url
     */
    public function encapsulation($url){
        //查询重定向前的api url在数据库中的主键id
        $sql = 'select id from add_api where api_path = \'' . $url . '\'';
        //dy($sql);
        $res = Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql);
        //dy(Db::connect(db_config2));
        $url_new = 'http://127.0.0.1:80/project/public/Api/id/' . $res[0]['id'];
        return $url_new;
    }


    /*
     * 重定向接口
     * @param id 重定向api url的主键id
     * @return
     */
    public function Api(){
        $path_info = $_SERVER['PATH_INFO'];
        $id = explode('/',$path_info)[2];
        //var_dump($id);die;
        $data = file_get_contents('php://input');
        //var_dump($id,$data);die;

        //查询数据库获取重定向前的url
        $sql = 'select api_path from add_api where id = ' . $id;
        $url = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql))[0]['api_path'];
        //var_dump($url);die;

        //查询数据库获取访问iph和访问限制
        $sql = 'select ip,max from fz_api where api_old = \'' . $url . '\'';
        $ip = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql))[0]['ip'];
        $max = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql))[0]['max'];
        //var_dump($ip,$max);die;

        //判断是否有访问限制
        if(empty($ip) && empty($max)){
            //查询重定向后的api url的id的对应的重定向前的api url
            $res = curl($url,$data);
            var_dump($res);
        }
        else{
            //存在访问限制，执行限制操作
            $ddd = limitRequest($ip,$max);
            if($ddd == 'Frequent visits'){
                return '访问频繁';
            }
            //$res = curl($url,$data);
            //return $res;
            return 1;

        }
    }

    /*
     * 获取api申请信息接口
     */
    public function limit(){
        $json = file_get_contents('php://input');
        $data = json_decode($json,true);
        $data['store_time'] = date('Y-m-d H:i:s',time());

        //将数据插入数据库
        $sql = "update fz_api" .
                "set apply_man = $data[apply_man]," .
                "enc_type = $data[enc_type]," .
                "ip = $data[ip]," .
                "max = $data[max]," .
                "concurrency = $data[concurrency]," .
                "apply_reason = $data[apply_reason]," .
                "store_time = $data[store_time]" .
                "where api_new =  '$data[url]'" ;
        //echo $sql;die;
        $ddd = Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->execute($sql);

        //执行结果判断是否操作成功
        if($ddd){
            //插入成功，返回成功
            $res['code'] = 200;
            $res['message'] = '接收成功';
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
     * 列表展示接口
     * 2019/11/30
     *  */
    public function api_list(){

        //登录验证
        @session_start();
        if(!isset($_SESSION['userid'])){
            $this->error('请先登录');
        }

        $user_id = $_SESSION['userid'];

        //检查是否登录
        //$res = $this->check_user_id();

        return $this->fetch('apiList');

        /*
        $page = Request::instance()->get('page');
        $size = Request::instance()->get('size');
        $keyword = Request::instance()->get('keyword'); //API名字搜索
        $where = '';
        if($keyword) {
            $where = " api_name LIKE '%{$keyword}%'";
        }
        $res = Db::connect($this->database_url)
            ->table('add_api')
            ->field('param_info, case_info, api_path, api_name, api_desc, agreement, request_mode, ')
            ->where('registerId', $user_id)
            ->where($where)
            //->page($page, $size)
            ->select();
        if($res){
            return_json(200, $res, '返回成功');
        }else{
            return_json(300, [], '返回失败');
        }
        */
    }

    public function delete(){
        //$user_id = $_SESSION('userid');
        $user_id = 1;
        $id = input('id');      //需要删除的api_id
        $res = Db::table('api_save_test')
            ->where('user_id',$user_id)
            ->where('id', $id)
            ->update(['api_status' => 2]);
        if($res){
            return_json(200, [], '操作成功');
        }else{
            return_json(300, [], '操作失败');
        }
    }

    /*
     * 测试 封装前接口
     */
    public function aaa(){
        $data = file_get_contents('php://input');
        //var_dump($data);
        return 'time is:'.$data;
    }

    /*
    * 返回普元目录给前段显示
    * */
    public function getPY_toIndex(){

        @session_start();
        $userid = session('userid');
        $userid = 'SKWBM';
        $cata = $this->getPY($userid);
        $this->assign('cata', $cata);

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
     * 新建安全组
     */
    public function add_group(){
        $data = $_POST;
        $store_time = date("Y-m-d H:i:s",time());
        $data['store_time'] = $store_time;
        if (empty($data['userid']) || empty($data['type']) || empty($data['name']) || empty($data['ip'])) {
            return $this->error('参数不完整');
        }
        $ddd = insert($data,'safe_group');

        if($ddd){
            //插入成功，返回成功
            $res['code'] = 200;
            $res['message'] = '插入成功';
            return json_encode($res,JSON_UNESCAPED_UNICODE);
        }
        else{
            //插入失败，返回失败
            $res['code'] = 400;
            $res['message'] = '插入失败';
            return json_encode($res,JSON_UNESCAPED_UNICODE);
        }



    }


    /*
     * aoi信息的删除
     */
    public function api_delete()
    {
        $data = $_POST;
        if (!isset($data['id'])) {
            return $this->error('id值不可为空');
        }
        $sql = "update `add_api` set is_delete = '2' where cataId = '{$data['id']}'";
        $ddd = Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->execute($sql);

        //执行结果判断是否操作成功
        if($ddd){
            //插入成功，返回成功
            $this->success('删除成功');
        }
        else{
            //插入失败，返回失败
            return $this->success('删除失败');
        }

    }

    /*
     * 查询账号所属的信息
     * */
    public function user_info(){

        //获取userid
        $user_id = session('userid');

        $res = Db::connect('mysql://detuo:DT@pt18cg@172.27.148.98/sip_data_base#utf8')
                    ->table('cap_user u')
                    //USER_ID 注册服务者id USER_NAME 注册服务者名称
                    //ORGID 注册机构编码     ORGNAME 注册机构名称
                    ->field('u.USER_ID, u.USER_NAME, o.ORGID, o.ORGNAME')
                    ->join('org_employee e','u.OPERATORID = e.OPERATORID','LEFT')
                    ->join('org_organization o', 'o.ORGID = e.ORGID', 'LEFT')
                    ->find();

        if($res) {
            return $res;
        }else{
            return 0;
        }
    }
    

}