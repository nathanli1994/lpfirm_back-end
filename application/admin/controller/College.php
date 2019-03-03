<?php
/**
 * Created by PhpStorm.
 * User: NATHAN
 * Date: 2019/2/28
 * Time: 11:51
 */

namespace app\admin\controller;


class College extends Base
{

    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            db('colleges')->insert($data);
            $this->redirect('college/index');
        }
        return view('schoolinfo/college/add');
    }


    public function index(){
        $res = db('colleges')->select()->toArray();
        if(count($res)>0){
            $this->assign('res',$res);
        }else{
            $this->error('没有college数据，先添加一个','college/add');
        }
        return view('schoolinfo/college/index');
    }


    public function delete(){
        $id = input('id');
        db('colleges')->where('id','=',$id)->delete();
        $this->redirect('college/index');
    }


    public function edit(){

        if(request()->isPost()){
            $data = input('post.');
            db('colleges')->where('id','=',$data['id'])->update($data);
            $this->redirect('college/index');
        }

        $id = input('id');
        $res = db('colleges')->where('id','=',$id)->find();
        $this->assign('res',$res);
        return view('schoolinfo/college/edit');
    }
}