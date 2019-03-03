<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/14
 * Time: 22:56
 */

namespace app\admin\controller;


class Index extends Base
{

    public function index(){
        $name = session('name');
        $duty = session('duty');

        /*
         * 1.统计数量
         */
        //个人客户统计
        if($duty != '前台'){
            $customer_count = db('customer')->where('user|export_to|wenan','like',$name)->count();
        }else{
            $customer_count = db('customer')->count();
        }
        if($customer_count != 0){
            $this->assign('customer_count',$customer_count);
        }else{
            $this->assign('customer_count',0);
        }
        //个人业务统计
        if($duty != '前台'){
            $business_count = db('business')->where('user|export_to|wenan','like',$name)->count();
        }else{
            $business_count = db('business')->count();
        }
        if($business_count != 0){
            $this->assign('business_count',$business_count);
        }else{
            $this->assign('business_count',0);
        }
        //个人业绩统计（高中申请）
        if($duty != '前台'){
            $performance_count = db('performance')->where('user|wenan','like',$name)->count();
        }else{
            $performance_count = db('performance')->count();
        }
        if($performance_count != 0){
            $this->assign('performance_count',$performance_count);
        }else{
            $this->assign('performance_count',0);
        }


        /*
         * 2.Statistic Chart
         *      1.按月份统计当年业务d1
         *      2.按月份计算当年金额d2
         */
        //d1部分
        //获取个人所有业务，记为$business
        $business = db('business')->where('user|export_to|wenan','like',$name)->column('create_time');
        if(empty($business)){
            $this->assign('business',$business);
        }
        //统计当前年份业务总量
        $this_year = (int)(date('Y'));
        for($c=0;$c<count($business);$c++){
            //是当前年份业务，则存储到$this_year_business
            if(date('Y',$business[$c]) == $this_year){
                $this_year_business[] = $business[$c];
            }
        }

        if(empty($this_year_business)){
            $this->redirect('customer/personal_customer');
        }
        //根据当前年份业务总量，来统计每个月有多少业务
        $counter =0;
        for($m=1;$m<=12;$m++){
            for ($a=0;$a<count($this_year_business);$a++){
                //统计出每个月有多少业务，m作为key，表示月份
                if(date('m',$this_year_business[$a]) == $m){
                    $counter += 1;
                }
            }
            $res[$m] = $counter;
            $counter = 0;
        }
        $this->assign('res',$res);


        //d2部分
        for($m=1;$m<=12;$m++){
            for ($a=0;$a<count($this_year_business);$a++){
                if(date('m',$this_year_business[$a]) == $m){
                    //每个月业务的服务费，m作为key，表示月份,得到一个三维数组
                    $service_fee_by_month[$m][] = db('business')->where('user|export_to|wenan','like',$name)->where('create_time','=',$this_year_business[$a])->column('service_fee');
                    //统计有多少个数组，需要12个数组,代表12个月份
                    $count_month_number = count($service_fee_by_month);
                    $short_month = 12-$count_month_number;
                    if($short_month != 0){
                        for($i=$count_month_number+1;$i<=12;$i++){
                            $service_fee_by_month[$i][][] = 0;
                        }
                    }
                }else{
                    $service_fee_by_month[$m][][] = 0;
                }
            }
        }


        foreach ($service_fee_by_month as $k=>$v){
            foreach ($v as $k1=>$v1){
                for ($i=0;$i<count($v1);$i++){
                    //记录每个月的每条记录的业务金额，是一个二维数组
                    $price[$k][] = $v1[$i];
                }
            }
        }


        //计算每个月的业务金额总数
        foreach ($price as $k=>$v){
            $total_money_per_month[$k] = array_sum($price[$k]);
        }

        $this->assign('total_money_per_month',$total_money_per_month);


        /*
         * Todo部分
         */
        $todo_count = db('todo')->where('user','like',$name)->count();
        $this->assign('todo_count',$todo_count);
        $todo_done_count = db('todo')->where('user','like',$name)->where('is_done','=',1)->count();
        $this->assign('todo_done_count',$todo_done_count);
        if($todo_count != 0){
            $todo_res = db('todo')
                ->where('user','like',$name)
                ->where('is_done','=',0)
                ->limit(5)->select()->toArray();
        }else{
            $todo_res = [];
        }

        $this->assign('todo_res',$todo_res);



        /*
         * Recent Activity部分
         */
        $time = time();
        $business_res = db('business')->where('user|export_to|wenan|referee','like',$name)->select()->toArray();

        foreach ($business_res as $k=>$v){
            $activity[$k]['day_diff'] = (int)round(($time - $v['create_time']) / 3600 / 24, 0);
            $activity[$k]['user'] = $v['user'];
            $activity[$k]['subservice_name'] = $v['subservice_name'];
            $activity[$k]['wenan'] = $v['wenan'];
            $activity[$k]['progress'] = $v['progress'];
            $activity[$k]['customer_name'] = $v['customer_name'];
        }

        //过滤出近15天的业务
        foreach ($activity as $k=>$v){
            if($v['day_diff'] <= 15){
                $recentActivity[$k]['day_diff'] = $v['day_diff'];
                $recentActivity[$k]['user'] = $v['user'];
                $recentActivity[$k]['subservice_name'] = $v['subservice_name'];
                $recentActivity[$k]['wenan'] = $v['wenan'];
                $recentActivity[$k]['progress'] = $v['progress'];
                $recentActivity[$k]['customer_name'] = $v['customer_name'];
            }else{
                $recentActivity[0]['day_diff'] = '无';
                $recentActivity[0]['user'] = '无';
                $recentActivity[0]['subservice_name'] = '无';
                $recentActivity[0]['wenan'] = '无';
                $recentActivity[0]['progress'] = '无';
                $recentActivity[0]['customer_name'] = '无';
            }
        }
        $this->assign('recentActivity',$recentActivity);

        $performance_res = db('performance')->where('user|wenan|contact_with_school','like',$name)->select()->toArray();

        if(!empty($performance_res)){
            foreach ($performance_res as $k=>$v){
                $recentPerformance[$k]['school_name'] = $v['school_name'];
                $recentPerformance[$k]['customer_name'] = $v['customer_name'];
                $recentPerformance[$k]['credit'] = $v['credit'];
                $recentPerformance[$k]['total'] = $v['total'];
                $recentPerformance[$k]['user'] = $v['user'];
            }
        }else{
            $recentPerformance[0]['school_name'] = 0;
            $recentPerformance[0]['customer_name'] = 0;
            $recentPerformance[0]['credit'] = 0;
            $recentPerformance[0]['total'] = 0;
            $recentPerformance[0]['user'] = 0;
        }

        $this->assign('recentPerformance',$recentPerformance);



        /*
         * Actual Statistics
         */
        $total_business = count($business_res);
        $total_customer = db('customer')->where('user|export_to|wenan','like',$name)->count();
        $this->assign('total_business',$total_business);
        $this->assign('total_customer',$total_customer);


        /*s
         * Projects Progress
         * 使用$recentActivity即可
         */
        return view();
    }



    public function logout(){
        session(null);
        $this->redirect('login/index');
    }


    public function password(){

        if(request()->isPost()){
            $data = input('post.');
            db('user')->where('name','like',$data['name'])->update($data);
            $this->redirect('index/index');
        }
        $name = session('name');
        $res = db('user')->where('name','like',$name)->find();
        $this->assign("res",$res);
        return view();
    }



    //todo相关操作
    public function recordTodo(){
        if(request()->isPost()){
            $session_name = input('session_name');
            $input_val = input('input_val');
            $data = [
                'user'=>$session_name,
                'create_time'=>time() - 3600*24,
                'record'=>$input_val,
                'is_done'=>0,
            ];
            $res = db('todo')->insert($data);
            if($res){
                return json(1);
            }else{
                return json(0);
            }
        }
    }
    public function todoFinish(){
        $id = input('id');
        db('todo')->where('id','=',$id)->update(['is_done'=>1]);
        $this->redirect('index/index');
    }
    public function todoDel(){
        $id = input('id');
        db('todo')->where('id','=',$id)->delete();
        $this->redirect('index/index');
    }
}