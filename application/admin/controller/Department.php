<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/15
 * Time: 11:38
 */

namespace app\admin\controller;

use think\paginator;
class Department extends Base
{

    public function index(){

        $res = db('department')->select()->toArray();
        if(!empty($res)){
            $this->assign('res',$res);
        }else{
            $this->redirect('department/add');
        }
        return view();
    }


    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            if(!is_null($data['name'])){
                if(empty($data['sort'])){
                    $data['sort'] = 1;
                }
                $res = db('department')->insert($data);
                if($res){
                    $this->redirect('department/index');
                }
            }else{
                $this->error('添加失败....','department/index');
            }
        }
        return view();
    }


    public function delete(){
        $id = input('id');
        $res = db('department')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('department/index');
        }
    }


    public function edit(){

        if(request()->isPost()) {
            $data = input('post.');

            $res = db('department')->where('id', '=', $data['id'])->update($data);
            if ($res) {
                $this->redirect('department/index');
            } else {
                $this->error('更新失败....', 'department/index');
            }
        }

            $id = input('id');
            $department_res = db('department')->where('id','=',$id)->find();
            $this->assign('res',$department_res);
            return view();
        }
}