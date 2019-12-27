<?php


namespace app\apimanage\model;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Model;
use think\Request;
use think\Config;
use think\Paginator;
use Crasphb\Pagination;

class ApiInformation extends Model
{

    //本地测试数据库连接
    protected $connection1;

    /**
     * 构造函数
     * AbnormalController constructor.
     */
    public function __construct()
    {
        $this->connection1 = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8');
    }
    /*
    * api详情页面
    */
    public function show_api_detail($userid)
    {
        $db = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8');
       // $list1 = $db->name('dt_apimanage_api_register')->where('is_delete','1')->where('registerId',$userid)->paginate(2);
        $list = $db->name('dt_apimanage_api_register')->where('is_delete','1')->where('registerId',$userid)->select();
        $count = $db->name('dt_apimanage_api_register')->where('is_delete','1')->where('registerId',$userid)->count();
        $data =['count'=>$count,
                'list'=> $list
                ];
        return $data;
    }

    /*
     * 绑定普元的目录
     */
    public function bind_catalog($cata_id, $userid, $api_path)
    {
        $sql = "update dt_apimanage_api_register set cataId = '{$cata_id}',is_mount = '1' where registerId = '{$userid}' and api_path = '{$api_path}'";
        $res = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8')->execute($sql);
        return $res;
    }

    /*
    * api信息的编辑
    */
    public function api_modify($data,$update_set)
    {
        $sql = "update `dt_apimanage_api_register` set {$update_set} where id = '{$data['id']}'";
//        print_r($sql);die;
        $res = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8')->execute($sql);
        return $res;
    }

    /*
     * aoi信息的删除
     */
    public function api_delete($id)
    {
        $sql = "update `dt_apimanage_api_register` set is_delete = '2' where id = {$id}";
        $res = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8')->execute($sql);
        return $res;
    }

    /*
     * 新建安全组
     */
    public function add_group($data){
        $res = insert($data,'safe_group');
        return $res;
    }

    /*
     * 弹窗api详情
     */
    public function apilist($userid)
    {
        $db = Db::connect('mysql://root:Zhy172976@111.229.179.2:3306/api_manage#utf8');
        $data = $db->name('dt_apimanage_api_register')->where('registerId', $userid)->where('is_mount', 0)->where('is_delete', 1)->select();
        return $data;
    }
}