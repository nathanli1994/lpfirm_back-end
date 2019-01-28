<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/24
 * Time: 14:14
 */

namespace app\admin\controller;


class Business extends Base
{

    //对权重小于3的用户，显示全部业务信息
    public function index(){

        $count = db('business')->count();
        if($count == 0){
            $this->redirect('customer/add');
        }else{
            $num = session('sort');
            if($num <3 || session('duty')=='前台'){
                $res = db('business')->select()->toArray();
            }
            $this->assign('res',$res);
        }
        return view();
    }




    //个人业务
    public function personal_business(){

        $name = session('name');
        $count = db('business')->where('user|export_to|wenan','like',$name)->count();
        if($count == 0){
            $this->redirect('customer/personal_customer');
        }else{
            if($count >1){
                $res = db('business')->where('user|export_to|wenan','like',$name)->select()->toArray();
            }else{
                $one_res = db('business')->where('user|export_to|wenan','like',$name)->find();
                $res[] = $one_res;
            }

            foreach ($res as $k=>$v){
                if($v['subservice_name'] == '疑难签证'){
                    $res[$k]['refundable'] = round($v['refundable']* 2 / 3, 0);
                }
            }
            $this->assign('res',$res);
        }
        return view();
    }


    public function edit(){
        if(request()->isPost()){
            $data = input('post.');
            $data = $this->solve_on_off($data);
            $data['update_time'] = time();
            unset($data['is_marry']);
            unset($data['is_worker']);
            unset($data['is_reject']);
            unset($data['is_criminal']);
            unset($data['is_education']);

            //将护照签证更新到同一天
            if($data['subservice_name'] == '境外旅游签'){
                //$ten_years_later = date('Y-m-d', strtotime ("+10 years", strtotime($data['expire_time_visa'])));
                $business_res = db('business')->where('id','=',$data['id'])->find();
                $customer_res = db('customer')->where('id','=',$business_res['student_id'])->find();
                $passport_due = $customer_res['passport_due'];
                db('customer')->where('id','=',$business_res['student_id'])->update(['visa_due'=>$passport_due]);
            }
            //同步更新客人信息
            if($data['is_export'] !== 0 && $data['is_remind'] == 0){
                if($data['subservice_name'] =='学签和小签' || $data['subservice_name'] =='身份恢复和小签' || $data['subservice_name'] =='毕业工签和小签'){

                    if($data['progress'] =='申请递交' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                        ];
                    }
                }else{
                    if($data['progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                        ];
                    }elseif($data['progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' =>0,
                            'visa_progress'=>$data['progress'],
                        ];
                    }
                }
            }
            elseif ($data['is_remind'] !== 0 && $data['is_export'] == 0){
                if($data['subservice_name'] =='学签和小签' || $data['subservice_name'] =='身份恢复和小签' || $data['subservice_name'] =='毕业工签和小签'){
                    if($data['progress'] =='申请递交' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                        ];
                    }
                }else{
                    if($data['progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                        ];
                    }elseif($data['progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                        ];
                    }
                }
            }
            elseif ($data['is_remind'] == 0 && $data['is_export'] == 0){
                if($data['subservice_name'] =='学签和小签' || $data['subservice_name'] =='身份恢复和小签' || $data['subservice_name'] =='毕业工签和小签'){
                    if($data['progress'] =='申请递交' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                        ];
                    }
                }else{
                    if($data['progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                        ];
                    }elseif($data['progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                            'visa_progress'=>$data['progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_remind' => $data['is_remind'],
                            'is_export' => 0,
                            'export_to' => 0,
                        ];
                    }
                }
            }else{
                if($data['subservice_name'] =='学签和小签' || $data['subservice_name'] =='身份恢复和小签' || $data['subservice_name'] =='毕业工签和小签'){
                    if($data['progress'] =='申请递交' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='收集材料'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证被拒'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='申请递交'){
                        $customer_update_info = [
                            'passport_submit_time'=>$data['submit_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='收集材料' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='申请递交' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证获批' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }elseif($data['progress'] =='签证被拒' && $data['extra_progress'] =='签证获批'){
                        $customer_update_info = [
                            'passport_due'=>$data['expire_time_passport'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                            'passport_progress'=>$data['extra_progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                        ];
                    }
                }else{
                    if($data['progress'] =='申请递交'){
                        $customer_update_info = [
                            'visa_submit_time'=>$data['submit_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                        ];
                    }elseif($data['progress'] =='签证获批'){
                        $customer_update_info = [
                            'visa_due'=>$data['expire_time_visa'],
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                            'visa_progress'=>$data['progress'],
                        ];
                    }else{
                        $customer_update_info = [
                            'sub_service' => $data['subservice_name'],
                            'is_export' => $data['is_export'],
                            'export_to' => $data['export_to'],
                            'is_remind' => $data['is_remind'],
                        ];
                    }
                }
            }

            //结案时间，case_close_time
            if($data['progress'] == '签证获批' || $data['progress'] == '签证被拒'){
                $data['case_close_time'] = time();
            }

            $current_business_res = db('business')->where('id','=',$data['id'])->find();
            db('customer')->where('id','=',$current_business_res['student_id'])->update($customer_update_info);
            db('business')->where('id','=',$data['id'])->update($data);

            $this->redirect('business/personal_business');
        }

        $id = input('id');
        $res = db('business')->where('id','=',$id)->find();
        $this->assign('res',$res);

        $sub_service = db('subservice')->select()->toArray();
        $this->assign('sub_service',$sub_service);
        $user_res = db('user')->select()->toArray();
        $this->assign('user_res',$user_res);

        return view();
    }


    public function add(){
        $student_id = input('student_id');
        $business_id = input('business_id');

        if(request()->isPost()){
            $data = input('post.');
            $data = $this->solve_on_off($data);
            $data['create_time'] = time();
            unset($data['is_marry']);
            unset($data['is_worker']);
            unset($data['is_reject']);
            unset($data['is_criminal']);
            unset($data['is_education']);


            //同步更新客人信息
            if($data['is_export'] !== 0){
                $customer_update_info = [
                    'sub_service' => $data['subservice_name'],
                    'is_export' => $data['is_export'],
                    'export_to' => $data['export_to'],
                ];
            }elseif ($data['is_export'] == 0){
                $customer_update_info = [
                    'sub_service' => $data['subservice_name'],
                    'is_export' => 0,
                    'export_to' => 0,
                ];
            }else{
                $customer_update_info = [
                    'sub_service' => $data['subservice_name'],
                    'is_export' => $data['is_export'],
                    'export_to' => $data['export_to'],
                ];
            }
            db('customer')->where('id','=',$student_id)->update($customer_update_info);


            if($data['service_fee'] == 0 && ($data['subservice_name'] == 'college申请' || $data['subservice_name'] == '大学申请')){
                $value = $data['amount'];
                $data['service_fee'] = $value;
            }
            $res = db('business')->insert($data);
            if($res){

                //客人信息
                $res = db('customer')->where('id','=',$student_id)->find();

                $this->assign('res',$res);
                //下拉框等
                $sub_service = db('subservice')->select()->toArray();
                $city_res = db('cities')->select()->toArray();
                $user_res = db('user')->select()->toArray();
                $visa_res = db('visatype')->select()->toArray();

                $this->assign('visa_res',$visa_res);
                $this->assign('res',$res);
                $this->assign('sub_service',$sub_service);
                $this->assign('city_res',$city_res);
                $this->assign('user_res',$user_res);
                //业务
                $business_res = db('business')->where('student_id','=',$student_id)->order('id desc')->select()->toArray();
                $this->assign('business_res',$business_res);

                /*
                 *对指定模板渲染
                 * assign分配要与之前的保持一致（把之前方法里面的分配代码抄过来）
                 */
//                return $this->fetch('customer/edit');
                $this->redirect('business/personal_business');
            }
        }

        //隐藏域
        $student_res = db('customer')->where('id','=',$student_id)->find();
        $this->assign('student_res',$student_res);
        //业务选择
        $count_subservice = db('subservice')->where('pid','=',$business_id)->count();
        if($count_subservice == 1){
            $subservice_res = [];
            $res = db('subservice')->where('pid','=',$business_id)->find();
            $subservice_res[] = $res;
        }else{
            $subservice_res = db('subservice')->where('pid','=',$business_id)->order('sort asc')->select()->toArray();
        }
        $this->assign('subservice_res',$subservice_res);


        //导单员工选择
        if(session('sort')<=3){
//            $user_res = db('user')->where('sort','>',session('sort'))->select()->toArray();
            $user_res = db('user')->select()->toArray();
            //将非销售文案移除选择列表
            foreach ($user_res as $k=>$v){
                if($v['id']==8 || $v['id']==9){
                    unset($user_res[$k]);
                }
            }

        }else{
            $user_res = db('user')->where('duty','like','前台')->select()->toArray();
        }
        $this->assign('user_res',$user_res);

        //接单人下来列表
        $user_export_res = db('user')->select()->toArray();
        foreach ($user_export_res as $k=>$v){
            if($v['duty'] == '销售'){
                $user_export[]['name'] = $v['name'];
            }
        }
        $this->assign('user_export',$user_export);
        return view();
    }



    public function delete(){
        $id = input('id');
        $business_student_id = db('business')->where('id','=',$id)->find();
        db('business')->where('id','=',$id)->delete();
        db('pay')->where('business_id','=',$id)->delete();


        $count = db('business')->where('student_id','=',$business_student_id['student_id'])->count();
        if($count == 0){
            $update_customer = [
                'sub_service'=> null,
                'is_export'=>0,
                'is_remind'=>0,
                'export_to'=>0,
                'remind'=>null,
                'visa_progress'=>null,
                'passport_progress'=>null,
                'wenan'=>null,
            ];
            db('customer')->where('id','=',$business_student_id['student_id'])->update($update_customer);
        }
        $this->redirect('business/personal_business');
    }





    public function get_Multi_Progress(){

        if(request()->isPost()){
            $subservice = input('subservice');
            $business_id = input('business_id');

            $business_res = db('business')->where('id','=',$business_id)->find();
            $res = db('progress')->where('subservice_name','like',$subservice)->order('sort','asc')->select()->toArray();

            $opt = '';
            $opt2 = '';
            $arr = [];

            foreach ($res as $k=>$v){
                if($business_res['progress'] == $v['name']){
                    $opt .= "<option selected value='{$business_res['progress']}'>{$business_res['progress']}</option>";
                }else{
                    $opt .= "<option value='{$v['name']}'>{$v['name']}</option>";
                }
            }

            foreach ($res as $k=>$v){
                if($business_res['extra_progress'] == $v['name']){
                    $opt2 .= "<option selected value='{$business_res['extra_progress']}'>{$business_res['extra_progress']}</option>";
                }else{
                    $opt2 .= "<option value='{$v['name']}'>{$v['name']}</option>";
                }
            }

            //返回给客户端的要是一个索引数组
            $arr[] = $opt;
            $arr[] = $opt2;
        }
        return json($arr);
    }


    public function getProgress(){

        if(request()->isPost()){
            $subservice = input('subservice');
            $business_id = input('business_id');

            $business_res = db('business')->where('id','=',$business_id)->find();
            $res = db('progress')->where('subservice_name','like',$subservice)->order('sort','asc')->select()->toArray();

            $opt = '';
            foreach ($res as $k=>$v){
                if($business_res['progress'] == $v['name']){
                    $opt .= "<option selected value='{$business_res['progress']}'>{$business_res['progress']}</option>";
                }else{
                    $opt .= "<option value='{$v['name']}'>{$v['name']}</option>";
                }
            }
        }
        return json($opt);
    }



    public function getFees(){

        if(request()->isPost()){
            $subservice = input('subservice');
            $res = db('subservice')->where('name','like',$subservice)->find();
            foreach ($res as $k=>$v){
                if(empty($res['fee'])){
                    $res['fee'] =0;
                }
                if(empty($res['post_fee'])){
                    $res['post_fee'] =0;
                }
                if(empty($res['goverment_fee'])){
                    $res['goverment_fee'] =0;
                }
            }
            $input = [];
            $input[] = "<input class='form-control' type='text' name='service_fee' value='{$res['fee']}' readonly/><p><span class=\"btn btn-danger btn-sm\">修改服务费</span></p>";
            $input[] = "<input class='form-control' type='text' name='post_fee' value='{$res['post_fee']}' readonly/><p><span class=\"btn btn-danger btn-sm\">修改邮寄费</span></p>";
            $input[] = "<input class='form-control' type='text' name='goverment_fee' value='{$res['goverment_fee']}' readonly/><p><span class=\"btn btn-danger btn-sm\">修改政府费</span></p>";
            if(!is_null($res['refundable'])){
                $input[] = "<input class='form-control' type='text' name='refundable' value='{$res['refundable']}' readonly/>";
            }else{
                $input[] = "<input class='form-control' type='text' name='refundable' value='0' readonly/>";
            }

            if(!is_null($res['non_refundable'])){
                $input[] = "<input class='form-control' type='text' name='non_refundable' value='{$res['non_refundable']}' readonly/>";
            }else{
                $input[] = "<input class='form-control' type='text' name='non_refundable' value='0' readonly/>";
            }

        }
        return json($input);
    }


    public function change_status(){

        if(request()->isPost()){
            $data = input('post.');
            $data['update_time'] = time();
            $data['is_export'] = 1;
            $data = $this->solve_on_off($data);
            unset($data['is_marry']);
            unset($data['is_worker']);
            unset($data['is_reject']);
            unset($data['is_criminal']);
            unset($data['is_education']);

            //如果选择了文案则添加建档时间，对应数据库make_file_date
            if(!empty($data['wenan'])){
                $data['make_file_date'] = time();
            }

            //把文案信息更新到客户
            $business_res = db('business')->where('id','=',$data['id'])->find();
            db('customer')->where('id','like',$business_res['student_id'])->update(['wenan'=>$data['wenan']]);

            $res = db('business')->where('id','=',$data['id'])->update($data);

            if($res){
                $this->redirect('business/index');
            }
        }


        $id = input('id');
        $res = db('business')->where('id','=',$id)->find();
        $this->assign('res',$res);

        $sub_service = db('subservice')->select()->toArray();
        $this->assign('sub_service',$sub_service);
        $user_res = db('user')->select()->toArray();
        $this->assign('user_res',$user_res);
        return view();
    }


    public function getSub(){

        if(request()->isPost()){
            $business_type = input('business_type');
            $res = db('subservice')->where('business_type','like','%'.$business_type.'%')->select()->toArray();

            $opt = '<option value="">请选择</option>';
            foreach ($res as $k=>$v){
                $opt .= "<option value='{$v['name']}'>{$v['name']}</option>";
            }
        }
        return json($opt);
    }





    public function university_program_add(){
        $student_id = input('student_id');

        if(request()->isPost()){
            $data = input('post.');
            $data['create_time'] = time();
            db('university')->insert($data);
            $this->redirect('business/personal_business');
        }
        $this->assign('student_id',$student_id);
        return view();
    }


    public function university_program_list(){
        $student_id = input('student_id');
        $count = db('university')->where('student_id','=',$student_id)->count();
        if($count == 0){
            $this->redirect('business/personal_business');
        }else{
            if($count == 1){
                $one_res = db('university')->where('student_id','=',$student_id)->find();
                $res[] = $one_res;
            }else{
                $res = db('university')->where('student_id','=',$student_id)->select()->toArray();
            }

            $this->assign('res',$res);
        }

        return view();
    }


    public function set_final_decision(){
        $id = input('id');
        $university_res = db('university')->where('id','=',$id)->find();
        $business_res = db('business')->where('id','=',$university_res['student_id'])->find();
        $customer_res = db('customer')->where('id','=',$business_res['student_id'])->find();
        db('university')->where('id','=',$id)->update(['final_decision'=>1]);
        $this->success('客户：' . $customer_res['name'] . '，最终决定：' . $university_res['schools']);
    }
    public function cancel_final_decision(){
        $id = input('id');
        $university_res = db('university')->where('id','=',$id)->find();
        $business_res = db('business')->where('id','=',$university_res['student_id'])->find();
        $customer_res = db('customer')->where('id','=',$business_res['student_id'])->find();
        db('university')->where('id','=',$id)->update(['final_decision'=>0]);
        $this->success('客户：' . $customer_res['name'] . '，取消决定：' . $university_res['schools']);
    }
    public function edit_university_status(){

        if(request()->isPost()){
            $data = input('post.');
            db('university')->where('id','=',$data['id'])->update($data);
            $this->redirect('business/personal_business');
        }
        $id = input('id');
        $university_res = db('university')->where('id','=',$id)->find();
        $this->assign('res',$university_res);
        return view();
    }






    public function college_program_add(){
        $student_id = input('student_id');

        if(request()->isPost()){
            $data = input('post.');
            $data['create_time'] = time();
            db('college')->insert($data);
            $this->redirect('business/personal_business');
        }
        $this->assign('student_id',$student_id);
        return view();
    }


    public function college_program_list(){
        $student_id = input('student_id');
        $count = db('college')->where('student_id','=',$student_id)->count();
        if($count == 0){
            $this->redirect('business/personal_business');
        }else{
            if($count == 1){
                $one_res = db('college')->where('student_id','=',$student_id)->find();
                $res[] = $one_res;
            }else{
                $res = db('college')->where('student_id','=',$student_id)->select()->toArray();
            }

            $this->assign('res',$res);
        }

        return view();
    }


    public function edit_college_status(){

        if(request()->isPost()){
            $data = input('post.');
            db('college')->where('id','=',$data['id'])->update($data);
            $this->redirect('business/personal_business');
        }
        $id = input('id');
        $college_res = db('college')->where('id','=',$id)->find();
        $this->assign('res',$college_res);
        return view();
    }
    public function college_set_final_decision(){
        $id = input('id');
        $college_res = db('college')->where('id','=',$id)->find();
        $business_res = db('business')->where('id','=',$college_res['student_id'])->find();
        $customer_res = db('customer')->where('id','=',$business_res['student_id'])->find();
        db('college')->where('id','=',$id)->update(['final_decision'=>1]);
        $this->success('客户：' . $customer_res['name'] . '，最终决定：' . $college_res['schools']);
    }
    public function college_cancel_final_decision(){
        $id = input('id');
        $college_res = db('college')->where('id','=',$id)->find();
        $business_res = db('business')->where('id','=',$college_res['student_id'])->find();
        $customer_res = db('customer')->where('id','=',$business_res['student_id'])->find();
        db('college')->where('id','=',$id)->update(['final_decision'=>0]);
        $this->success('客户：' . $customer_res['name'] . '，取消决定：' . $college_res['schools']);
    }




    public function downloadExcel(){
        $level = input('level');
        $user_name = session('name');

        if($level == 'personal'){
            $res = db('business')->where('user|wenan','like',$user_name)->whereTime('create_time','month')->select()->toArray();
            $filename = $user_name . '的' . date('n') . '月业务报表';
        }else{
            $res = db('business')->whereTime('create_time','month')->select()->toArray();
            $filename =  '加诺咨询' . date('n') . '月业务报表';
        }

        if(empty($res)){
            $this->error('不符合导出标准，因此不能进行Excel导出','business/personal_business');
        }
        foreach ($res as $k=>$v){
            $list[$k]['客户姓名'] = $v['customer_name'];
            $list[$k]['业务名称'] = $v['subservice_name'];
            $list[$k]['收费金额'] = $v['service_fee'];
            $list[$k]['负责销售'] = $v['user'];
            $list[$k]['业务进度'] = $v['progress'];
        }

        $indexkey = array('客户姓名','业务名称','收费金额','负责销售','业务进度');
        $this->exportExcel($list,$filename,$indexkey,$level);
    }
}