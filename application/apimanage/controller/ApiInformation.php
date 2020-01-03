<?php
/*
 * Api详情控制器
 * 2019/12/3 16:58:03
 */

namespace app\apimanage\controller;

use app\apimanage\model\ApiInformation as ApiInformationModel;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;


class ApiInformation extends Controller
{

    private $CataInformation;
    private $Registered;

    //初始化配置
    protected $apiInformationModel;

    /**
     * 构造函数
     * AbnormalController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->apiInformationModel = new \app\apimanage\model\ApiInformation;
    }

//    protected function _initialize()
//    {
//        parent::_initialize();
//        $this->CataInformation = new CataInformation;
//        $this->Registered = new Registered;
//    }

    /*
         * api详情页面
         */
    public function show_api_detail()
    {
        $userid = input();
        $userid = $userid['userid'];
        //字段名称:序号id，API名称api_name，API地址api_path，API描述api_desc，注册时间registerDate，目录绑定状态is_mount
        //$user_id = session('userid');
        //$user_id = 1560014588;
        //登录验证
        //$this->check_user_id($user_id);
//        $sql = "select `id`,`api_name`,`api_path`,`api_desc`,`registerDate`,`is_mount` from `api` limit $pageSize offset $page";
       // @session_start();
      //  $userid = $_SESSION['userid'];
        $list1 =$this->apiInformationModel->show_api_detail($userid);
        if (!empty($list1)) {
            return success($list1);
        } else {
            return error();
        }
        //$list1 = $this->apiInformationModel->show_api_detail($userid);
        /*$page1 = $list1->render();
        // 模板变量赋值
        $this->assign('list', $list1);
        $this->assign('page', $page1);
        // 渲染模板输出
        return $this->fetch('Api_Manage/api');*/
    }



    /*
     * 选择未绑定api，从普元资源目录中传入cataId和cataName,更新到api表中
     */
    public function bind_catalog()
    {
        $data = input();
        //var_dump($data);die;
        $ddd = $this->apiInformationModel->bind_catalog($data['cata_id'],$data['userid'],$data['api_path']);
        if ($ddd) {
            //$this->Registered->add($data['id'],$data['api_path']);
            return success('绑定成功');
        } else {
            return error('绑定失败');
        }
    }



    /*
     * 新建安全组
     */
    public function add_group(){
        $data = input();
        $store_time = date("Y-m-d H:i:s",time());
        $data['store_time'] = $store_time;
        if (empty($data['userid']) || empty($data['type']) || empty($data['name']) || empty($data['ip'])) {
            return error();
        }
        $ddd = $this->apiInformationModel->add_group($data);
        if($ddd){
            //插入成功，返回成功
            return success();
        }
        else{
            //插入失败，返回失败
            return error();
        }
    }



    /*
     * api详情页面
     */
    public function one_detail()
    {
        //登录验证
        //$this->check_user_id($user_id);
        $id = input();
        $db = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8');
        $list1 = $db->name('dt_apimanage_api_register')->where('id',$id['id'])->select();
        if ($list1) {
            return success($list1);
        } else {
            return error();
        }
    }
    /*
     * api信息的编辑
     */
    public function api_modify($data)
    {
//        $data = input();
        if (empty($data['id'])) {
            return error();
        } else if(!empty($data) && empty($data['id'])){
            return error();
        }
        $res = array_keys($data);
        $update_values ='';
        foreach ($res as $key => $value) {
            if ($value != 'id') {
                $update_v = $value.'';
                $update_values .= $value . "= '{$data[$update_v]}',";
            }
        }

        $update_set = rtrim($update_values,',');
        $ddd =  $this->apiInformationModel->api_modify($data,$update_set);
        //执行结果判断是否操作成功
        if($ddd) {
            //插入成功，返回成功
            return success();
        }
        else {
            //插入失败，返回失败
            return error();
        }
    }

    /*
     *提交修改信息给普元
     * @param api_path,绑定api地址,userid 用户id
     */
    public function puyuanUpdate()
    {
        $param = input();
        if ($param['is_mount'] == 0) {
            $this->api_modify($param);
        } else {
            $api_path = $param['api_path'];
            $sql_url = "select api_new from dt_apimanage_api_encapsulate_limit where api_old = '{$api_path}'";
            $url = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql_url))[0]['api_new'];

            $sql_code = "select code from dt_apimanage_api_register where id ='{$param['id']}'";
            $code    =  (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql_code))[0]['id'];

             $data['id'] = $param['id'];
            $data['code'] = $code;
            $data['cnName'] = $param['chnName'];
            $data['cataId'] = $param['cataId'];
            $data['serviceDesc'] = $param['serviceDesc'];
            $data['version'] = $param['version'];
            $data['registerId'] = $param['registerId'];
            $data['register'] = $param['register'];
            $data['deptId'] = $param['deptId'];
            $data['dept'] = $param['dept'];
            $data['registerDate'] = $param['registerDate'];
            //publishDate validityDate status optype isPublic requireFile url isDBService DBColumn
            $data['publishDate'] = $param['publishDate'];
            $data['validityDate'] = $param['validityDate'];
            $data['status'] = '8';
            $data['optype'] = 'update';
            $data['isPublic'] = $param['isPublic'];
            $data['requireFile'] = $param['requireFile'];
            $data['url'] = $url;
            $data['isDBService '] = 'false';
            $data['DBColumn'] = ' ';

            $data_js = json_encode($data);

            $res = curl_post('http://10.81.67.54:8080/catalog/rest/services/catalogService/mountService', $data_js);

            $code = json_decode($res)['code'];

            if (!$code) {
                $this->api_modify($data);
            } else {
                return error('编辑失败');
            }
        }
    }
    /*
     * api信息的删除
     */
    public function api_delete($id)
    {
//        $data = input();
        if (!isset($id)) {
            return error();
        }
        $ddd = $this->apiInformationModel->api_delete($id);
        //执行结果判断是否操作成功
        if ($ddd) {
            //插入成功，返回成功
            return success();
        } else {
            //插入失败，返回失败
            return error();
        }
    }

    /*
     * 提交删除信息给普元数据库
     */
    public function puyuanDelete()
    {
        $param = input();
        if ($param['is_mount'] == 0) {
            $this->api_delete($param['id']);
        } else {
            $id = $param['id'];
            $api_path = $param['api_path'];
            $sql_url = "select id,chnName,cataId,serviceDesc,version,registerId,register,deptId,dept,registerDate,publishDate,validityDate,isPublic,requireFile from dt_apimanage_api_register where id = '{$id}'";
            $params = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql_url));

            $sql_url = "select api_new from dt_apimanage_api_encapsulate_limit where api_old = '{$api_path}'";
            $url = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->query($sql_url))[0]['api_new'];

            foreach ($params[0] as $key => $value) {
                $data[$key] = $value;
            }

            $data['status'] = '8';
            $data['optype'] = 'delete';
            $data['url'] = $url;
            $data['isDBService '] = 'false';
            $data['DBColumn'] = ' ';

            $data_js = json_encode($data);

            $res = curl_post('http://10.81.67.54:8080/catalog/rest/services/catalogService/mountService', $data_js);
            $code = json_decode($res)['code'];

            if (!$code) {

                $sql_url1 = "delete from dt_apimanage_api_encapsulate_limit where id = '{$id}'";
                $ddd1 = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->execute($sql_url1));

                $sql_url2 = "delete from dt_apimanage_api_encapsulate where id = '{$id}'";
                $ddd2 = (Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8')->execute($sql_url2));
                $this->api_delete($id);
            } else {
                return error();
            }
        }
    }
    /*
     * 弹框api详情页面
     */
    public function apilist()
    {
        //字段名称:序号id，API名称api_name，API地址api_path，API描述api_desc，注册时间registerDate，目录绑定状态is_mount
//        @session_start();
        $data = input();
        $userid = $data['userid'];
        if(!$userid){
            return error('缺少参数!');
        }
        //$userid = 'SFGWBM1';
        //登录验证
        //$this->check_user_id($user_id);
//        $sql = "select `id`,`api_name`,`api_path`,`api_desc`,`registerDate`,`is_mount` from `api` limit $pageSize offset $page";
        $data['list'] = $this->apiInformationModel->apilist($userid);
//        print_r($list);die;
        if ($data) {
            return success($data);
        } else {
            return error();
        }
//        $page = $list->render();
//        $cata_id = input('cata_id');
//        $this->assign('list', $list);
//        $this->assign('page', $page);
//        $this->assign('cata_id',$cata_id);
//        $this->assign('userid',$userid);
//
//        // 渲染模板输出
//        return $this->fetch('Api_Manage/source_bind_api');
    }

}