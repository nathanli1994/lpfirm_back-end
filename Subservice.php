<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/29
 * Time: 12:25
 */

namespace app\admin\controller;


class Subservice extends Base
{

    public function index(){

        $res = db('subservice')->select()->count();
        if($res ==0){
            $this->redirect('subservice/add');
        }else{
            $res = db('subservice')->select()->toArray();
        }
        $this->assign('res',$res);
        return view();
    }


    public function add(){

        if (request()->isPost()){
            $data = input('post.');

            $res = db('subservice')->insert($data);
            if($res){
                $this->redirect('subservice/index');
            }
        }
        return view();
    }


    public function edit(){
        $id = input('id');
        if(request()->isPost()){
            $data = input('post.');

            $res = db('subservice')->where('id','=',$data['id'])->update($data);
            if($res){
                $this->redirect('subservice/index');
            }
        }

        $res = db('subservice')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view();
    }


    public function delete(){

        $id = input('id');
        $res = db('subservice')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('subservice/index');
        }
    }




    public function highschools_index(){

        $count = db('highschools')->count();
        if($count ==0){
            $this->redirect('subservice/highschools_add');
        }else{
            $res = db('highschools')->select()->toArray();
        }
        $this->assign('res',$res);
        return view();
    }




    public function highschools_add(){

        if(request()->isPost()){
            $data = input('post.');
            $res = $this->calculate_share($data);

            db('highschools')->insert($res);
            $this->redirect('subservice/highschools_index');
        }
        return view();
    }



    public function highschools_edit(){

        if(request()->isPost()){
            $data = input('post.');
            $res = $this->calculate_share($data);
            db('highschools')->where('id','=',$data['id'])->update($res);
            $this->redirect('subservice/highschools_index');
        }
        $id = input('id');
        $res = db('highschools')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view();
    }


    public function highschools_delete(){

        $id = input('id');
        db('highschools')->where('id','=',$id)->delete();
        $this->redirect('subservice/highschools_index');
        return view();
    }


    public function calculate_share($data){
        //一课时
        $one_credit_school_commission_profit = round($data['fee'] / $data['credit'] * $data['school_commission'] / 100 /1.13,2);
        $one_credit_company_commission_profit = round($data['fee'] / $data['credit'] * $data['company_commission'] / 100 /1.13,2);
        //total
        $school_commission_profit = round($data['fee'] * $data['school_commission'] / 100 /1.13,2);
        $company_commission_profit = round($data['fee'] * $data['company_commission'] / 100 /1.13,2);
        ////////////////////////
        /// 兼职一课时的钱 //////
        ///     1-10        ///
        ///     11-20      ///
        ///     21-30     ///
        ///     大于30    ///
        ////////////////////
        $p_one_to_ten = round($data['part_time']  / 100 * $one_credit_company_commission_profit,2);
        $p_eleven_to_twenty = round($p_one_to_ten + ($p_one_to_ten * 10 / 100),2);
        $p_twenty_one_to_thirty = round($p_eleven_to_twenty + ($p_eleven_to_twenty * 10 / 100),2);
        $p_gt_thirty = round($p_twenty_one_to_thirty + ($p_twenty_one_to_thirty * 10 / 100),2);

        ////////////////////////
        /// 全职一课时的钱 //////
        ///     1-10        ///
        ///     11-20      ///
        ///     21-30     ///
        ///     大于30    ///
        ////////////////////
        $f_eleven_to_twenty = round($data['full_time'] / 100 * $one_credit_company_commission_profit,2);
        $f_one_to_ten = round($f_eleven_to_twenty - ($f_eleven_to_twenty * 10 / 100),2);
        $f_twenty_one_to_thirty = round($f_eleven_to_twenty + ($f_eleven_to_twenty * 10 / 100),2);
        $f_gt_thirty = round($f_twenty_one_to_thirty + ($f_twenty_one_to_thirty * 10 / 100),2);



        $data['one_credit_school_commission_profit'] = $one_credit_school_commission_profit;
        $data['one_credit_company_commission_profit'] = $one_credit_company_commission_profit;
        $data['school_commission_profit'] = $school_commission_profit;
        $data['company_commission_profit'] = $company_commission_profit;

        $data['p_one_to_ten'] = $p_one_to_ten;
        $data['p_eleven_to_twenty'] = $p_eleven_to_twenty;
        $data['p_twenty_one_to_thirty'] = $p_twenty_one_to_thirty;
        $data['p_gt_thirty'] = $p_gt_thirty;
        $data['f_eleven_to_twenty'] = $f_eleven_to_twenty;
        $data['f_one_to_ten'] = $f_one_to_ten;
        $data['f_twenty_one_to_thirty'] = $f_twenty_one_to_thirty;
        $data['f_gt_thirty'] = $f_gt_thirty;

        return $data;
    }
}