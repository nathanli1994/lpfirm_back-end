<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/18
 * Time: 11:38
 */

namespace app\admin\controller;


class Customer extends Base
{

    public function index(){

        $res = db('customer')->select()->toArray();
        if(empty($res)){
            $this->redirect('customer/add');
        }else{
            //计算：当前日期与护照到期日相差时间
            foreach ($res as $k => $v) {
                $res[$k]['passport_due_diff'] = round((strtotime($v['passport_due']) - time() + 3600*24) / 3600 / 24);
            }

            foreach ($res as $k => $v) {
                $res[$k]['visa_due_diff'] = round((strtotime($v['visa_due']) - time() + 3600*24) / 3600 / 24);
            }

            $res = $this->count_down($res);
            $this->assign('res',$res);
        }

        return view();
    }


    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            $data['create_time'] = time() - 3600*24;
            //解决按钮开关值
            $data = $this->solve_on_off($data);
            //婚姻史
            if($data['is_marry'] == 1){
                $this->add_marriage_history($data);
                unset($data['marriage_history']);
            }else{
                unset($data['marriage_history']);
            }

            //工作史
            if($data['is_worker'] ==1){
                $this->add_work_history($data);
                unset($data['work_history']);
            }else{
                unset($data['work_history']);
            }
            //教育史
            if($data['is_education'] ==1){
                $this->add_education_history($data);
                unset($data['education_history']);
            }else{
                unset($data['education_history']);
            }

//            图片上传封装，暂时不用
//            $upload = $this->uploadFile('img','customer',100,100);
//            $data['thumb_img'] = $upload['thumb_img'];

            /*
             * 添加之前先判断客户是否存在，passport_number作为标识
             */

            $num = db('customer')->where('passport_number','=',$data['passport_number'])->count();
            if($num >0){
                $this->error('客户已存在','customer/index');
            }else{
                $res = db('customer')->insert($data);
                if($res){
                    $this->redirect('customer/personal_customer');
                }
            }
        }

        $sub_service = db('subservice')->select()->toArray();
        $city_res = db('cities')->select()->toArray();
        $user_res = db('user')->select()->toArray();
        foreach($user_res as $k=>$v){
            unset($user_res[3]);
            unset($user_res[4]);
        }
        $visa_res = db('visatype')->select()->toArray();
        $this->assign('visa_res',$visa_res);
        $this->assign('sub_service',$sub_service);
        $this->assign('city_res',$city_res);
        $this->assign('user_res',$user_res);
        return view();
    }



    public function delete(){
        $id = input('id');
        db('business')->where('student_id','=',$id)->delete();
        $res = db('customer')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('customer/personal_customer');
        }
    }



    public function edit(){

        $id = input('id');
        $res = db('customer')->where('id','=',$id)->find();
        $this->assign('res',$res);

        if(request()->isPost()){
            $data = input('post.');
            $data['update_time'] = time() - 3600*24;
            //解决按钮开关值
            $data = $this->solve_on_off($data);
            //婚姻史
            if($data['is_marry'] == 1){
                $this->add_marriage_history($data);
                unset($data['marriage_history']);
            }else{
                unset($data['marriage_history']);
            }

            //工作史
            if($data['is_worker'] ==1){
                $this->add_work_history($data);
                unset($data['work_history']);
            }else{
                unset($data['work_history']);
            }
            //教育史
            if($data['is_education'] ==1){
                $this->add_education_history($data);
                unset($data['education_history']);
            }else{
                unset($data['education_history']);
            }

            if(is_null($res['thumb_img'])){
                $upload = $this->uploadFile('img','customer',100,100);
                $data['thumb_img'] = $upload['thumb_img'];
            }

            $res = db('customer')->where('id','=',$data['id'])->update($data);
            if($res){
                $this->redirect('customer/personal_customer');
            }
        }

        //客人业务分配
        /*
         * volist用来便利二维数组，（也就是select的结果集）
         * 当使用find方法，得到一个一维数组结果集的时候，要先把他变成二维数组，然后再用volist输出，不然会提示illigal string offset
         */
        $count = db('business')->where('student_id','=',$id)->count();
        if($count ==1){
            $business_res = [];
            $business = db('business')->where('student_id','=',$id)->find();
            $business['update_time'] = date('Y-m-d',$business['update_time']);
            $business_res[] = $business;
        }else{
            $business_res = db('business')->where('student_id','=',$id)->order('update_time desc')->select()->toArray();

            //前端有模板函数可以使用 {$v.update_time|date="Y-m-d",###}
            foreach ($business_res as $k=>$v){
                if(!is_null($v['update_time'])){
                    $v['update_time'] = date('Y-m-d',$v['update_time']);
                    $business_res[$k]['update_time'] = $v['update_time'];
                }
            }
        }
        $this->assign('business_res',$business_res);

        //展示业绩信息
        $count = db('performance')->where('student_id','=',$id)->count();
        if($count ==1){
            $performance = db('performance')->where('student_id','=',$id)->find();
            $performance_res[] = $performance;
        }else{
           $performance_res = db('performance')->where('student_id','=',$id)->select()->toArray();
        }
        $this->assign('performance_res',$performance_res);


        //对婚史，教育，工作的展示
        $count = db('marriage')->where('customer_name','like',$res['name'])->count();
        if($count ==1){
            $marriage_res = [];
            $marriage = db('marriage')->where('customer_name','like',$res['name'])->find();
            $marriage_res[] = $marriage;
        }else{
            $marriage_res = db('marriage')->where('customer_name','like',$res['name'])->select()->toArray();
        }
        $this->assign('marriage_res',$marriage_res);

        $count = db('work')->where('customer_name','like',$res['name'])->count();
        if($count ==1){
            $work_res = [];
            $work = db('work')->where('customer_name','like',$res['name'])->find();
            $work_res[] = $work;
        }else{
            $work_res = db('work')->where('customer_name','like',$res['name'])->select()->toArray();
        }
        $this->assign('work_res',$work_res);

        $count = db('education')->where('customer_name','like',$res['name'])->count();
        if($count ==1){
            $education_res = [];
            $education = db('education')->where('customer_name','like',$res['name'])->find();
            $education_res[] = $education;
        }else{
            $education_res = db('education')->where('customer_name','like',$res['name'])->select()->toArray();
        }
        $this->assign('education_res',$education_res);



        $sub_service = db('subservice')->select()->toArray();
        $this->assign('sub_service',$sub_service);

        $city_res = db('cities')->select()->toArray();
        $this->assign('city_res',$city_res);

        $user_res = db('user')->select()->toArray();
        $this->assign('user_res',$user_res);

        $visa_res = db('visatype')->select()->toArray();
        $this->assign('visa_res',$visa_res);

        return view();
    }







    //original replacement "($1) $2-$3"
    public function format_phone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        if(strlen($phone) == 10){
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/","$1$2$3",$phone);
        }
    }


    public function getSub(){

        if(request()->isPost()){
            $sub_service = input('sub_service');
            $sub_service_res = db('subservice')->where('name','like',$sub_service)->find();
            $service_res = db('service')->where('has_subservice','=',$sub_service_res['pid'])->find();

            $input = '';
            $input .= "<input class='form-control' type='text' name='service' value='{$service_res['name']}'/>";
        }
        return json($input);
    }


    public function getProvince(){
        if(request()->isPost()){
            $city = input('city');

            $city_info = db('cities')->where('name','like',$city)->find();
            $province_info = db('provinces')->where('id','=',$city_info['province_id'])->find();

//            $opt = '';
//            $opt .= "<option value='{$province_info['id']}'>{$province_info['name']}</option>";
            $input = '';
            $input .= "<input class='form-control' type='text' name='province' value='{$province_info['name']}'/>";

        }
        return json($input);
    }

    public function getRegion(){
        if(request()->isPost()){
            $province = input('province');
            $province_info = db('provinces')->where('name','like',$province)->find();
            $region_info = db('regions')->where('id','=',$province_info['region_id'])->find();

//            $opt = '';
//            $opt .= "<option value='{$region_info['id']}'>{$region_info['name']}</option>";
            $input = '';
            $input .= "<input class='form-control' type='text' name='region' value='{$region_info['name']}'/>";
        }
        return json($input);
    }


    /*
     * 解决额外信息的补充
    */
    public function add_marriage_history($data){
        if($data['is_marry'] == 1){
            $marry_history = [];

            for($i=0;$i<count($data['marriage_history']['pre_spouse_name']);$i++){
                $new_key = array(
                    "customer_name",
                    "spouse_name",
                    "marriage_status",
                    "is_citizen",
                    "marry_date",

                    "pre_spouse_name",
                    "pre_birthday",
                    "marriage_period"
                );
                $new_value = array(
                    $data['name'],
                    $data['marriage_history']['spouse_name'],
                    $data['marriage_history']['marriage_status'],
                    $data['marriage_history']['is_citizen'],
                    $data['marriage_history']['marry_date'],

                    $data['marriage_history']['pre_spouse_name'][$i],
                    $data['marriage_history']['pre_birthday'][$i],
                    $data['marriage_history']['marriage_period'][$i]
                );
                $marry_history[] = array_combine($new_key,$new_value);
            }
        }
        db('marriage')->insertAll($marry_history);
    }
    public function add_work_history($data){
        if($data['is_worker'] == 1){
            $work_history = [];

            for($i=0;$i<count($data['work_history']['name']);$i++){
                $new_key = array(
                    "customer_name",
                    "name",
                    "address",
                    "time",
                    "position"
                );
                $new_value = array(
                    $data['name'],
                    $data['work_history']['name'][$i],
                    $data['work_history']['address'][$i],
                    $data['work_history']['time'][$i],
                    $data['work_history']['position'][$i]
                );
                $work_history[] = array_combine($new_key,$new_value);
            }
        }
        db('work')->insertAll($work_history);
    }
    public function add_education_history($data){
        if($data['is_education'] == 1){
            $education_history = [];

            for($i=0;$i<count($data['education_history']['name']);$i++){
                $new_key = array(
                    "customer_name",
                    "name",
                    "address",
                    "time",
                    "program"
                );
                $new_value = array(
                    $data['name'],
                    $data['education_history']['name'][$i],
                    $data['education_history']['address'][$i],
                    $data['education_history']['time'][$i],
                    $data['education_history']['program'][$i],
                );
                $education_history[] = array_combine($new_key,$new_value);
            }
        }
        db('education')->insertAll($education_history);
    }


    public function personal_customer(){
        $name = session('name');
        $res = db('customer')->where('user|export_to|wenan','like',$name)->select()->toArray();
        if(empty($res)){
            $this->redirect('customer/add');
        }else{
            //计算：当前日期与护照到期日相差时间
            foreach ($res as $k => $v) {
                $res[$k]['passport_due_diff'] = (int)round((strtotime($v['passport_due']) - time() + 3600*24) / 3600 / 24);
            }

            foreach ($res as $k => $v) {
                $res[$k]['visa_due_diff'] = (int)round((strtotime($v['visa_due']) - time() + 3600*24) / 3600 / 24);
            }

            $res = $this->count_down($res);

            $this->assign('res',$res);
        }


        return view();
    }



    public function experience_del(){
        $category_id = input('category_id');
        $id = input('id');
        if($category_id == 1){
            $res = db('education')->where('id','=',$id)->delete();
        }elseif ($category_id ==2){
            $res = db('marriage')->where('id','=',$id)->delete();
        }else{
            $res = db('work')->where('id','=',$id)->delete();
        }

        if($res){
            $this->success();
        }
    }


    public function education_edit(){

        if(request()->isPost()){
            $data = input('post.');
            db('education')->where('id','=',$data['id'])->update($data);
            $this->redirect('customer/index');
        }
        $id = input('id');
        $res = db('education')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view('customer/education_edit');
    }
    public function work_edit(){

        if(request()->isPost()){
            $data = input('post.');
            db('work')->where('id','=',$data['id'])->update($data);
            $this->redirect('customer/index');
        }
        $id = input('id');
        $res = db('work')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view('customer/work_edit');
    }


    public function PassportCheck(){
        if(request()->isPost()){
            $passport_num = input('passport_num');
            $count = db('customer')->where('passport_number','=',$passport_num)->count();
            if($count ==1){
                $res = db('customer')->where('passport_number','=',$passport_num)->find();
            }else{
                $res = 0;
            }
        }
        //如果找到了，则是信息，否则返回0
        return json($res);
    }
	
	
	public function UciCheck(){
        if(request()->isPost()){
            $uci_num = input('uci_num');
            $count = db('customer')->where('uci','=',$uci_num)->count();
            if($count ==1){
                $res = db('customer')->where('uci','=',$uci_num)->find();
            }else{
                $res = 0;
            }
        }
        //如果找到了，则是信息，否则返回0
        return json($res);
    }



    public function vertify(){
        $id = input('id');
        $name = input('name');
        db('customer')->where('id','=',$id)->update(['vertified_by'=>$name]);
        $this->redirect('customer/personal_customer');
    }



    public function count_down($res){
        foreach ($res as $k=>$v){
            //0<= xxx <=180  提醒剩余天数 180天倒计时
            if(0<=$v['passport_due_diff'] && $v['passport_due_diff'] <= 180){
                $res[$k]['passport_count_down'] = $v['passport_due_diff'];
            }
            //0<= xxx <=90  提醒剩余天数 90天倒计时
            if(0<=$v['visa_due_diff'] && $v['visa_due_diff'] <= 90){
                $res[$k]['visa_count_down'] = $v['visa_due_diff'];
            }

            //-90<= xxx <0  护照过期天数
//            if(-90<=$v['passport_due_diff'] && $v['passport_due_diff'] < 0){
//                $res[$k]['passport_count_down'] = abs($v['passport_due_diff']);
//            }
            //-90<= xxx <0  restoration剩余有效期
            if(-90<=$v['visa_due_diff'] && $v['visa_due_diff'] < 0){
                $res[$k]['visa_count_down'] = 90-abs($v['visa_due_diff']);
            }
        }
        return $res;
    }





















}


