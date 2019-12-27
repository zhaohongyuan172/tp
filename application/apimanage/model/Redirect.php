<?php
/*
 * 重定向模型
 * 2019/12/9 10:53:30
 */

namespace app\apimanage\model;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Model;
use think\Request;
use think\Config;
use think\Paginator;
use Crasphb\Pagination;

class Redirect extends Model
{
    //本地测试数据库连接
    protected $connection1;

    /**
     * 构造函数
     * AbnormalController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->connection1 = Db::connect('mysql://root:123456@192.168.83.8:3306/api_manage#utf8');
    }

    //二次封装接口
    public function encapsulation($url){
        $sql = 'select id from dt_apimanage_api_register where api_path = \'' . $url . '\'';
        $res = $this->connection1->query($sql);
        return $res;
    }

    //重定向接口,获取封装前api的url地址
    public function Api_url($id){
        $sql = 'select api_path from dt_apimanage_api_register where id = ' . $id;
        $url = ($this->connection1->query($sql))[0]['api_path'];
        $sql_api_new = "select api_new from dt_apimanage_api_encapsulate where api_old = '{$url}'";
        $api_new = ($this->connection1->query($sql_api_new))[0]['api_new'];
        return $api_new;
    }

    //获取api注册时的信息
    public function get_api_type($code){
        $sql = "select api_desc,agreement,request_mode,back_type,param_info from dt_apimanage_api_register where code = '$code'";
        $res = $this->connection1->query($sql);
        return $res;
    }


    //通过url查询数据库获取访问iph和访问限制
    public function Api_ip_max($url){
        $sql = "select systemId,reqRate from dt_apimanage_api_encapsulate_limit where api = '" . $url . "'";
        $rows = $this->connection1->query($sql);
        return $rows;

    }

    //将获取到的封装信息更新至数据库
    public function limit($data){
        $sql = "update dt_apimanage_api_encapsulate_limit" .
            " set code = '$data[code]'," .
            "lifetime = '$data[lifetime]'," .
            "applyPeopleId = '$data[applyPeopleId]'," .
            "systemId = '$data[systemId]'," .
            "reqRate = '$data[reqRate]'," .
            "store_time = '$data[store_time]'" .
            " where api =  '$data[url]'" ;
        $ddd =  $this->connection1->execute($sql);
        return $ddd;
    }

    //插入token
    public function insert_token($arr){
        $sql = "select id from dt_apimanage_token where token = '{$arr['token']}'";
        $ddd = $this->connection1->query($sql);
        if(!$ddd){
            return insert($arr,'dt_apimanage_token');
        }
        return true;

    }

    //插入用户申请信息
    public function insert_user_apply($api_apply){
        $sql = "select id from dt_apimanage_api_apply where userid = '{$api_apply['userid']}' and api = '{$api_apply['api']}'";
        $ddd = $this->connection1->query($sql);
        if(!$ddd){
            return insert($api_apply,'dt_apimanage_api_apply');
        }

        return true;

    }

    //记录api接口被调用的情况
    public function recode_call_api($user,$id){
        $api = 'http://10.83.68.85:12309/project/public/Api?id=' . $id;
        $insert['userinformation'] = $user;
        $insert['api'] = $api;
        $insert['call_time'] = date('Y-m-d H:i:s',time());
        $insert['store_time'] = date('Y-m-d H:i:s',time());
        $res = insert($insert,'dt_apimanage_log_callencapulate');
        if($res){
            return true;
        }
        else{
            return false;
        }

    }


}