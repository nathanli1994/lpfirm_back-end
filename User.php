<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/17
 * Time: 15:28
 */

namespace app\admin\controller;



class User extends Base
{
    public function index(){

        $res = db('user')->select()->toArray();
        if(!empty($res)){
            $this->assign('res',$res);
        }else{
            $this->redirect('user/add');
        }
        return view();
    }


    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            $position_res = db('position')->where('name','like',$data['position_name'])->find();
            $data['sort'] = $position_res['sort'];

            $res = db('user')->insert($data);
            if($res){
                $this->redirect('user/index');
            }
        }
        //部门的展示
        $department_res = db('department')->select()->toArray();
        $position_res = db('position')->select()->toArray();
        $this->assign('department_res',$department_res);
        $this->assign('position_res',$position_res);
        return view();
    }


    public function edit(){
        $id = input('id');
        $res = db('user')->where('id','=',$id)->find();
        $position_res = db('position')->select()->toArray();
        $this->assign('res',$res);
        $this->assign('position_res',$position_res);

        if(request()->isPost()){
            $data = input('post.');
            $position_res = db('position')->where('name','like',$data['position_name'])->find();
            $data['sort'] = $position_res['sort'];
            $res = db('user')->where('id','=',$data['id'])->update($data);
            if($res){
                $this->redirect('user/index');
            }
        }

        return view();
    }


    public function delete(){
        $id = input('id');
        $res = db('user')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('user/index');
        }
    }


    public function total(){

        $user_count = db('user')->count();
        $department_count = db('department')->count();
        $position_count_total = db('position')->count();
        $this->assign('user_count',$user_count);
        $this->assign('department_count',$department_count);
        $this->assign('position_count_total',$position_count_total);


        //每个部门下有多少个职位
        $department_res = db('department')->select()->toArray();
        foreach ($department_res as $k=>$v){
            $department_name[]['department_name'] = $v['name'];//部门名称
        }
        foreach ($department_name as $k1=>$v1){
            $department_position_count[$k1]['count'] = db('position')->where('department_name','like',$v1['department_name'])->count();
            $department_position_count[$k1]['department_name'] = $v1['department_name'];
        }

        $num_arr_to_str = '';
        foreach ($department_position_count as $k2=>$v2){
            $count[] = $v2['count'];
            $num_arr_to_str = implode(',',$count);
        }
        foreach ($department_position_count as $k2=>$v2){
            $name_arr_to_str[] = $v2['department_name'];
        }
        $this->assign('department_name',$department_name);
        $this->assign('department_position_count',$department_position_count);
        $this->assign('num_arr_to_str',$num_arr_to_str);
        $this->assign('name_arr_to_str',$name_arr_to_str);
        //每个职位里面有多少人
        $position_res = db('position')->select()->toArray();
        foreach ($position_res as $k=>$v){
            $position_name[]['name'] = $v['name'];
        }
        foreach ($position_name as $k=>$v){
            $position_people[$k]['name'] = $v['name'];
            $position_people[$k]['count'] = db('user')->where('position_name','like',$v['name'])->count();
        }
        $this->assign('position_people',$position_people);
        return view();
    }



    public function showUserByPosition(){
        $position = input('position');
        $this->assign('position',$position);
        $res = db('user')->where('position_name','=',$position)->select()->toArray();
        $this->assign('res',$res);
        return view();
    }


    public function review(){
        $position_res = db('position')->select()->toArray();
        foreach ($position_res as $k=>$v){
            $position_name[]['name'] = $v['name'];
        }
        foreach ($position_name as $k=>$v){
            $position_people[$k]['name'] = $v['name'];
            $position_people[$k]['count'] = db('user')->where('position_name','like',$v['name'])->count();
        }
        $this->assign('position_people',$position_people);

        $customer = db('customer')->column('create_time');
        $business = db('business')->column('create_time');

        $this_year = (int)(date('Y'));
        $last_year = $this_year - 1;

        //拿到今年的客人
        for($c=0;$c<count($customer);$c++){
            if(date('Y',$customer[$c]) == $this_year){
                $this_year_customer[] = $customer[$c];
            }
        }
        //拿到去年的客人
        for($c=0;$c<count($customer);$c++){
            if(date('Y',$customer[$c]) == $last_year){
                $last_year_customer[] = $customer[$c];
            }
        }

        $timer1 = 0;
        for($m=1;$m<=12;$m++){
            //统计当前年份，每个月有多少客人
            for ($a=0;$a<count($this_year_customer);$a++){
                if(date('m',$this_year_customer[$a]) == $m){
                    $timer1 += 1;
                }
            }
            $res1[$m]['this_year_people_per_month'] = $timer1;
            $res1[$m]['month'] = $m;
            $timer1 = 0;
        }

        $timer2 = 0;
        for($m=1;$m<=12;$m++){
            //统计去年，每个月有多少客人
            for ($c=0;$c<count($last_year_customer);$c++){
                if(date('m',$last_year_customer[$c]) == $m){
                    $timer2 +=1;
                }
            }
            $res1[$m]['last_year_people_per_month'] = $timer2;
            $timer2 = 0;
        }

        $this->assign('res1',$res1);
        $this->assign('this_year',$this_year);
        $this->assign('last_year',$last_year);



        //拿到今年的业务
        for($c=0;$c<count($business);$c++){
            if(date('Y',$business[$c]) == $this_year){
                $this_year_business[] = $business[$c];
            }
        }

        //拿到去年的业务
        for($c=0;$c<count($business);$c++){
            if(date('Y',$business[$c]) == $last_year){
                $last_year_business[] = $business[$c];
            }
        }


        $timer_business = 0;
        for($m=1;$m<=12;$m++){
            //统计当前年份，每个月有多少客人
            for ($a=0;$a<count($this_year_business);$a++){
                if(date('m',$this_year_business[$a]) == $m){
                    $timer_business += 1;
                }
            }
            $res2[$m]['this_year_people_per_month'] = $timer_business;
            $res2[$m]['month'] = $m;
            $timer_business = 0;
        }


        $timer_business2 = 0;
        for($m=1;$m<=12;$m++){
            //统计去年，每个月有多少客人
            for ($c=0;$c<count($last_year_business);$c++){
                if(date('m',$last_year_business[$c]) == $m){
                    $timer_business2 +=1;
                }
            }
            $res2[$m]['last_year_people_per_month'] = $timer_business2;
            $timer_business2 = 0;
        }

        $this->assign('res2',$res2);


        //计算每天的钱
        $today =strtotime(date('Y-m-d',time()));

        $business_count = db('business')->whereTime('create_time',$today)->count();

        if($business_count <= 1){
            $business = db('business')->whereTime('create_time',$today)->find();
            $result[] = $business;
        }else{
            $result = db('business')->whereTime('create_time',$today)->select()->toArray();
        }

        foreach ($result as $k=>$v){
            if(empty($v['amount'])){
                $v['amount'] = 0;
            }
            if(empty($v['post_fee'])){
                $v['post_fee'] = 0;
            }
            if(empty($v['goverment_fee'])){
                $v['goverment_fee'] = 0;
            }
            if(empty($v['explain_fee'])){
                $v['explain_fee'] = 0;
            }
            if(empty($v['service_fee'])){
                $v['service_fee'] = 0;
            }
            if(empty($v['subservice_name'])){
                $v['subservice_name'] = "业务未找到";
            }


            if($v['subservice_name'] == '大学申请' || $v['subservice_name'] == 'college申请'){
                $total_ideal_income[] = $v['amount'] + $v['post_fee'] + $v['goverment_fee'] + $v['explain_fee'];
            }else{
                $total_ideal_income[] = $v['service_fee'] + $v['post_fee'] + $v['goverment_fee'] + $v['explain_fee'];
            }
            $real_service_fee_income[] = $v['amount'];
        }

        $sum_ideal = array_sum($total_ideal_income);
        $sum_real = array_sum($real_service_fee_income);
        $short = $sum_ideal - $sum_real;
        $this->assign('sum_ideal',$sum_ideal);
        $this->assign('sum_real',$sum_real);
        $this->assign('short',$short);


        //计算月收入
        //业务部分
        $month = date('Y年n月',time());

        $month_business_res = db('business')->whereTime('create_time','month')->select()->toArray();
        foreach ($month_business_res as $k=>$v){
            if(empty($v['amount'])){
                $v['amount'] = 0;
            }
            if(empty($v['post_fee'])){
                $v['post_fee'] = 0;
            }
            if(empty($v['goverment_fee'])){
                $v['goverment_fee'] = 0;
            }
            if(empty($v['explain_fee'])){
                $v['explain_fee'] = 0;
            }
            if(empty($v['service_fee'])){
                $v['service_fee'] = 0;
            }
            if(empty($v['service_fee'])){
                $v['subservice_name'] = $v['service_fee_change_reason'];
            }


            if($v['subservice_name'] == '大学申请' || $v['subservice_name'] == 'college申请'){
                $month_income[] = $v['amount'] + $v['post_fee'] + $v['goverment_fee'] + $v['explain_fee'];
            }else{
                $month_income[] = $v['service_fee'] + $v['post_fee'] + $v['goverment_fee'] + $v['explain_fee'];
            }
        }
        $sum_month_income = array_sum($month_income);
        $this->assign('month',$month);
        $this->assign('sum_month_income',$sum_month_income);
        return view();
    }

}