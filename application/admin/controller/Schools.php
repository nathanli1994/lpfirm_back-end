<?php
/**
 * Created by PhpStorm.
 * User: NATHAN
 * Date: 2019/2/22
 * Time: 14:49
 */

namespace app\admin\controller;


class Schools extends Base
{

    public function loadpage(){
        $student_id = input('student_id');
        $val = input('val');

        if($val == '高中申请'){
            $highschools_res = db('highschools')->select()->toArray();
            return view('schools/add', ['student_id'=>$student_id, 'highschools_res'=>$highschools_res]);
        }

        if($val == 'college申请'){
            $colleges_res = db('colleges')->distinct(true)->field('schools')->select()->toArray();
            return view('schools/college_add', ['student_id'=>$student_id, 'colleges_res'=>$colleges_res]);
        }
    }


    public function add(){
        if(request()->isPost()){
            //高中
            $data = input('post.');
            $data['create_time'] = time() - 3600*24;
            $data['total'] = $data['company_tuition_fee_fee'] + $data['company_fee_service_fee'] + $data['company_fee_apply_fee'];
            $data['have_append'] = 0;
            $customer = db('customer')->where('id','=',$data['student_id'])->find();
            $data['customer_name'] = $customer['name'];
            db('schools')->insert($data);
            $this->redirect('performance/personal_performance');
        }
    }


    public function college_add(){
        if(request()->isPost()){
            //college
            $data = input('post.');
            $data['create_time'] = time() - 3600*24;
            $customer = db('customer')->where('id','=',$data['student_id'])->find();
            $data['customer_name'] = $customer['name'];

            if(key_exists('append_info',$data)){
                foreach ($data['append_info'] as $k=>$v){
                    $arr_keys[] = $k;
                }

                for($i=0;$i<count($data['append_info']['append_language_or_class']);$i++){
                    $arr_vals = array(
                        $data['append_info']['append_language_or_class'][$i],
                        $data['append_info']['append_program'][$i],
                        $data['append_info']['append_open_season'][$i],
                        $data['append_info']['append_service_fee'][$i],
                        $data['append_info']['append_company_fee_service_fee'][$i],
                    );
                    $final_data[] = array_combine($arr_keys,$arr_vals);
                }
                unset($data['append_info']);
                $data['have_append'] = 1;

                foreach($final_data as $k=>$v){
                    $final_data[$k]['wenan'] = $data['wenan'];
                    $final_data[$k]['cutomer_id'] = $data['student_id'];
                    $final_data[$k]['customer_name'] = $data['customer_name'];
                    $final_data[$k]['user'] = $data['user'];
                }

            }else{
                $data['have_append'] = 0;
            }
            db('college')->insertAll($final_data);
            db('schools')->insert($data);
            $this->redirect('performance/personal_performance');
        }
    }





    public function getSchoolData(){
        if(request()->isPost()){
            $school_name = input('school_name');
            $res = db('highschools')->where('school_name','like',$school_name)->find();
        }
        return json($res);
    }

    public function getCollegeData(){
        if(request()->isPost()){
            $school_name = input('school');
            $res = db('colleges')->where('schools','like',$school_name)->select()->toArray();
            $apply_fee = $res[0]['apply_fee'];

            $opt='';
            foreach ($res as $k=>$v){
                $opt .= "<option value='{$v['program_name']}'>{$v['program_name']}</option>";
            }
            $data['apply_fee'] = $apply_fee;
            $data['opt'] = $opt;
        }
        return json($data);
    }

}

