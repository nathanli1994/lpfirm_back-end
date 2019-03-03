<?php
/**
 * Created by PhpStorm.
 * User: NATHAN
 * Date: 2019/2/28
 * Time: 11:08
 */

namespace app\admin\controller;


class Highschool extends Base
{

    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            db('highschools')->insert($data);
            $this->redirect('highschool/index');
        }
        return view('schoolinfo/highschool/add');
    }


    public function index(){
        $res = db('highschools')->select()->toArray();
        if(count($res)>0){
            $this->assign('res',$res);
        }else{
            $this->error('没有高中数据，先添加一个','highschool/add');
        }
        return view('schoolinfo/highschool/index');
    }


    public function delete(){
        $id = input('id');
        db('highschools')->where('id','=',$id)->delete();
        $this->redirect('highschool/index');
    }


    public function edit(){

        if(request()->isPost()){
            $data = input('post.');
            db('highschools')->where('id','=',$data['id'])->update($data);
            $this->redirect('highschool/index');
        }

        $id = input('id');
        $res = db('highschools')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view('schoolinfo/highschool/edit');
    }
}