<?php

/******************************8*****************************************************/
/********************************核心函数文件库**************************************/
/**************************8*********************************************************/
//__aotulode()自动加载函数有不足的地方，就是当你自动加载一个文件时，
//加载的文件中还需要自动加载文件时，这个函数就显得力不从心，具体为什么，我也不知道。
//可以使用spl_autoload_register()定义一个匿名函数进行加载。就可以实现自动加载的文件中
//还需要自动加载文件这个需求。
/*function _autoload($name)
{
    echo $name . '<BR>';
    $name = str_replace('\\', '/', $name);
    echo $name . '<br>';
    @include_once APP_PATH . $name . '.class.php';
}*/
//调用该函数创建一个自动加载的匿名函数
spl_autoload_register(function ($name) {
    $arr = explode('\\', $name);
    if ($arr[0] == 'MingPHP') {
        $path = ROOT;
    } else {
        $path = APP_PATH;
    }
    $path = $path . $name . '.class.php';
    $path = str_replace('\\', '/', $path);
//    echo$path.'<br>';
    @include_once $path;
});

//C方法，将配置文件数组进行提取配置
function C($k)
{
    global $config_row;
    return $config_row[$k];
}

//创建一个M函数实例化基本核心模型数据库操作类
function M($table)
{
    static $db_arr = array();
    if (empty($db_arr[$table])) {
        $db_arr[$table] = new MingPHP\Core\Model($table);
    }
    return $db_arr[$table];
}

//
/*
 * @function D  实例化对应的模型类文件，若文件不存在，则实例化基本核心模型。
 * @parameter  $table 表名 当$type==true,则输入自定义目标Model
 * @parameter  $type  属性，默认false,
 *
 */

function D($table,$type=false)
{
    if($type){
        $model=$table.'Model';
        $table=basename($table);
    }else{
        $model =  '\\'.MODULE . '\Model\\' . $table . 'Model';
    }
    $file = APP_PATH . $model . '.class.php';
    if (file_exists($file)) {
        static $model_arr = array();
        if (empty($model_arr[$table])) {
            $model_arr[$table] = new $model($table);
        }
        return $model_arr[$table];
    } else {
        return M($table);
    }
}


//$_POST数据过滤实体化操作函数
function _post($str,$type=false){
    if($type){
        return htmlspecialchars_decode($str);
    }
    return htmlspecialchars(trim($str));
}

//打印数组方法
function p($arr)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

//删除文件，当文件删除后目录为空时，递归将空目录删除
function del_File($file)
{
    if (is_file($file)) {
        unlink($file);
        $file = dirname($file);
    }
    if (is_dir($file)) {
        $p = @opendir($file);
        $i = 0;
        while (readdir($p)) {
            $i++;
        }
        closedir($p);
        if ($i <= 2) {
            $father_dir = dirname($file);
            @rmdir($file);
            del_File($father_dir);
        }
    }
}

////删除目录下所有子目录和文件
function delDirAll($dir){
    if(is_dir($dir)){
        $dir_res=opendir($dir);
        while(($filename=readdir($dir_res)) !== false){
            if($filename != '.' && $filename != '..'){
                $url=$dir.'/'.$filename;
                if(is_dir($url)){
                    delDirAll($url);
                }else{
                    unlink($url);
                }
            }
        }
        closedir($dir_res);     //一定要关闭资源，否则报错
        rmdir($dir);

    }elseif(is_file($dir)){
        unlink($dir);
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/** 自定义加密方法
 * @param string $value 需要加密或解密点数据
 * @param boolean $type 是否解密（默认为加密）
 * @return string  返回字符（加密或解密）
 */
function encryption($value, $type = 0)
{
    $key =md5( C('ENCRYPTION_KEY')).md5( C('ENCRYPTION_KEY'));
    if (empty($type)) {
        $value = str_replace('=', '', base64_encode($value ^ $key));
        return $value;
    }else {
        $value = base64_decode($value);
        return $value ^ $key;
    }

}


/** //session 函数
 * @param string $k session键值
 * @param string $$val 是否赋值（默认null）
 * @return string  返回session值
 */
function session($k='',$val=null){
    if(!isset($_SESSION)){
        session_start();
    }
    if($val!== null){
        $_SESSION[$k]=$val;
    }else if($k){
        if(isset($_SESSION[$k])){
            return $_SESSION[$k];
        }else{
            return null;
        }
    }else{
        return $_SESSION;
    }
}


/** //dateDay 函数
 * @param int $date 时间戳
 * @return string  对应点时间样式
 */
function dateStyle($date){

    $val='';
    $date=time()-$date;
    switch($date){
        case 0:
            $val="刚刚";
            break;
        case $date<60:
            $val=floor($date/1)."秒前";
            break;
        case $date<3600:
            $val=floor($date/60)."分钟前";
            break;
        case $date<3600*24:
            $val=floor($date/3600)."小时前";
            break;
        case $date<=3600*24*30:
            $val=floor($date/(3600*24))."天前";
            break;
        case $date<=3600*24*30*12:
            $val=floor($date/(3600*24*30)) ."个月前";
            break;
        default:
            $val=date('Y/m/d',$date);
    }
    return$val;
}


/*
 * U()  function  生成伪静态路径
 * @param  string  $url 路径参数
 * @return string  生成点绝对路劲。
 * */

function U($path='',$query=''){
    $url=!empty($path) ? $path: 'home/index/index';
    $path= explode('/',$url);
    $count=count($path);
    switch($count){
        case 3:
            $path=$url;
            break;
        case 2:
            $path=__MODULE__.$url;
            break;
        case 1:
            $path=__CONTROLLER__.$url;
            break;
    }
    $url='http://'.$_SERVER['HTTP_HOST'].$path.$query.C('URL_FIX');
    return $url;
}




