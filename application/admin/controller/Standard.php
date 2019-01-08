<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/17
 * Time: 13:35
 */

namespace app\admin\controller;


class Standard extends Base
{

    public function index(){

        $res = db('standard')->select()->toArray();

        if(!empty($res)){
            $this->assign('res',$res);
        }else{
            $this->redirect('standard/add');
        }

        return view();
    }


    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            $data['section_min'] = (float)$data['section_min'];
            $data['section_max'] = (float)$data['section_max'];
            $data['commission'] = (float)$data['commission'];
			if(empty($data['sort'])){
				$data['sort'] = 1;
			}
            $res = db('standard')->insert($data);
            if($res){
                $this->redirect('standard/index');
            }
        }
        $position_res = db('position')->select()->toArray();
        $this->assign('position_res',$position_res);
        return view();
    }


    public function delete(){
        $id = input('id');
        $res = db('standard')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('standard/index');
        }
    }


    public function edit(){
        $id = input('id');
        $res = db('standard')->where('id','=',$id)->find();
        $position_res = db('position')->select()->toArray();
        $this->assign('res',$res);
        $this->assign('position_res',$position_res);

        if(request()->isPost()){
            $data = input('post.');
            $data['section_min'] = (float)$data['section_min'];
            $data['section_max'] = (float)$data['section_max'];
            $data['commission'] = (float)$data['commission'];

            $res = db('standard')->where('id','=',$data['id'])->update($data);
            if($res){
                $this->redirect('standard/index');
            }
        }

        return view();
    }
}