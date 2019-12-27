<?php
/*
 * 资源目录信息模型
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



class CataInformation extends  Model
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

    public function bind_api($data){
        $sql = "update dt_apimanage_api_register set cataId = '{$data['cata_id']}',is_mount = '1' where registerId = '{$data['userid']}' and api_path = '{$data['api_path']}'";
        //echo $sql;die;
        $ddd = $this->connection1->execute($sql);
        return $ddd;
    }
}