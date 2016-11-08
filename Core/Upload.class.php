<?php

/**
 * Created by PhpStorm.
 * User: 4d4k
 * Date: 2016/8/17
 * Time: 14:12
 * author:shaoZeMing
 *
 */
namespace MingPHP\Core;
class  Upload
{
    //配置参数
    private $config = array(
        'maxSize' => 2 * 1024 * 1024,                              //上传文件最大值，0为不限制
        'exts' => array('.jpg','.jpeg', '.png', '.gif'),                   //允许上传是文件后缀名
        'mimes' => array('image/jpeg', 'image/gif', 'image/png'),  //允许上传文件的mime类型
        'dirName' => array('date', 'Y-m-d'),                       //子目录名字，（0：函数名，1：参数）
        'saveName' => array('uniqid', ''),                         //设置文件名字，（0：函数名，1：参数）
        'saveExt' => '',                                           // 设置文件后缀，默认原后缀
        'rootPath' => './upload/',                                 //上传默认根目录
        'dirPath' => '',                                           //子目录路径
        'ifSaveFname' => true,                                     //是否更改文件名，默认更改为saveName

    );

    //上传文件名
    private $_filename;

    //错误提示属性
//    private $_error;

    //上传文件的基本信息
    private $_files_arr = array();

    //构造方法，对必要属性初始化。
    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, $config);  //合并数组，
    }

    //上传方法， 输入上传文件详细信息
    // $files_aff 一维数组
    public function upload(array $files_arr)
    {
        if(empty($files_arr)){
            die('未找到需要上传的文件！');
        }else {
            $this->_files_arr = $files_arr;      //将文件信息赋值给属性
//            $name=$files_arr['name'];
            if ($this->checkSize()) {
                if ($this->checkExt()) {
                    if ($this->checkMime()) {
                        $pathArr = $this->getPath();
                        @mkdir($pathArr[0], 0777, true);           //创建文件上传目录
                        if (move_uploaded_file(($this->_files_arr['tmp_name']), ($pathArr[0] . $pathArr[1]))) {
                             return $pathArr;
                        } else {
                            echo '<script>alert("文件上传失败！")</script>';
                            return false;
                        }
                    } else {
                        echo '<script>alert("文件类型非法，上传失败！")</script>';                              return false;
                        return false;
                    }
                } else {
                    echo '<script>alert("文件后缀名不支持，上传失败！")</script>';
                    return false;
                }
            } else {
                echo '<script>alert("文件过大，上传失败！")</script>';
                return false;

            }
        }

    }

    //判断上传文件大小是否合法
    private function checkSize()
    {
        return ($this->_files_arr['size']) < ($max_size = $this->config['maxSize']) ? true : false;

    }

    //判断文件后缀名是否合法
    private function checkExt()
    {
        $filename = $this->_files_arr['name'];
        $ext = strtolower(trim(strrchr($filename, '.')));                      //获取源文件后缀
        $type_arr = $this->config['exts'];
        return in_array($ext, $type_arr) ? true : false;


    }

    //判断文件mime类型是否合法
    private function checkMime()
    {
        $finfo= new \finfo(FILEINFO_MIME_TYPE);
        $_mime=$finfo->file(($this->_files_arr['tmp_name']));
        return in_array($_mime, ($max_size = $this->config['mimes'])) ? true : false;

    }

    //判断属性获取上传路径和文件名。
    //返回一个array(),一个为终目录路径array[0]，一个为最终文件名称array[1]，
    private function getPath()
    {
        $filename = iconv('utf-8', 'gbk', ($this->_files_arr['name']));               //获取源文件name
        $ext = strtolower(trim(strrchr($filename, '.')));                      //获取源文件后缀

        $fun_dn = $this->config['dirName'][0];                //识别创建子目录文件夹名函数
        $gs_dn = $this->config['dirName'][1];                 //函数格式
        $fun_fn = $this->config['saveName'][0];               //识别创建子文件夹名函数
        $gs_fn = $this->config['saveName'][1];                //函数格式

        $s_ext = $this->config['saveExt'] == '' ? $ext : $this->config['saveExt']; //最终后缀名
        $dir_path = $this->config['rootPath'] . $this->config['dirPath'] . $fun_dn($gs_dn) . '/';    //拼装子目录路径
        $f_name = $this->config['ifSaveFname'] ? $fun_fn($gs_fn) . $s_ext : $filename;    //最终文件名

        return array($dir_path, $f_name);      //返回一个含子目录路径，和最终文件名。
    }

    // 多文件上传方法
    public function uploadAll(array $files_arr)
    {
        $arr=array();
        $filearr = array();
        $count = count($files_arr['size']);          //识别文件个数
        for ($i = 0; $i < $count; $i++) {            //循环上传文件
            foreach ($files_arr as $k => $v) {
                if($v[$i]==='' || ($files_arr['error'][$i]!==0)){    //判断是否有文件选择框未提交文件。
                    $filearr=array();                        //将
                    continue;
                }else{
                    $filearr[$k] = $v[$i];                //将总文件参数拆分成各个单独文件参数。

                }
            }
            $arr[]= $this->upload($filearr);               //循环调用upload上传方法，将文件循环上传。
        }
        return $arr;


    }

    //错误提示方法待定
    private function error()
    {

    }


}