<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/29
 * Time: 12:10
 */

namespace app\admin\controller;


class Pay extends Base
{

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $data['create_time'] = time();

            //1.如果存在政府费但是没有邮寄费
            //2.如果存在邮寄费但是没有政府费
            //3.如果三个都存在
            //4.如果只有服务费
            if(isset($data['goverment_fee']) || !isset($data['post_fee']) || isset($data['service_fee'])){
                if($data['goverment_fee'] == $data['goverment_fee_amount'] && $data['service_fee'] == $data['service_fee_amount']){
                    db('business')->where('id','=',$data['business_id'])->update(['is_payoff'=>1]);
                }
            }elseif(!isset($data['goverment_fee']) || isset($data['post_fee']) || isset($data['service_fee'])){
                if($data['post_fee'] == $data['post_fee_amount'] && $data['service_fee'] == $data['service_fee_amount']){
                    db('business')->where('id','=',$data['business_id'])->update(['is_payoff'=>1]);
                }
            }elseif(isset($data['goverment_fee']) || isset($data['post_fee']) || isset($data['service_fee'])){
                if($data['post_fee'] == $data['post_fee_amount'] && $data['service_fee'] == $data['service_fee_amount'] && $data['goverment_fee'] == $data['goverment_fee_amount']){
                    db('business')->where('id','=',$data['business_id'])->update(['is_payoff'=>1]);
                }
            }elseif(isset($data['service_fee']) || !isset($data['post_fee']) || !isset($data['goverment_fee'])){
                if($data['service_fee'] == $data['service_fee_amount']){
                    db('business')->where('id','=',$data['business_id'])->update(['is_payoff'=>1]);
                }
            }


            db('pay')->insert($data);
            $this->redirect('pay/index');
        }


        //接收客户的业务id
        $business_id = input('id');
        $res = db('business')->where('id','=',$business_id)->find();
        $res['fee_total'] = $res['service_fee'] + $res['post_fee'] + $res['goverment_fee'];
        $res['post_fee_remain'] = $res['post_fee'];
        $res['goverment_fee_remain'] = $res['goverment_fee'];
        //存在首次缴费
        if(!empty($res['amount'])){
            $res['service_fee_remain'] = $res['service_fee'] - $res['amount'];
            $res['service_amount'] = $res['amount'];
        }else{
            $res['service_fee_remain'] = $res['service_fee'];
        }//最终得到服务费的实际剩余


        //如果存在缴费记录的时候：
        $pay_res = db('pay')->where('business_id','=',$business_id)->select()->toArray();
        if(!empty($pay_res)){
            //计算balance
            $service_fee_balance = array();
            $post_fee_amount = array();
            $goverment_fee_amount = array();

            foreach ($pay_res as $k=>$v){
                $service_fee_balance[] = $v['service_fee_amount'];
                $post_fee_amount[] = $v['post_fee_amount'];
                $goverment_fee_amount[] = $v['goverment_fee_amount'];
            }
            //每次缴费的钱求和
            $service_fee_total = array_sum($service_fee_balance);
            $post_fee_total = array_sum($post_fee_amount);
            $goverment_fee_total = array_sum($goverment_fee_amount);

            /*
             * 服务费
             */
            //计算一共已经交了多少（每次缴费的钱的总和+首次缴费的钱）
            if(empty($res['amount'])){
                $res['service_amount'] = $service_fee_total;
            }else{
                $res['service_amount'] = $service_fee_total + $res['amount'];
            }
            //计算还差多少钱没交
            $res['service_fee_remain'] = $res['service_fee'] - $res['service_amount'];

            /*
             * 邮寄费
             */

            $res['post_amount'] = $post_fee_total;
            $res['post_fee_remain'] = $res['post_fee'] - $post_fee_total;

            /*
             * 政府费
             */
            $res['goverment_amount'] = $goverment_fee_total;
            $res['goverment_fee_remain'] = $res['goverment_fee'] - $goverment_fee_total;
        }

        $res['fee_total_remain'] = $res['service_fee_remain']+$res['post_fee_remain']+ $res['goverment_fee_remain'];
        //如果不存在首次缴费记录和缴费记录的话
        $this->assign('res',$res);
        return view();
    }


    //要考虑客人是否首次已经交钱
    public function index(){

        //接收客户的业务id
        $business_id = input('id');
        $res = db('pay')->where('business_id','=',$business_id)->select()->toArray();
        if($res){
            //计算balance
            $service_fee_balance = array();$post_fee_amount = array();$goverment_fee_amount = array();

            foreach ($res as $k=>$v){
                $service_fee_balance[] = $v['service_fee_amount'];
                $post_fee_amount[] = $v['post_fee_amount'];
                $goverment_fee_amount[] = $v['goverment_fee_amount'];
            }
            $service_fee_total = array_sum($service_fee_balance);
            $post_fee_total = array_sum($post_fee_amount);
            $goverment_fee_total = array_sum($goverment_fee_amount);


            $business_res = db('business')->where('id','=',$business_id)->find();
            //计算还差多少钱
            $business_res['service_fee_remain'] = $business_res['service_fee'] - ($service_fee_total + $business_res['amount']);
            $business_res['post_fee_remain'] = $business_res['post_fee'] - $post_fee_total;
            $business_res['goverment_fee_remain'] = $business_res['goverment_fee'] - $goverment_fee_total;


            $this->assign('business_res',$business_res);
            $this->assign('res',$res);
        }else{
            $this->redirect('business/index');
        }

        return view();
    }



    public function change_Is_Payoff_Status(){
        if(request()->isPost()){
            $business_id = input('business_id');
            db('business')->where('id','=',$business_id)->update(['is_payoff'=>1]);
        }
        return;
    }


    public function delete(){
        $id = input('id');
        $business_id = input('business_id');
        db('pay')->where('id','=',$id)->delete();
        $res = db('pay')->where('business_id','=',$business_id)->count();
        db('business')->where('id','=',$business_id)->update(['is_payoff'=>0]);
        $this->redirect('business/index');
    }
}