<?php

/**
 * Created by PhpStorm.
 * User: 4d4k
 * Date: 2016/8/17
 * Time: 21:33
 * author:shaoZeMing
 */
namespace MingPHP\Core;
class  Page
{
    private $total;             //总条数
    private $page_rows;         //总页数
    private $page_ones;          //每一页条数
    private $self_page;         //当前页
    private $l_id;          //每页首行id
    private $n_id;          //每页最后id
    private $pages;              //显示几个页按钮
    private $url;                //URL处理
    private $name;                //页码文字


    public function __construct($total, $page_ones = 10, $pages = 5, $arr_str = array())
    {
        $this->config($total, $page_ones, $pages);  //调用配置方法配置属性
        $this->url = $this->getUrl();              //调用url配置方法获取url
        $this->name = $this->getName($arr_str);    //调用标签方法对后缀名称更改
    }

    //配置方法
    private function config($total, $page_ones, $pages)
    {
        $this->total = $total;                         //总条数
        $this->page_ones = $page_ones;                //每页显示条数
        $this->pages = $pages;                        // 显示页码按钮数
        $this->page_rows = ceil($total / $page_ones);   //获取总页数
        $this->self_page = min($this->page_rows, max(isset($_GET['page'])?$_GET['page']:(isset($_POST['page'])?$_POST['page']:0), 1));   //获取当前页
        $this->l_id = ($this->self_page - 1) * $this->page_ones + 1;      //每页第一条数据id
        $this->n_id = min($this->l_id + $this->page_ones -1, $this->total); //每页最后一条ID

    }

    //配置路由url
    public function getUrl()
    {
        //识别url并获取
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
        $arr_url = parse_url($url);     //拆分url为数组
        if( isset($arr_url['path'])){
            $arr_url['path']=str_replace(strrchr($arr_url['path'],'.'),'',$arr_url['path']);
        }

        if (isset($arr_url['query'])) {
            parse_str($arr_url['query'], $arr);        //将?后的GET参数全部转换为数组
            unset($arr['page']);                     //删除page=xx的键
            $url_now = $arr_url['path'] . '?' . http_build_query($arr) . '&page=';  //拼装新url
        } else {
            $url_now = $arr_url['path'] . '?page=';        //拼装新url
        }
        return $url_now;
    }

    //定义后缀名称
    private function getName($str_arr)
    {
        $a = array(
            'last' => '<',
            'next' => '>',
            'first' => '首',
            'end' => '末',
            'txt' => '条',
        );
        $a = !empty($str_arr) && is_array($str_arr) ? array_merge($a, $str_arr) : $a;
        return $a;
    }

    //limit分页查询方法
    public function limit()
    {
        return "LIMIT " . max(0, ($this->self_page - 1) * $this->page_ones) . "," . $this->page_ones;
    }


    //上一页
    private function last()
    {
        return $this->self_page > 1 ? "<a href='" . $this->url . ($this->self_page - 1) . "'>" . ($this->name['last']) . "</a> " : '';

    }


    //下一页
    private function next()
    {
        return ($this->self_page < $this->page_rows) ? "<a href='" . $this->url . ($this->self_page + 1) . "'>" . $this->name['next'] . "</a> " : '';

    }


    //首页
    private function first()
    {
        return ($this->self_page > 1) ? "<a href='" . $this->url . "1'>" . $this->name['first'] . "</a> " : '';

    }


    //尾页
    private function end()
    {
        return ($this->self_page < $this->page_rows) ? "<a href='" . $this->url . $this->page_rows . "'>" . $this->name['end'] . "</a> " : '';

    }

    //每页详情
    private function selfshow(){
        return "当前页：第".$this->l_id."—".$this->n_id.$this->name['txt'].'；　';
    }
    //总页数，总条数
    private function totalshow(){
        return "总计：".$this->page_rows."页，共".$this->total.$this->name['txt'].'；';
    }


    //将页码数据进行遍历并附上链接
    private function showlist(){
        $arr=$this->showArr();     //调用数组方法获得页码详细信息数组
        $show='';                //定义变量接收页码详细信息
        //遍历数组，获取页码
        foreach($arr as $v){
            $show .= empty($v['url']) ? "<strong>{$v['str']}</strong>" : "<a href='".$v['url']."'>{$v['str']}</a>";
        }
        return $show;
    }




    //将页码循环成数组
    private function showArr()
    {
        $arr=array();
        $list_l = max(1,min($this->self_page - ceil($this->pages / 2),$this->page_rows-$this->pages)); //页码第一个
        $list_n = min($this->pages + $list_l,$this->page_rows);  //页码最后一个
        for ($i = $list_l; $i <= $list_n; $i++) {
            if($this->self_page == $i){       //判断是否当前页
                $arr[$i]['url']='';           //当前页不给url
                $arr[$i]['str']=$i;
                continue;
            }
            $arr[$i]['url']=$this->url.$i;      //将url导入数组
            $arr[$i]['str']=$i;

        }
        return $arr;
    }




    //自定义调用显示分页类样式
    function page($p=1){
        switch($p){
            case $p==1:
                return $this->first().$this->last().$this->showlist().$this->next().$this->end().$this->selfshow().$this->totalshow();
                break;
            case $p==2 :
                return $this->first().$this->last().$this->showlist().$this->next().$this->end();
                break;
            //3为ajax分页显示方法调用
            case $p==3 :
                return $this->firstAjax().$this->lastAjax().$this->showlistAjax().$this->nextAjax().$this->endAjax();
                break;
        }
    }


/*******************************************AJAX分页方法**********************************************************8*/
    //ajax上一页
    private function lastAjax()
    {
        return $this->self_page > 1 ? "<a href='javascript:pageAjax(".($this->self_page - 1) . ");'>" . ($this->name['last']) . "</a> " : '';

    }

    //ajax下一页
    private function nextAjax()
    {
        return ($this->self_page < $this->page_rows) ? "<a href='javascript:pageAjax(".($this->self_page + 1) . ");'>" . $this->name['next'] . "</a> " : '';

    }



    //ajax首页
    private function firstAjax()
    {
        return ($this->self_page > 1) ? "<a href='javascript:pageAjax(1);'>" . $this->name['first'] . "</a> " : '';

    }


    //ajax尾页
    private function endAjax()
    {
        return ($this->self_page < $this->page_rows) ? "<a href='javascript:pageAjax(" . $this->page_rows . ");'>" . $this->name['end'] . "</a> " : '';

    }



    //ajax将页码循环成数组
    private function showArAjax()
    {
        $arr=array();
        $list_l = max(1,min($this->self_page - ceil($this->pages / 2),$this->page_rows-$this->pages)); //页码第一个
        $list_n = min($this->pages + $list_l,$this->page_rows);  //页码最后一个
        for ($i = $list_l; $i <= $list_n; $i++) {
            if($this->self_page == $i){       //判断是否当前页
                $arr[$i]['url']='';           //当前页不给url
                $arr[$i]['str']=$i;
                continue;
            }
            $arr[$i]['url']="javascript:pageAjax(".$i.");";      //将url导入数组
            $arr[$i]['str']=$i;

        }
        return $arr;
    }


    //ajax将页码数据进行遍历并附上链接
    private function showlistAjax(){
        $arr=$this->showArAjax();     //调用数组方法获得页码详细信息数组
        $show='';                //定义变量接收页码详细信息
        //遍历数组，获取页码
        foreach($arr as $v){
            $show .= empty($v['url']) ? "<strong>{$v['str']}</strong>" : "<a href='".$v['url']."'>{$v['str']}</a>";
        }
        return $show;
    }



}