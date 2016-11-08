<?php
/**
 * Created by PhpStorm.
 * User: 4d4k
 * Date: 2016/8/25
 * Time: 11:01
 */
include '../Smarty.class.php';

//set_error_handler('myError_handler');
$strs='';



gettype();

$smarty = new Smarty();
$smarty->left_delimiter='<{';
$smarty->right_delimiter='}>';
$smarty->setTemplateDir('tpls');
$smarty->setCompileDir('tpls_c');
$smarty->setCacheDir('cache');
$smarty->caching=0;

$girl='yafei';
$boy= 'shaoming';

$smarty->assign('boy',$boy);
$smarty->assign('girl',$girl);

$smarty->display('index.html');
echo '<hr>';
var_dump($strs);

function myError_handler($a1,$a2,$a3,$a4){
    global $strs;
    $strs .= $a1.$a2.$a3.$a4.'<br>';
    return $strs;
}

try {
    $error = ' this error';
    throw new Exception($error);

    // Code following an exception is not executed.
    echo 'hahahahah';

} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

// Continue execution
echo 'Hello World';

