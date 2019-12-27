<?php
/*
 * 用户资源目录详情控制器
 * 2019/12/3 16:58:03
 */

namespace app\apimanage\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;
use think\Paginator;
use Crasphb\Pagination;


class CataInformation extends Controller
{
    //初始化配置
    protected $cataModel;
    protected $Registered;

    /**
     * 构造函数
     * AbnormalController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->cataModel = new \app\apimanage\model\CataInformation;
        $this->Registered = new Registered;
    }

    /*
     * 用户资源目录展示
     * @param page 页码 pagesize每页条数
     * @return res 用户资源目录
     * @author xsx
     * 2019/12/3
     */
    public function cata_index(){

        //用户登录验证
        @session_start();
        if(!isset($_SESSION['userid'])){
            $this->error('请先登录');
        }

        //调取普元目录接口
//        $userid = 'SFGWBM1';
//        $data['page'] = $page;
//        $data['pageSize'] = 10;
//        $data['status'] = 4;
//        $data['user_id'] = $userid;
//        $url = 'http://172.26.16.2:8080/catalog/rest/services/catalogService/getCatalogDataByAll';
//        $rows = $curl_post($url,$data);
//        $rows = json_decode($rows,true);

        $json = '{"code":0,
        "data":[{"bus_type":"","cataLogGroups":[],"cata_code":"303084122704\/000001","cata_id":"000dc0c72c2e4062820e7bea17090730","cata_level":"国家目录","cata_status_cn":"1","cata_title":"异常运维保障数据","open_condition":"不对社会开放。","org_name":"国务院办公厅","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"作为行政依据、工作参考，用于数据校核、业务协同等。","shared_type":"2"},
        {"bus_type":"","cataLogGroups":[],"cata_code":"BA6006\/000218","cata_id":"0018a7a0-f37c-11e8-9b39-890fce5b9e18","cata_level":"市级目录","cata_status_cn":"6","cata_title":"项目信息","open_condition":"不开放","org_name":"上海市青浦区人民政府","parent_cata_id":"","public_type":"","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"申请共享","shared_type":"2"},
        {"bus_type":"","cataLogGroups":[],"cata_code":"3070010370\/000066","cata_id":"004457334c204a40ba14567fe840324f","cata_level":"国家目录","cata_status_cn":"1","cata_title":"案例库信息","open_condition":"不开放","org_name":"北京市","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"依申请","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"AB8008\/000001","cata_id":"004ece43-bdb7-11e9-96db-450520f842af","cata_level":"市级目录","cata_status_cn":"6","cata_title":"上海市突发疾病控制快速检索文献服务支撑平台","open_condition":null,"org_name":"上海市预防医学研究院","parent_cata_id":"","public_type":"","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":null,"shared_type":"3"},
{"bus_type":"","cataLogGroups":[],"cata_code":"00247017X\/000709","cata_id":"004fe4b6-ef3e-4358-a9e1-a2c518542f6a","cata_level":"市级目录","cata_status_cn":"6","cata_title":"调研反馈信息","open_condition":"依申请开放","org_name":"上海市奉贤区人民政府办公室","parent_cata_id":"","public_type":"文件下载","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"依申请共享","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"307021022100\/000009","cata_id":"00524fd4105a4a5e9a5600143b094639","cata_level":"国家目录","cata_status_cn":"1","cata_title":"湖南省_监管对象_个体工商户网站或网店信息","open_condition":"有条件共享","org_name":"湖南省","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"有条件共享","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"303084122404\/000001","cata_id":"0064c722a69c4c5fa22485a748ffc54c","cata_level":"国家目录","cata_status_cn":"1","cata_title":"异常运维保障数据","open_condition":"不对社会开放。","org_name":"国务院办公厅","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"作为行政依据、工作参考，用于数据校核、业务协同等。","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"00247017X\/000750","cata_id":"006a1718-5879-4dbe-b9af-31d2bdfe5e77","cata_level":"市级目录","cata_status_cn":"6","cata_title":"视频监控表","open_condition":"依申请开放","org_name":"上海市奉贤区人民政府办公室","parent_cata_id":"","public_type":"文件下载","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"依申请共享","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"303021070100\/000003","cata_id":"006f83414838490887278b95ff4ca255","cata_level":"国家目录","cata_status_cn":"1","cata_title":"数据对账表(推送)","open_condition":"0","org_name":"商务部","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"与国办数据对接，予以共享。","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"AB8008\/000001","cata_id":"004ece43-bdb7-11e9-96db-450520f842af","cata_level":"市级目录","cata_status_cn":"6","cata_title":"上海市突发疾病控制快速检索文献服务支撑平台","open_condition":null,"org_name":"上海市预防医学研究院","parent_cata_id":"","public_type":"","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":null,"shared_type":"3"}],
"message":"调用成功","recordsTotal":23589}';
        $rows = json_decode($json,true);
        foreach ($rows['data'] as $num => $row) {
            $res[$num]['cata_code'] = $row['cata_code'];
            $res[$num]['cata_id'] = $row['cata_id'];
            $res[$num]['cata_title'] = $row['cata_title'];
            $res[$num]['org_name'] = $row['org_name'];
            $res[$num]['cata_status_cn'] = $row['cata_status_cn'];
        }
//        $res['totle'] = count($res);

        return success($res);

//        $paginator = new Pagination($res,3);
//        $page = $paginator ->render();
//        $list  = $paginator->getItem();
//
//        $this->assign('list', $list);
//        $this->assign('page', $page);
//
//        // 渲染模板输出
//        return $this->fetch('Api_Manage/source');
    }

    /*
     * 用户资源目录绑定api
     * @param  userid 用户id  cataId 资源目录id  cataName  资源目录名称  api_path api地址
     * @return
     * @author xsx
     * 2019/12/3
     */
    public function bind_api(){
        //用户登录验证
        @session_start();
        if(!isset($_SESSION['userid'])) {
            return error('请先登录');
        }

        //获取前端数据
        $data = input();
//print_r($data);die;
        //参数验证
        if(empty($data['userid']) || empty($data['cata_id'])  || empty($data['api_path'])){
            return error('绑定参数不完整');
        }

        //更新数据
        //$ddd = $this->Registered->add($data['userid'],$data['api_path'],$data['cata_id']);
        $ddd = 1;

        //执行结果判断是否操作成功
        if($ddd){
            //插入成功，绑定成功
            $rrr = $this->cataModel->bind_api($data);
            return success('绑定成功');
        }
        else{
            //插入失败，绑定失败
            return error('绑定失败，发生意外错误');
        }

    }

    /*
    * 用户资源目录查询
    * @param page 页码 pagesize每页条数
    * @return res 用户资源目录
    * @author xsx
    * 2019/12/3
    */
    public function sourceList(){
        //用户登录验证
       @session_start();
        if(!isset($_SESSION['userid'])) {
            $this->error('请先登录', 'Login/index');
        }

        $userid = $_SESSION['userid'];


        //调取普元目录接口
//        $data['page'] = $page;
//        $data['pageSize'] = 10;
//        $data['status'] = 4;
//        $data['user_id'] = $userid;
//        $url = 'http://172.26.16.2:8080/catalog/rest/services/catalogService/getCatalogDataByAll';
//        $rows = $curl_post($url,$data);
//        $rows = json_decode($rows,true);

       //模拟测试数据
        $json = '{"code":0,
        "data":[{"bus_type":"","cataLogGroups":[],"cata_code":"303084122704\/000001","cata_id":"000dc0c72c2e4062820e7bea17090730","cata_level":"国家目录","cata_status_cn":"1","cata_title":"异常运维保障数据","open_condition":"不对社会开放。","org_name":"国务院办公厅","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"作为行政依据、工作参考，用于数据校核、业务协同等。","shared_type":"2"},
        {"bus_type":"","cataLogGroups":[],"cata_code":"BA6006\/000218","cata_id":"0018a7a0-f37c-11e8-9b39-890fce5b9e18","cata_level":"市级目录","cata_status_cn":"6","cata_title":"项目信息","open_condition":"不开放","org_name":"上海市青浦区人民政府","parent_cata_id":"","public_type":"","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"申请共享","shared_type":"2"},
        {"bus_type":"","cataLogGroups":[],"cata_code":"3070010370\/000066","cata_id":"004457334c204a40ba14567fe840324f","cata_level":"国家目录","cata_status_cn":"1","cata_title":"案例库信息","open_condition":"不开放","org_name":"北京市","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"依申请","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"AB8008\/000001","cata_id":"004ece43-bdb7-11e9-96db-450520f842af","cata_level":"市级目录","cata_status_cn":"6","cata_title":"上海市突发疾病控制快速检索文献服务支撑平台","open_condition":null,"org_name":"上海市预防医学研究院","parent_cata_id":"","public_type":"","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":null,"shared_type":"3"},
{"bus_type":"","cataLogGroups":[],"cata_code":"00247017X\/000709","cata_id":"004fe4b6-ef3e-4358-a9e1-a2c518542f6a","cata_level":"市级目录","cata_status_cn":"6","cata_title":"调研反馈信息","open_condition":"依申请开放","org_name":"上海市奉贤区人民政府办公室","parent_cata_id":"","public_type":"文件下载","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"依申请共享","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"307021022100\/000009","cata_id":"00524fd4105a4a5e9a5600143b094639","cata_level":"国家目录","cata_status_cn":"1","cata_title":"湖南省_监管对象_个体工商户网站或网店信息","open_condition":"有条件共享","org_name":"湖南省","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"有条件共享","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"303084122404\/000001","cata_id":"0064c722a69c4c5fa22485a748ffc54c","cata_level":"国家目录","cata_status_cn":"1","cata_title":"异常运维保障数据","open_condition":"不对社会开放。","org_name":"国务院办公厅","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"作为行政依据、工作参考，用于数据校核、业务协同等。","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"00247017X\/000750","cata_id":"006a1718-5879-4dbe-b9af-31d2bdfe5e77","cata_level":"市级目录","cata_status_cn":"6","cata_title":"视频监控表","open_condition":"依申请开放","org_name":"上海市奉贤区人民政府办公室","parent_cata_id":"","public_type":"文件下载","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"依申请共享","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"303021070100\/000003","cata_id":"006f83414838490887278b95ff4ca255","cata_level":"国家目录","cata_status_cn":"1","cata_title":"数据对账表(推送)","open_condition":"0","org_name":"商务部","parent_cata_id":"","public_type":"在线浏览","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":"与国办数据对接，予以共享。","shared_type":"2"},
{"bus_type":"","cataLogGroups":[],"cata_code":"AB8008\/000001","cata_id":"004ece43-bdb7-11e9-96db-450520f842af","cata_level":"市级目录","cata_status_cn":"6","cata_title":"上海市突发疾病控制快速检索文献服务支撑平台","open_condition":null,"org_name":"上海市预防医学研究院","parent_cata_id":"","public_type":"","resource_type":"","resource_type_son":"","sec_propertytype":"","shared_condition":null,"shared_type":"3"}],
"message":"调用成功","recordsTotal":23589}';
        $rows = json_decode($json,true);

        //整理目录输出结果
        foreach ($rows['data'] as $num => $row) {
            $res[$num]['cata_code'] = $row['cata_code'];
            $res[$num]['cata_id'] = $row['cata_id'];
            $res[$num]['cata_title'] = $row['cata_title'];
            $res[$num]['org_name'] = $row['org_name'];
            $res[$num]['cata_status_cn'] = $row['cata_status_cn'];

        }

        //数组分页
        $paginator = new Pagination($res,10);
        $page = $paginator ->render();
        $list  = $paginator->getItem();

        $api_path = input('api_path');
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('api_path',$api_path);
        $this->assign('userid',$userid);

        // 渲染模板输出
        return $this->fetch('Api_Manage/api_bind_source');

    }







}