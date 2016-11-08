<?php
/**
 * Created by PhpStorm.
 * User: 4d4k
 * Date: 2016/8/26
 * Time: 21:30
 */

namespace MingPHP\Core;
//require CLASS_PATH . 'Db.class.php';
class Model extends Db
{
    protected function redirect($url,$date,$str){
        redirect($url,$date,$str);
    }

    protected function error($str){

        echo '<script>alert("'.$str.'");history.go(-1)</script>';

    }



}