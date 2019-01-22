<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/12/18
 * Time: 12:01
 */

namespace app\admin\controller;


class Performance extends Base
{

    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            $data['create_time'] = time();
            $data['edit_time'] = time();
            if(key_exists('extra_highschools',$data)){
                $arr_keys = array();
                foreach ($data as $k=>$v){
                    $arr_keys[] = $k;
                }
                unset($arr_keys[14]);

                for($i=0;$i<count($data['extra_highschools']['extra_schools']);$i++){
                    $arr_vals = array(
                        $data['wenan'],
                        $data['contact_with_school'],
                        $data['extra_highschools']['extra_schools'][$i],
                        $data['extra_highschools']['extra_apply_fee'][$i],
                        $data['extra_highschools']['extra_tuition_fee'][$i],
                        $data['extra_highschools']['extra_total'][$i],
                        $data['extra_highschools']['extra_grade'][$i],
                        $data['extra_highschools']['extra_credit'][$i],
                        $data['extra_highschools']['extra_enroll_date'][$i],
                        $data['status'],
                        $data['accept_date'],
                        $data['student_id'],
                        $data['customer_name'],
                        $data['user'],
                        $data['create_time'],
                    );
                    $final_data[] = array_combine($arr_keys,$arr_vals);
                }
                unset($data['extra_highschools']);
                $final_data[] = $data;
                foreach ($final_data as $k=>$v){
                    $final_data[$k]['extra_highschools'] = 1;
                    $final_data[$k]['edit_time'] = time();
                }
                db('performance')->insertAll($final_data);
                $this->redirect('performance/personal_performance');
            }else{
                $data['extra_highschools'] = 0;
                db('performance')->insert($data);
                $this->redirect('performance/personal_performance');
            }
        }

        $student_id = input('student_id');
        $student_res = db('customer')->where('id','=',$student_id)->find();
        $this->assign('student_res',$student_res);
//        $highschools_res = db('highschools')->select()->toArray();
//        $this->assign('highschools_res',$highschools_res);
        return view();
    }


    public function personal_performance(){
        $user = session('name');
        $count = db('performance')->where('user','like',$user)->count();
        if($count ==0){
            $this->redirect('customer/personal_customer');
        }else{
            if($count ==1){
                $res = db('performance')->where('user','like',$user)->find();
                $performance_res[] = $res;
            }else{
                $performance_res = db('performance')->where('user','like',$user)->select()->toArray();
            }
        }


        $this->assign('performance_res',$performance_res);
        return view();
    }


    public function index(){

        $count = db('performance')->select()->count();
        if($count ==0){
            $this->redirect('customer/index');
        }else{
            if($count ==1){
                $res = db('performance')->find();
                $performance_res[] = $res;
            }else{
                $performance_res = db('performance')->select()->toArray();
            }
        }

        $this->assign('performance_res',$performance_res);
        return view();

    }



    public function performance_list(){

        $user = session('name');
        $count = db('performance')->where('user','like',$user)->count();
        if($count ==1){
            $performance = db('performance')->where('user','like',$user)->find();
            $performance_res[] = $performance;
        }else{
            $performance_res = db('performance')->where('user','like',$user)->select()->toArray();
        }
        $this->assign('performance_res',$performance_res);
        return view();
    }



    public function delete(){
        $id = input('id');
        db('performance')->where('id','=',$id)->delete();
        $this->redirect('performance/personal_performance');
    }



    public function decision_set(){
        $id = input('id');
        db('performance')->where('id','=',$id)->update(['decision'=>1,'edit_time'=>time()]);
        $this->redirect('performance/personal_performance');
    }

    public function decision_cancel(){
        $id = input('id');
        db('performance')->where('id','=',$id)->update(['decision'=>0,'edit_time'=>0]);
        $this->redirect('performance/personal_performance');
    }
}