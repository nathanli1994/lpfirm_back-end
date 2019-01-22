<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/14
 * Time: 22:56
 */

namespace app\admin\controller;


use think\Controller;
use PHPExcel_IOFactory;
use PHPExcel;
use PHPExcel_Writer_Excel5;

class Base extends Controller
{
    /*
     *  1.公共控制器，其他控制器在执行方法之前，会先执行公共控制器的方法
     */
    public function _initialize()
    {
        if(is_null(session('name'))){
            $this->redirect('login/index');
        }
    }

    /*
   * $name为表单上传的name值
   * $filePath为为保存在入口文件夹public下面uploads/下面的文件夹名称，没有的话会自动创建
   * $width指定缩略宽度
   * $height指定缩略高度
   * 自动生成的缩略图保存在$filePath文件夹下面的thumb文件夹里，自动创建
   * @return array 一个是图片路径，一个是缩略图路径，如下：
   * array(2) {
     ["img"] => string(57) "uploads/img/20171211\3d4ca4098a8fb0f90e5f53fd7cd71535.jpg"
     ["thumb_img"] => string(63) "uploads/img/thumb/20171211/3d4ca4098a8fb0f90e5f53fd7cd71535.jpg"
    }
   */
    protected function uploadFile($name,$filePath,$width,$height)
    {
        $file = request()->file($name);
        if($file){
            $filePaths = ROOT_PATH . 'public' . DS . 'uploads' . DS .$filePath;
            if(!file_exists($filePaths)){
                mkdir($filePaths,0777,true);
            }
            $info = $file->move($filePaths);
            if($info){
                $imgpath = 'uploads/'.$filePath.'/'.$info->getSaveName();
                $image = \think\Image::open($imgpath);
                $date_path = 'uploads/'.$filePath.'/thumb/'.date('Ymd');
                if(!file_exists($date_path)){
                    mkdir($date_path,0777,true);
                }
                $thumb_path = $date_path.'/'.$info->getFilename();
                $image->thumb($width, $height)->save($thumb_path);
                $data['img'] = $imgpath;
                $data['thumb_img'] = $thumb_path;
                return $data;
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }



    public function solve_on_off($data){
        //这里要解决on off 变成 0和1的问题
        if(!key_exists('is_export',$data)){
            $data['is_export'] = 0;
            unset($data['export_to']);
        }else{
            $data['is_export'] = 1;
        }

        if(!key_exists('is_remind',$data)){
            $data['is_remind'] = 0;
            unset($data['remind']);
        }else{
            $data['is_remind'] = 1;
        }

        if(!key_exists('is_marry',$data)){
            $data['is_marry'] = 0;
            unset($data['marry']);
        }else{
            $data['is_marry'] = 1;
        }

        if(!key_exists('is_worker',$data)){
            $data['is_worker'] = 0;
            unset($data['worker']);
        }else{
            $data['is_worker'] = 1;
        }

        if(!key_exists('is_reject',$data)){
            $data['is_reject'] = 0;
            unset($data['reject']);
        }else{
            $data['is_reject'] = 1;
        }

        if(!key_exists('is_criminal',$data)){
            $data['is_criminal'] = 0;
            unset($data['criminal']);
        }else{
            $data['is_criminal'] = 1;
        }

        if(!key_exists('is_education',$data)){
            $data['is_education'] = 0;
            unset($data['education']);
        }else{
            $data['is_education'] = 1;
        }

        if(!key_exists('in_progress',$data)){

        }else{
            $data['in_progress'] = 1;
        }
        return $data;
    }



    public function exportExcel($list,$filename,$indexKey=array(),$level=''){
        // dirname(__FILE__)得到当前文件所在目录名字
//        require_once dirname(dirname(__FILE__)) . '/Lib/PHPExcel/PHPExcel/PHPExcel/IOFactory.php';
//        require_once dirname(dirname(__FILE__)) . '/Lib/PHPExcel/PHPExcel/PHPExcel.php';
//        require_once dirname(dirname(__FILE__)) . '/Lib/PHPExcel/PHPExcel/PHPExcel/Writer/Excel2007.php';

        //excel的A-Z的部分
        $header_arr = array('A','C','E','G','I');

        if($level == 'personal'){
            $template_personal = dirname(dirname(dirname(dirname(__FILE__)))).'/vendor/phpoffice/personal.xlsx';			//使用模板
            $objPHPExcel = PHPExcel_IOFactory::load($template_personal);  	//加载excel文件,设置模板
        }else{
            $template_company = dirname(dirname(dirname(dirname(__FILE__)))).'/vendor/phpoffice/company.xlsx';			//使用模板
            $objPHPExcel = PHPExcel_IOFactory::load($template_company);  	//加载excel文件,设置模板
        }



        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);	//设置保存版本格式

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A4',  "公司名称：加诺咨询");

        if($level == 'personal'){
            $objActSheet->setCellValue('C4',  "员工姓名：" . session('name'));
        }

        $objActSheet->setCellValue('H4',  "导出时间：".date('Y-m-d'));
        $i = 8;
        foreach ($list as $row) {//list里的每条数据是一行
            foreach ($indexKey as $key => $value){
                //这里是设置单元格的内容
                $objActSheet->setCellValue($header_arr[$key].$i,$row[$value]);
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        //$objWriter->save($filename.'.xls');

        // 2.接下来当然是下载这个表格了，在浏览器输出就好了
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$filename.'.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

}