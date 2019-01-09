<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/14
 * Time: 23:22
 */

namespace app\admin\controller;


use think\Controller;

class Login extends Controller
{
    /*
    *   1.登陆：单独继承控制器，并传递username，其余控制器继承base
    */
    public function index(){

        if(request()->isPost()){
            $data = input('post.');
            $user_data = db('user')->where('account','like',$data['account'])->find();
            $user_position_data = db('position')->where('name','like',$user_data['position_name'])->find();
            if($user_data['password'] == $data['password']){
                session('name',$user_data['name']);
                session('duty',$user_data['duty']);
                session('position_status',$user_data['position_status']);
                session('sort',$user_position_data['sort']);
                session('position',$user_position_data['name']);
                $this->redirect('index/index');
            }else{
                $this->redirect('login/index');
            }
        }
        return view();
    }
}