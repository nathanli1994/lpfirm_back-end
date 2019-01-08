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
            db('performance')->insert($data);
            $this->redirect('performance/personal_performance');
        }

        $student_id = input('student_id');
        $student_res = db('customer')->where('id','=',$student_id)->find();
        $highschools_res = db('highschools')->select()->toArray();
        $this->assign('student_res',$student_res);
        $this->assign('highschools_res',$highschools_res);
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
}