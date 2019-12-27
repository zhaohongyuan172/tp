<?php


namespace app\apimanage\model;
use think\Db;
use think\Model;

class Admin extends Model
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
        $this->connection1 = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8');
    }

    //查询第三方注册二次封装接口调用情况
    public function get_api(){
        $sql = "select api,count(id) as call_number from dt_apimanage_log_callencapulate GROUP BY userinformation,api order by call_number desc";
        $res = $this->connection1->query($sql);
        return $res;
    }

    //获取接口调用总次数
    public function all_call_number(){
        $sql = "select count(id) as all_call_number from dt_apimanage_log_callencapulate";
        $res = $this->connection1->query($sql);
        return $res;
    }

    //获取当日接口调用总次数
    public function today_call_number(){
       //获取时间限制
        $today  = date('Y-m-d',time());
        $start = $today . ' 00:00:00';
        $start = date('Y-m-d H:i:s',strtotime($start));
        $end = $today  . ' 23:59:59';
        $end = date('Y-m-d H:i:s',strtotime($end));

        $sql = "select count(id) as today_call_number from dt_apimanage_log_callencapulate  where '{$start}' < call_time and call_time < '{$end}'";
        $res = $this->connection1->query($sql);
        return $res;
    }

    //获取接口总量
    public function month_call_number(){
        //获取时间限制
        $month  = date('Y-m',time());
        $start = $month . '-01 00:00:00';
        $start = date('Y-m-d H:i:s',strtotime($start));
        $end =  date('Y-m-d', strtotime("$month +1 month -1 day"))  . ' 23:59:59';
        $end = date('Y-m-d H:i:s',strtotime($end));

        $sql = "select count(id) as month_call_number from dt_apimanage_log_callencapulate  where '{$start}' < call_time and call_time < '{$end}'";
        $res = $this->connection1->query($sql);
        return $res;
    }

}