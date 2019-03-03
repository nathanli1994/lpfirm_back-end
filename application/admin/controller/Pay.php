<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/29
 * Time: 12:10
 */

namespace app\admin\controller;


use think\Error;

class Pay extends Base
{

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $data['create_time'] = time() - 3600*24;

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
            $this->redirect('business/index');
        }


        //amount的值是下面人实际收进来的钱，入手里面看得见摸得着的钱，
        /*
         * 添加缴费记录的业务思路：
         *  1.如果客户没有交齐钱，则存在添加需求
         *      1.没有交起包括：服务费，邮寄费，政府费
         */
        //接收客户的业务id
        $business_id = input('id');
        $res = db('business')->where('id','=',$business_id)->find();

        //费用总计
        $res['fee_total'] = $res['service_fee'] + $res['post_fee'] + $res['goverment_fee'];

        /*
         * 计算各项费用的尾款，邮寄费和政府费默认是由客户自行负责的
         */
        $res['post_fee_remain'] = $res['post_fee'];
        $res['goverment_fee_remain'] = $res['goverment_fee'];

        //amount是客户缴费总金额，这一项是必填的，如果销售输入的金额是服务费，邮寄费，政府费的总和，说明费用交齐了，不存在剩余费用
        if($res['amount'] == $res['service_fee'] + $res['post_fee'] + $res['goverment_fee']){
            $res['service_fee_remain'] = 0;
            $res['post_fee_remain'] = 0;
            $res['goverment_fee_remain'] = 0;
        }else{
            //如果没交齐，则判断是否需要记录以及提示信息
            if($res['is_remind'] ==0){
                //如果销售输入的缴费总金额=服务费，则记录邮寄费和政府费的原因
                if($res['amount'] == $res['service_fee']){
                    $this->redirect('pay/add_remind',['id'=>$business_id, 'msg_type'=>1]);
                //如果销售输入的缴费总金额=服务费+邮寄费，则记录政府费的原因
                }elseif ($res['amount'] == $res['service_fee'] + $res['post_fee']){
                    $this->redirect('pay/add_remind',['id'=>$business_id, 'msg_type'=>2]);
                //如果销售输入的缴费总金额=服务费+政府费，则记录邮寄费的原因
                }elseif ($res['amount'] == $res['service_fee'] + $res['goverment_fee']){
                    $this->redirect('pay/add_remind',['id'=>$business_id, 'msg_type'=>3]);
                }else{
                    //amount金额自定义的，推算不出来的话
                    $this->redirect('pay/add_remind',['id'=>$business_id, 'msg_type'=>4]);
                }
            }else{//如果不用记录或者存在记录，则无需跳转去添加记录，费用就是规定的费用
                $res['service_fee_remain'] = $res['service_fee'];
                $res['post_fee_remain'] = $res['post_fee'];
                $res['goverment_fee_remain'] = $res['goverment_fee'];
            }
        }
        //显示客户交了多少费用

        $res['customer_pay'] = $res['amount'];

        //计算应缴费用和客户实际交的费用的差额，如果amount的数值过大，就让他直接等于0
        $res['balance'] = $res['service_fee'] + $res['post_fee'] + $res['goverment_fee'] - $res['amount'];
        if($res['balance'] <=0){
            $res['balance'] = 0;
        }



        //如果已经存在多笔缴费记录的时候：
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
            //多次缴费的钱求和，这里算出来的是已经交的
            $service_fee_total = array_sum($service_fee_balance);
            $post_fee_total = array_sum($post_fee_amount);
            $goverment_fee_total = array_sum($goverment_fee_amount);

            //如果由付款记录，重新定义客户付费
            $res['customer_pay'] = $service_fee_total + $post_fee_total + $goverment_fee_total;

            /*
             * 服务费
             */
            //计算一共已经交了多少（每次缴费的钱的总和）
            $res['service_amount'] = $service_fee_total;
            //计算还差多少钱没交
            $res['service_fee_remain'] = $res['service_fee'] - $service_fee_total;

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

            /*
             * total remain
             */
            $res['fee_total_remain'] = $res['fee_total'] - $res['customer_pay'];

        }else{
            if($res['amount'] == 0){
                $res['fee_total_remain'] = $res['service_fee_remain']+$res['post_fee_remain']+ $res['goverment_fee_remain'];
            }else{
                $res['fee_total_remain'] = $res['amount'];
            }
        }





        //如果不存在首次缴费记录和缴费记录的话
        $this->assign('res',$res);
        return view();
    }


    //要考虑客人是否首次已经交钱
    public function index(){

        //接收客户的业务id
        $business_id = input('id');
        //查看是否存在缴费记录
        $res = db('pay')->where('business_id','=',$business_id)->select()->toArray();
        if(empty($res)){
            $this->redirect("pay/origin",['id'=>$business_id]);
        }

        if($res){
            //计算balance
            $service_fee_balance = array();$post_fee_amount = array();$goverment_fee_amount = array();

            foreach ($res as $k=>$v){
                $service_fee_balance[] = $v['service_fee_amount'];
                $post_fee_amount[] = $v['post_fee_amount'];
                $goverment_fee_amount[] = $v['goverment_fee_amount'];
            }
            //已经交的钱的各项总和
            $service_fee_total = array_sum($service_fee_balance);
            $post_fee_total = array_sum($post_fee_amount);
            $goverment_fee_total = array_sum($goverment_fee_amount);


            $business_res = db('business')->where('id','=',$business_id)->find();
            //计算还差多少钱
            $business_res['service_fee_remain'] = $business_res['service_fee'] - $service_fee_total;
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
        $res = db('pay')->where('id','=',$id)->find();
        db('pay')->where('id','=',$id)->delete();

        $count = db('pay')->where('business_id','=',$res['business_id'])->count();
        if($count ==0){
            db('business')->where('id','=',$res['business_id'])->update(['is_payoff'=>0]);
        }

        $pay = db('pay')->where('business_id','=',$res['business_id'])->select()->toArray();
        foreach ($pay as $k=>$v){
            $service_fee_balance[] = $v['service_fee_amount'];
            $post_fee_amount[] = $v['post_fee_amount'];
            $goverment_fee_amount[] = $v['goverment_fee_amount'];
        }
        $service_fee_total = array_sum($service_fee_balance);
        $post_fee_total = array_sum($post_fee_amount);
        $goverment_fee_total = array_sum($goverment_fee_amount);

        $business = db('business')->where('id','=',$res['business_id'])->find();
        if($service_fee_total != $business['service_fee'] || $post_fee_total != $business['post_fee'] || $goverment_fee_total != $business['goverment_fee']){
            db('business')->where('id','=',$res['business_id'])->update(['is_payoff'=>0]);
        }
        $this->redirect('business/index');
    }


    public function edit(){
        //业务的id
        $id = input('id');
        $res = db('pay')->where('business_id','=',$id)->select()->toArray();

        if(empty($res)){
            $this->redirect('pay/origin',['id'=>$id]);
        }else{
            $this->assign('res',$res);
        }
        return view();
    }


    public function origin(){
        //业务id
        $id = input('id');
        $res = db('business')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view();
    }


    public function edit_pay_history(){

        if(request()->isPost()){
            $data = input('post.');
            $res = db('pay')->where('id','=',$data['id'])->find();
            db('pay')->where('id','=',$data['id'])->update($data);

            //如果修改过后的金额与应收金额对不上，则可以继续添加缴费记录，并且重置这个业务的is_payoff字段
            $business_res = db('business')->where('id','=',$res['business_id'])->find();
            $service_fee = $business_res['service_fee'];
            $post_fee = $business_res['post_fee'];
            $goverment_fee = $business_res['goverment_fee'];

            $pay_history = db('pay')->where('business_id','=',$res['business_id'])->select()->toArray();
            foreach($pay_history as $k=>$v){
                $arr_service_fee[] = $v['service_fee_amount'];
                $arr_goverment_fee[] = $v['goverment_fee_amount'];
                $arr_post_fee[] = $v['post_fee_amount'];
            }

            if($service_fee != array_sum($arr_service_fee) || $post_fee != array_sum($arr_post_fee) || $goverment_fee != array_sum($arr_goverment_fee)){
                db('business')->where('id','=',$res['business_id'])->update(['is_payoff' => 0]);
            }
            $this->redirect('pay/edit',['id'=>$res['business_id']]);
        }
        $pay_id = input('id');
        $res = db('pay')->where('id','=',$pay_id)->find();
        $this->assign('res',$res);
        return view();
    }


    public function checkRemainBalance(){
        if(request()->isPost()){
            $id = input('id');
            db('business')->where('id','=',$id)->update(['is_payoff'=>1]);
        }
    }



    public function add_remind(){

        if(request()->isPost()){
            $data = input('post.');
            $data['is_remind'] = 1;
            db('business')->where('id','=',$data['id'])->update($data);
            $this->redirect('business/index');
        }

        $id = input('id');
        $res = db('business')->where('id','=',$id)->find();
        $this->assign('res',$res);

        $msg_type = input('msg_type');
        if($msg_type == 1){
            $msg = '客户已缴服务费，请询问销售，并备注政府费和邮寄费情况';
        }elseif ($msg_type == 2){
            $msg = '客户已缴服务费和邮寄费，请询问销售，并备注政府费情况';
        }elseif ($msg_type == 3){
            $msg = '客户已缴服务费和政府费，请询问销售，并备注邮寄费情况';
        }else{
            $msg = '客户缴费金额与实际应收金额不符，请与销售核对并备注记录';
        }
        $this->assign('msg',$msg);

        return view();
    }
}