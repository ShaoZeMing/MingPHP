<?php

/**
 * Created by PhpStorm.
 * User: 4d4k
 * Date: 2016/5/11
 * Time: 11:44
 */
namespace MingPHP\Core;
use PDO;
class Db
{
    protected $_pdo;                    //PDO对象
    protected $table;                   //表名
    protected $opt = array();           //SQL语句拼方法数组
    protected $pk;                     //表主键

    function  __construct($tab_name)
    {

        $this->config($tab_name);
    }

    //配置方法
    function  config($tab_name)
    {
        try {
            $host = C('DBTYPE') . ':host=' . C('DBHOST') . ';dbname=' . C('DBNAME') . ';charset=' . C('DB_CHARSET');
            $this->_pdo = new PDO($host, C('DBUSER'), C('DBPWD'));
        } catch (PDOException $e) {
            echo '链接失败。错误：' . $e->getMessage();
        }
        $this->table = C('DBFIX') . $tab_name;
        $this->tbField();
        $this->opt['field'] = '*';
        $this->opt['where'] = $this->opt['limit'] = $this->opt['group'] = $this->opt['order'] = '';

    }

//查询表字段,并获的主键
    function tbField()
    {
        $result = $this
            ->query("DESC $this->table");
        foreach($result as $v){
            if($v['Key'] == 'PRI'){
                $this->pk=$v['Field'];
            }
        }
    }


//field字段查询
    function field($field)
    {
        $this->opt['field'] = is_string($field) ? $field : '';
        return $this;
    }

//where条件方法
    function where($where)
    {
        if(is_array($where)){
            $key=array_keys($where);
            $value=array_values($where);
            $val='';
            for($i=0;$i<count($where);$i++){
                $val .= '`'.$key[$i].'` = "'.$value[$i].'" AND ';
            }
            $where=mb_substr($val,0,-5);
            $this->opt['where'] = " WHERE " . $where ;
        }
        $this->opt['where'] = is_string($where) ? " WHERE " . $where : '';
        return $this;
    }

//limit条件方法
    function limit($limit)
    {
        $this->opt['limit'] = is_string($limit) ? " LIMIT " . $limit : '';
        return $this;
    }

//order排序方法
    function order($order)
    {
        $this->opt['order'] = is_string($order) ? " ORDER BY " . $order : '';
        return $this;
    }

//group 分组方法
    function group($group)
    {
        $this->opt['group'] = is_string($group) ? " GROUP BY " . $group : '';
        return $this;
    }

//select查询方法
    function select()
    {
        $sql = "SELECT {$this->opt['field']} FROM {$this->table} {$this->opt['where']} {$this->opt['group']} {$this->opt['order']} {$this->opt['limit']} ";
//      echo $sql;
        return $this->query($sql);

    }

//count
    function count()
    {
        $sql = "SELECT COUNT(*) as n FROM {$this->table} {$this->opt['where']} {$this->opt['group']} ";
        $res=$this->query($sql);
        return $res[0]['n'];
    }
//find查询单条记录
    function find($id='')
    {
        if ($id=='' && empty($this->opt['where']) ) {
            die('错误提示：调用find()查询单条语句需要给where条件或传入主键值');
        }
        if(!empty($id)){
            $sql = "SELECT {$this->opt['field']} FROM {$this->table} WHERE `{$this->pk}` = $id";
        } else {
            $sql = "SELECT {$this->opt['field']} FROM {$this->table}{$this->opt['where']} ";
        }
        $result=$this->_pdo->query($sql);
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    //   delete删除方法
    function delete($id = '')
    {
        if ($id == '' && empty($this->opt['where'])) {
            die('没有条件，不能删除！');
        }
        if (is_array($id)) {
            $id = implode(',', $id);
            $this->opt['where'] = "WHERE `{$this->pk}` in ($id)";
        }else{
            $this->opt['where'] = "WHERE `{$this->pk}` in ($id)";
        }
        $sql = "DELETE FROM {$this->table} {$this->opt['where']}  {$this->opt['limit']}";
        var_dump($sql);
        return $this->exec($sql);

    }

    //insert 添加方法
    function  insert($insert)
    {
        is_array($insert) or die("插入数据非数组");
        $fields = $this->fields(array_keys($insert));
        $values = $this->value(array_values($insert));
        $sql = "INSERT INTO {$this->table}($fields) VALUES ($values) ";
//print_r($sql."<br/>");
        if ($row = $this->exec($sql) !== false) {
            return $row;
        } else {
            return false;
        }

    }

//查询字段方法
    function fields($field)
    {
        //判断字段类型，
        $fieldAll = is_string($field) ? explode(',', $field) : $field;    //将字符串拆分为数组
        if (is_array($fieldAll)) {
            $field = '';
            foreach ($fieldAll as $v) {                              //将数组遍历
                $field .= '`' . $v . '`' . ',';                            //为字段两边添加 ` ;
            }
            return rtrim($field, ',');
        }
    }

    //value 插入数据转译方法
    function  value($value)
    {
        $strvalue = '';
        foreach ($value as $v) {
            $strvalue .= '"'. addslashes($v) .'",';
        }
//        foreach ( $value as $v ) {
//            $strvalue .= "'$v',";
//        }
        return rtrim($strvalue, ',');
    }

    //update更新语句方法
    function update( $data)
    {
        $pk=$this->pk;
        if (empty($this->opt['where']) && empty($data[$pk])) {
            die("没有where条件");
        }
        if( empty($this->opt['where']) && !empty($data[$pk])) {
            $this->opt['where'] = ' WHERE `' . $pk . '`="' . $data[$pk] . '"';
            unset($data[$pk]);
        }
        if(is_array($data)){
             $str = '';
            while (list($k, $v) = each($data)) {
                $v = addslashes($v);
                $str .= '`'.$k .'` ="' . $v . '",';
            }
            $str = rtrim($str, ',');
        }else{
            $str = is_string($data)? $data :'';
        }

            $sql = "UPDATE {$this->table} SET  $str {$this->opt['where']}";
//var_dump($sql);
//        die();
            return $this->exec($sql);

    }


//有结果返回方法
    function query($sql)
    {
        $result = $this->_pdo->query($sql) or die($this->dbError());
        //    var_dump($result);
            $resultArr = $result->fetchAll(PDO::FETCH_ASSOC);
        return $resultArr;
    }

//无结果返回方法

    function exec($sql)
    {
         $this->_pdo->exec($sql) or die($this->dbError());
        $id = $this->_pdo->lastInsertId();

        return $id;
    }

    //错误返回返回
    function dbError()
    {
        return $this->_pdo->errorInfo();
    }


    function lastInsertId(){
       return $this->_pdo->lastInsertId();
    }
}
