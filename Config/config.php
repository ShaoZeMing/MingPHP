<?php
/****************框架核心配置文件******************************/
return array(


    'APP' => $_SERVER['SCRIPT_NAME'],                        //入口文件路径
    'MODULE' => $_SERVER['SCRIPT_NAME'] . '/' . MODULE,          //当前模型路径
    'CONTROLLER' => $_SERVER['SCRIPT_NAME'] . '/' . MODULE . '/' . CONTROLLER,  //当前控制器路径


    'URL_FIX'   => '.html',   //伪静态后缀

    'AUTO_LOGIN_TIME' => 3600*24*7,    //自动登录过期时间
    'ACTIVATION'   =>3600*24,        //邮箱激活有效时间。
    'ENCRYPTION_KEY' => 'MING',    // 秘钥 加密需要点

//    'ADMIN_VIEW_COMMON' => APP_PATH . '/Admin/View/Common/',          //后台视图模板公共文件目录路劲


    'DBTYPE' => 'mysql',
    'DBHOST' => 'localhost',
    'DBUSER' => 'root',
    'DBPWD' => '12315Smm',
    'DBNAME' => '',
    'DBFIX' => '',
    'DBPORT' => '3306',
    'DB_CHARSET' => 'UTF8',
    'LEFT_DELIMITER' => '<{', //模板定界符左
    'RIGHT_DELIMITER  ' => '}>', //模板定界符右


);