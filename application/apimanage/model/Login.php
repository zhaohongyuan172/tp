<?php

/*
 * 登录模型
 * 2019/12/9 10:53:30
 */

namespace app\apimanage\model;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Model;
use think\Request;
use think\Config;

class Login extends  Model
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

    public function test(){
        $res = $this->connection1->query('show tables');
        return $res;
    }

    //记录登录日志
    public function log_login($insert){
        $ddd = insert($insert,'dt_apimanage_log_login');
        return $ddd;
    }


}