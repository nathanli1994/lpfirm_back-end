<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/24
 * Time: 13:53
 */

namespace app\admin\controller;


class Visatype extends Base
{

    public function index(){

        $res = db('visatype')->select()->toArray();

        if(empty($res)){
            $this->redirect('visatype/add');
        }else{
            $this->assign('res',$res);
        }
        return view();
    }


    public function add(){

        if(request()->isPost()){
            $data = input('post.');
            $res = db('visatype')->insert($data);

            if($res){
                $this->redirect('visatype/index');
            }else{
                $this->error('添加失败..','visatupe/index');
            }
        }
        return view();
    }


    public function delete(){
        $id = input('id');
        $res = db('visatype')->where('id','=',$id)->delete();
        if($res){
            $this->redirect('visatype/index');
        }
    }
}