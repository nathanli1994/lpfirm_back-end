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


    public function total(){

        $user_count = db('user')->count();
        $department_count = db('department')->count();
        $position_count_total = db('position')->count();
        $this->assign('user_count',$user_count);
        $this->assign('department_count',$department_count);
        $this->assign('position_count_total',$position_count_total);


        //每个部门下有多少个职位
        $department_res = db('department')->select()->toArray();
        foreach ($department_res as $k=>$v){
            $department_name[]['department_name'] = $v['name'];//部门名称
        }
        foreach ($department_name as $k1=>$v1){
            $department_position_count[$k1]['count'] = db('position')->where('department_name','like',$v1['department_name'])->count();
            $department_position_count[$k1]['department_name'] = $v1['department_name'];
        }

        $num_arr_to_str = '';
        foreach ($department_position_count as $k2=>$v2){
            $count[] = $v2['count'];
            $num_arr_to_str = implode(',',$count);
        }
        foreach ($department_position_count as $k2=>$v2){
            $name_arr_to_str[] = $v2['department_name'];
        }
        $this->assign('department_name',$department_name);
        $this->assign('department_position_count',$department_position_count);
        $this->assign('num_arr_to_str',$num_arr_to_str);
        $this->assign('name_arr_to_str',$name_arr_to_str);
        //每个职位里面有多少人
        $position_res = db('position')->select()->toArray();
        foreach ($position_res as $k=>$v){
            $position_name[]['name'] = $v['name'];
        }
        foreach ($position_name as $k=>$v){
            $position_people[$k]['name'] = $v['name'];
            $position_people[$k]['count'] = db('user')->where('position_name','like',$v['name'])->count();
        }
        $this->assign('position_people',$position_people);
        return view();
    }
}