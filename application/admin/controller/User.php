<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/17
 * Time: 15:28
 */

namespace app\admin\controller;


class User extends Base
{
    public function index(){

        $res = db('user')->select()->toArray();
        if(!empty($res)){
            $this->assign('res',$res);
        }else{
            $this->redirect('user/add');
        }
        return view();
    }


    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            $position_res = db('position')->where('name','like',$data['position_name'])->find();
            $data['sort'] = $position_res['sort'];

            $res = db('user')->insert($data);
            if($res){
                $this->redirect('user/index');
            }
        }
        //部门的展示
        $department_res = db('department')->select()->toArray();
        $position_res = db('position')->select()->toArray();
        $this->assign('department_res',$department_res);
        $this->assign('position_res',$position_res);
        return view();
    }


    public function edit(){
        $id = input('id');
        $res = db('user')->where('id','=',$id)->find();
        $position_res = db('position')->select()->toArray();
        $this->assign('res',$res);
        $this->assign('position_res',$position_res);

        if(request()->isPost()){
            $data = input('post.');
            $position_res = db('position')->where('name','like',$data['position_name'])->find();
            $data['sort'] = $position_res['sort'];
            $res = db('user')->where('id','=',$data['id'])->update($data);
            if($res){
                $this->redirect('user/index');
            }
        }

        return view();
    }


    public function delete(){
        $id = input('id');
        $res = db('user')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('user/index');
        }
    }
}