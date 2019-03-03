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
            $data['create_time'] = time() - 3600*24;
            $data['edit_time'] = time() - 3600*24;

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
                        $data['edit_time'],
                    );
                    $final_data[] = array_combine($arr_keys,$arr_vals);
                }
                unset($data['extra_highschools']);
                $final_data[] = $data;
                foreach ($final_data as $k=>$v){
                    $final_data[$k]['extra_highschools'] = 1;
                    $final_data[$k]['edit_time'] = time() - 3600*24;
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
        return view();
    }


    public function personal_performance(){
//        rewrite personal_performance based on new request
//        $user = session('name');
//
//        $count = db('performance')->where('user','like',$user)->count();
//        if($count ==0){
//            $this->redirect('customer/personal_customer');
//        }else{
//            if($count ==1){
//                $res = db('performance')->where('user','like',$user)->find();
//                $performance_res[] = $res;
//            }else{
//                $performance_res = db('performance')->where('user','like',$user)->select()->toArray();
//            }
//        }
//
//        $this->assign('performance_res',$performance_res);
//        return view();
        $user = session('name');
        $res = db('schools')->where('user','like',$user)->select()->toArray();
        if(count($res) == 0 ){
            $this->error('用户没有可查的院校记录，请先去为客户添加学校申请', 'customer/personal_customer');
        }else{
            //calculate college fees
            foreach ($res as $k=>$v){
                if($v['have_append'] == 1){
                    $extra_college_program_application = db('college')->where('cutomer_id','=',$v['student_id'])->select()->toArray();

                    foreach ($extra_college_program_application as $k1=>$v1){
                        $append_service_fee[] = $v1['append_company_fee_service_fee'];
                    }
                    $append_service_fee_total = array_sum($append_service_fee);
                    $total = $append_service_fee_total + $v['company_fee_apply_fee'] + $v['company_fee_service_fee'];
                    $res[$k]['total'] = $total;
                }
            }

            $this->assign('res',$res);
        }

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
//        $id = input('id');
//        db('performance')->where('id','=',$id)->delete();
//        $this->redirect('performance/personal_performance');
        $id = input('id');
        $res = db('schools')->where('id','=',$id)->find();
        if(key_exists("have_append",$res)){
            db('college')->where('cutomer_id','=',$res['student_id'])->delete();
            db('schools')->where('id','=',$id)->delete();
        }else{
            db('schools')->where('id','=',$id)->delete();
        }
        $this->redirect('performance/personal_performance');
    }



    public function decision_set(){
        $id = input('id');
        db('performance')->where('id','=',$id)->update(['decision'=>1,'edit_time'=>time() - 3600*24]);
        $this->redirect('performance/personal_performance');
    }

    public function decision_cancel(){
        $id = input('id');
        db('performance')->where('id','=',$id)->update(['decision'=>0,'edit_time'=>0]);
        $this->redirect('performance/personal_performance');
    }


    public function college_application(){
        $id = input('id');
        $school_name = input('school');
        $res = db('schools')->where('id','=',$id)->find();
        $append_res = db('college')->where('cutomer_id','=',$res['student_id'])->select()->toArray();

        $arr_keys = ['open_season','language_or_class','program','wenan'];

        $data[0][] = $res['open_season'];
        $data[0][] = $res['language_or_class'];
        $data[0][] = $res['program'];
        $data[0][] = $res['wenan'];

        foreach ($append_res as $k=>$v){
            $data[$k+1][] = $v['append_open_season'];
            $data[$k+1][] = $v['append_language_or_class'];
            $data[$k+1][] = $v['append_program'];
            $data[$k+1][] = $v['wenan'];
        }

        foreach ($data as $k=>$v){
            $result[] = array_combine($arr_keys,$v);
        }

        $this->assign('result',$result);
        $this->assign('school_name',$school_name);
        $this->assign('id',$id);
        return view();
    }


    public function changeProgress(){
        if(request()->isPost()){
            $id = input('id');
            $progress = input('val');
            $res = db('schools')->where('id','=',$id)->update(['progress'=>$progress]);
        }
        return json($res);
    }
}