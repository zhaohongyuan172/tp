<?php


namespace app\apimanage\controller;
use think\Controller;
use think\Db;

class Admin extends Controller
{
    //初始化配置
    protected $adminModel;

    /**
     * 构造函数
     * AbnormalController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->adminModel = new \app\apimanage\model\Admin;
    }

    /*
     * 获取接口调用情况
     * @param
     * @return res 调用情况列表
     * @author xsx
     * @time 2019/12/12 11:34
     */
    public function get_api(){
        //查询第三方注册二次封装接口调用情况
        $register = $this->adminModel->get_api();

        //查询任务下发生成的接口的调用情况
        //$issued = '';

        $res['list'] = $register;

        //排序
        //取出数组中call_number的一列，返回一维数组
        $timeKey =  array_column($res['list'],'call_number');
        //排序，根据$call_number 排序
        array_multisort($timeKey,SORT_DESC,$res['list']);


        //获取当日接口调用总次数
        $res['today_call_number'] = ($this->adminModel->today_call_number())[0]['today_call_number'];

        //获取当月接口调用总次数
        $res['month_call_number'] = ($this->adminModel->month_call_number())[0]['month_call_number'];

        //获取接口调用总次数
        $res['all_call_number'] = ($this->adminModel->all_call_number())[0]['all_call_number'];


        return success($res);
    }

}