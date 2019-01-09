<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/15
 * Time: 11:38
 */

namespace app\admin\controller;


class Position extends Base
{

    public function index(){

        $res = db('position')->select()->toArray();
        if(!empty($res)){
            $this->assign('res',$res);
        }else{
            $this->redirect('position/add');
        }
        return view();
    }


    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $data['base_salary'] = (float)$data['base_salary'];
            $data['business_percentage'] = (float)$data['business_percentage'];
            if(empty($data['sort'])){
                $data['sort'] = 1;
            }
            $res = db('position')->insert($data);
            if($res){
                $this->redirect('position/index');
            }
        }
        //部门的展示
        $department_res = db('department')->select()->toArray();
        $this->assign('department_res',$department_res);
        return view();
    }


    public function delete(){
        $id = input('id');
        $res = db('position')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('position/index');
        }
    }


    public function edit(){
        $id = input('id');
        $res = db('position')->where('id','=',$id)->find();
        $department_res = db('department')->select()->toArray();
        $this->assign('res',$res);
        $this->assign('department_res',$department_res);

        if(request()->isPost()){
            $data = input('post.');
            $data['base_salary'] = (float)$data['base_salary'];
            $data['business_percentage'] = (float)$data['business_percentage'];
            $res = db('position')->where('id','=',$data['id'])->update($data);
            if($res){
                $this->redirect('position/index');
            }
        }

        return view();
    }
}