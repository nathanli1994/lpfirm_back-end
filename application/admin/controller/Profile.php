<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/17
 * Time: 16:29
 */

namespace app\admin\controller;


class Profile extends Base
{

    public function index(){
        /*
         * 在不考虑权限的前提下，每个人看到每个人的个人统计（按月计算）
         *      1.客人基础信息里面不再允许设置导单，导单选择在客户修改时，做添加业务操作才可选，谁添加的就是谁的业务
         */
        $name = session('name');
        //这里只考虑了在添加的时候就设置好，但没有考虑如果在当月一开始没有设置，后续在其他月补充接单人的情况
        //当月业务总量
        $total = db('business')->where('user|export_to','like',$name)->whereTime('create_time','month')->count();
        $total_display = db('business')->where('user|export_to','like',$name)->whereTime('create_time','month')->order('create_time desc')->select()->toArray();
        $this->assign('total',$total);$this->assign('total_display',$total_display);
        //自己的单，不是导入也不是导出
        $self = db('business')->where('user','like',$name)->where('is_export','=',0)->whereTime('create_time','month')->count();
        $self_display = db('business')->where('user','like',$name)->where('is_export','=',0)->whereTime('create_time','month')->order('create_time desc')->select()->toArray();
        $this->assign('self',$self);$this->assign('self_display',$self_display);
        //别人给的单
        $import = db('business')->where('export_to','like',$name)->whereTime('create_time','month')->count();
        $import_display = db('business')->where('export_to','like',$name)->whereTime('create_time','month')->order('create_time desc')->select()->toArray();
        $this->assign('import',$import);$this->assign('import_display',$import_display);
        //自己给出去的单
        $export = db('business')->where('user','like',$name)->where('is_export','=',1)->whereTime('create_time','month')->count();
        $export_display = db('business')->where('user','like',$name)->where('is_export','=',1)->whereTime('create_time','month')->order('create_time desc')->select()->toArray();
        $this->assign('export',$export);$this->assign('export_display',$export_display);

        return view();
    }


    public function edit(){

    }
}