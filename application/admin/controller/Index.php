<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/14
 * Time: 22:56
 */

namespace app\admin\controller;


class Index extends Base
{

    public function index(){

        return view();
    }



    public function logout(){
        session(null);
        $this->redirect('login/index');
    }


    public function password(){

        if(request()->isPost()){
            $data = input('post.');
            db('user')->where('name','like',$data['name'])->update($data);
            $this->redirect('index/index');
        }
        $name = session('name');
        $res = db('user')->where('name','like',$name)->find();
        $this->assign("res",$res);
        return view();
    }
}