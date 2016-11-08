<?php
/**********************************获取URL路由进行重写*************************************/
/********************URL已兼容http://localhost/WEB/index.php/Blog/index/id/1***************/
/********************URL已兼容http://localhost/WEB/index.php?c=Blog&a=Index&id=1***********/
if (isset($_SERVER['PATH_INFO']) && empty($_GET)) {
    $hz=strrchr($_SERVER['PATH_INFO'],'.');//伪静态后缀
    $url_arr = explode('/', str_replace($hz,'',$_SERVER['PATH_INFO']));      //获取index.php后的参数进行处理
//    var_dump($url_arr);
//    die();
    $cou = count($url_arr);
    //判断进行赋值，
    if ($cou >= 3) {
        $_GET['m'] = count($url_arr) % 2 != 0 ? null : $url_arr[1];
        $_GET['c'] = count($url_arr) % 2 != 0 ? $url_arr[1] : $url_arr[2];
        $_GET['a'] = count($url_arr) % 2 != 0 ? $url_arr[2] : $url_arr[3];

        //判断分析后获得后面传递的参数 如（/id/2 同 &id=2）将其转换传入$_GET数组中。
        if ($_GET['m'] === null) {
            if ($cou >= 5) {
                for ($i = 3; $i < ($cou - 3) / 2 + 3; $i++) {
                    $_GET[$url_arr[$i * 2 - 3]] = $url_arr[($i * 2 - 2)];
                }
            }
        } else {
            if ($cou >= 6) {
                for ($i = 4; $i < ($cou - 4) / 2 + 4; $i++) {
                    $_GET[$url_arr[$i * 2 - 4]] = $url_arr[($i * 2 - 3)];
                }
            }
        }
    }
}
//无论你使用什么模式。最后都将参数赋值
$m = isset($_GET['m']) ? ucfirst($_GET['m']) : 'Home';
$c = isset($_GET['c']) ? ucfirst($_GET['c']) : 'Index';
$a = isset($_GET['a']) ? $_GET['a'] : 'index';

//将路由get传递过来的参数定义为常量方便后面使用
define('MODULE', $m);
define('CONTROLLER', $c);
define('ACTION', $a);
/************************************END********************************************/


/******************************系统信息并获得环境根目录************************************/
// 系统信息
if(version_compare(PHP_VERSION,'5.4.0','<')) {
    ini_set('magic_quotes_runtime',0);
    define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()? true : false);
}else{
    define('MAGIC_QUOTES_GPC',false);
}
define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    //定义环境根目录，针对于项目图片，CSS,JS文件使用
    if(!defined('__ROOT__')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
    }
}
/**************************************END************************************/

/**************************************路径常量************************************/
define('MINGPHP_PATH', ROOT . 'MingPHP/');                             //框架目录路径
define('CLASS_PATH', MINGPHP_PATH . 'Core/');                       //核心文件类目录路径
define('CONFIG_PATH', MINGPHP_PATH . 'Config/');                     //框架config文件目录路径
define('SMARTY_PATH', MINGPHP_PATH . 'Smarty/Smarty.class.php');     //Smarty核心文件
define('RUNTIME', APP_PATH . 'Runtime/');                          //缓存目录
define('__PUBLIC__', __ROOT__ . '/Public/');                            //Public目录
define('__APP__', __ROOT__ .'/'/*. '/index.php'*/);                            //首页路径目录
define('__MODULE__', __APP__ .MODULE.'/');                        //模型环境路径常量
define('__CONTROLLER__', __MODULE__ .CONTROLLER.'/');                 //控制器环境路径常量
define('__SELF__', __CONTROLLER__.ACTION);                            //当前方法环境路径常量
// 定义当前请求的系统常量
define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
define('IS_AJAX',       ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['ajax']) || !empty($_GET['ajax'])) ? true : false);
define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);
define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false);

define('PUBLIC_HOME',__PUBLIC__.'home/');
define('PUBLIC_ADMIN',__PUBLIC__.'admin/');
define('ADMIN_CSS',__PUBLIC__.'admin/css/');
define('ADMIN_JS',__PUBLIC__.'admin/js/');
define('ADMIN_IMG',__PUBLIC__.'admin/img/');
define('HOME_CSS',__PUBLIC__.'home/css/');
define('HOME_JS', __PUBLIC__.'home/js/');
define('HOME_IMG',__PUBLIC__.'home/img/');

/************************************END********************************************/



/**********************************配置文件合并******************************************/

$config_arr1 = include_once CONFIG_PATH . 'config.php';               //核心配置文件数组
$config_arr2 = include_once APP_PATH . 'Common/config/config.php';   //项目公共配置文件
$config_row = array_merge($config_arr1, $config_arr2);             //合并配置文件数组
/************************************END********************************************/


/********************************引入框架核心函数库文件***************************************/
include_once CONFIG_PATH . 'function.php';              //引入核心函数库
/************************************END****************************************************/


/*************************************访问的对应控制器****************************************/
//根据路径判断M,C,A 要访问的控制器，利用命名空间，实现自动加载。
$controller_namespace =  MODULE . '\Controller\\' . CONTROLLER . 'Controller';
$controller = new $controller_namespace();      //利用自动加载将对应的控制器实例化
$controller->$a();                                //调用对应$_GET['a']，方法
/************************************END*****************************************************/
