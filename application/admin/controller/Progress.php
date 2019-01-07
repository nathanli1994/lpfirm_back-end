<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/29
 * Time: 15:41
 */

namespace app\admin\controller;


class Progress extends Base
{

    public function index(){

        $res = db('progress')->select()->count();
        if($res ==0){
            $this->redirect('progress/add');
        }else{
            $res = db('progress')->select()->toArray();
        }
        $this->assign('res',$res);
        return view();
    }

    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            db('progress')->insert($data);
            $this->redirect('progress/index');
        }

        $res = db('subservice')->select()->toArray();
        $this->assign('res',$res);
        return view();
    }


    public function delete(){
        $id = input('id');
        $res = db('progress')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('progress/index');
        }
    }


    public function edit(){

        if(request()->isPost()){
            $data = input('post.');
            db('progress')->where('id','=',$data['id'])->update($data);
            $this->redirect('progress/index');
        }
        $id = input('id');
        $res = db('progress')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view();
    }


}