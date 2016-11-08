<?php
/**
 * Created by PhpStorm.
 * User: 4d4k
 * Date: 2016/9/1
 * Time: 10:10
 */
namespace MingPHP\Core;
class Image
{
    private $img;
    private $img_info = array();

    function __construct($img)
    {
        if (!is_file($img)) {
            die('文件不存在');
        }
        $this->getMime($img);
    }

    private function getMime($img)
    {
        $info = getimagesize($img);
        if ($info === false || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            die('处理图片文件非法');
        }
        $this->img_info = array(
            'ext' => image_type_to_extension($info[2], false),
            'width' => $info[0],
            'height' => $info[1],
            'mime' => $info['mime'],
        );
        $fun = 'imagecreatefrom' . $this->img_info['ext'];
        $this->img = $fun($img);

    }

    //压缩图形
    /* @param  integer $filename 图片保存路径默认当前目录
     * @param  integer $quality 图片质量默认80
     */
    function save($filename = './', $quality = 100)
    {
        if (empty($this->img)) {
            die('没有可操作图像资源！');
        }

        $ext = $this->img_info['ext'];
        $fun     = 'image' . $ext;
        if ($ext == 'jpeg') {
            $fun($this->img, $filename, $quality);
        } else {
            $fun($this->img, $filename);
        }

    }

    /**
     * 裁剪图像
     * @param  integer $w 裁剪区域宽度
     * @param  integer $h 裁剪区域高度
     * @param  integer $x 裁剪区域x坐标
     * @param  integer $y 裁剪区域y坐标
     * @param  integer $width 图像保存宽度
     * @param  integer $height 图像保存高度
     */
    public function crop1($w, $h, $x = 0, $y = 0, $width = null, $height = null)
    {
        if (empty($this->img)) die('没有可以被裁剪的图像资源');

        //设置保存尺寸
        empty($width) && $width = $w;
        empty($height) && $height = $h;


        //创建新图像
        $img = imagecreatetruecolor($width, $height);
        // 调整默认颜色

        //针对图像，背景透明化处理方法
        $color = imagecolorallocate($img, 255, 255, 255);
        imagecolortransparent($img, $color);
        imagefill($img, 0, 0, $color);
        //裁剪
        imagecopyresampled($img, $this->img, 0, 0, $x, $y, $width, $height, $w, $h);
        imagedestroy($this->img); //销毁原图

        //设置新图像
        $this->img = $img;

    }

    /**
     * 生成缩略图
     * @param  integer $width 缩略图最大宽度
     * @param  integer $height 缩略图最大高度
     * @param  integer $type 缩略图裁剪类型
     */
    public function thumb($width, $height)
    {
        if (empty($this->img)) die('没有可以被缩略的图像资源');

        //原图宽度和高度
        $w = $this->img_info['width'];
        $h = $this->img_info['height'];

//原图小于目标尺寸，无法压缩
        if ($w < $width && $h < $height) return;

        //计算缩放比例
        $scale = min($width / $w, $height / $h);

        //设置缩略图的坐标及宽度和高度
        $x = $y = 0;
        $width = $w * $scale;
        $height = $h * $scale;


        /* 裁剪图像 */
        $this->crop1($w, $h, $x, $y, $width, $height);

    }

}
