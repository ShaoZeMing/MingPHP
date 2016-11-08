<?php
/**
 * Created by PhpStorm.
 * User: 4d4k
 * Date: 2016/8/26
 * Time: 20:23
 */

namespace MingPHP\Core;

class Controller
{
    private $sarty;

    function __construct()
    {

        $this->config();

    }

    protected function config()
    {
        include_once SMARTY_PATH; //引入Smarty.class.php  //文件
        $this->sarty = new \Smarty();
        $this->sarty->left_delimiter = C('LEFT_DELIMITER');
        $this->sarty->right_delimiter = C('RIGHT_DELIMITER');
        $this->sarty->setTemplateDir(APP_PATH . MODULE . '/View/' . CONTROLLER . '/');
        $this->sarty->setCompileDir(RUNTIME . MODULE . '/tpl_c/');


    }

    protected function assign($val1, $val2)
    {
        $this->sarty->assign($val1, $val2);
    }

    protected function display($tpl = ACTION . '.html')
    {
        $this->sarty->display($tpl);
    }

    protected function redirect($url,$date,$str){
        redirect($url,$date,$str);
    }

    protected function error($str){

        echo '<script>alert("'.$str.'");history.go(-1)</script>';

    }

}

