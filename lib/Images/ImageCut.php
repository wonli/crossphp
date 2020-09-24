<?php

namespace lib\Images;

use Exception;

class ImageCut
{
    /**
     * 临时创建的图象
     *
     * @var resource
     */
    private $im;

    /**
     * 图片类型
     *
     * @var string
     */
    private $type;

    /**
     * 实际宽度
     *
     * @var int
     */
    private $width;

    /**
     * 实际高度
     *
     * @var int
     */
    private $height;

    /**
     * 原始图片
     *
     * @var string
     */
    protected $srcImages;

    /**
     * 图片保存路径
     *
     * @var string
     */
    protected $savePath;

    /**
     * 图片保存名
     *
     * @var string
     */
    protected $saveName;

    /**
     * 原始图片信息
     *
     * @var array|bool
     */
    protected $imagesInfo;

    /**
     * @var int
     */
    private $resizeWidth;

    /**
     * @var int
     */
    private $resizeHeight;

    /**
     * ImageCut constructor.
     *
     * @param $srcImages
     */
    function __construct($srcImages)
    {
        $this->srcImages = $srcImages;
        $this->imagesInfo = $this->getImageInfo($srcImages);
        $this->type = $this->imagesInfo['file_type'];

        //初始化图象
        $this->createImageResource();

        //目标图象地址
        $this->width = $this->imagesInfo['width'];
        $this->height = $this->imagesInfo['height'];
    }

    /**
     * 设置保存路径
     *
     * @param $path
     * @param $name
     * @return $this
     */
    function setSaveInfo($path, $name)
    {
        $this->savePath = $path;
        $this->saveName = $name;

        return $this;
    }

    /**
     * 设置剪切大小
     *
     * @param $width
     * @param $height
     * @return $this
     */
    function setCutSize($width, $height)
    {
        $this->resizeWidth = $width;
        $this->resizeHeight = $height;

        return $this;
    }

    /**
     * 剪切图象
     *
     * @param $coordinate
     * @param bool $returnPath
     * @return string
     * @throws Exception
     */
    function cut($coordinate, $returnPath = false)
    {
        if (!isset($coordinate['x']) || !isset($coordinate['y']) ||
            !isset($coordinate['w']) || !isset($coordinate['h'])
        ) {
            throw new Exception('请设置剪切坐标x, y, w, h');
        }

        $savePath = $this->getSavePath();

        //改变后的图象的比例
        if (!empty($this->resizeHeight)) {
            $resizeRatio = ($this->width) / ($this->height);
        } else {
            $resizeRatio = 0;
        }

        //实际图象的比例
        $ratio = ($this->width) / ($this->height);

        if ($ratio >= $resizeRatio) //高度优先
        {
            $thumbImagesWidth = $this->height * $resizeRatio;
            $thumbImagesHeight = $this->height;
        } else {
            $thumbImagesWidth = $this->width;
            $thumbImagesHeight = $this->width / $resizeRatio;
        }

        //创建缩略图
        if ($this->imagesInfo['file_type'] != 'gif' && function_exists('imagecreatetruecolor')) {
            $thumbImages = imagecreatetruecolor($this->width, $this->height);
        } else {
            $thumbImages = imagecreate($this->width, $this->height);
        }

        imagecopyresampled(
            $thumbImages,
            $this->im,
            0,
            0,
            $coordinate['x'],
            $coordinate['y'],
            $thumbImagesWidth,
            $thumbImagesHeight,
            $coordinate['w'],
            $coordinate['h']
        );

        $this->saveImage($thumbImages, $savePath, $this->imagesInfo['file_type'], 100);
        if (true === $returnPath) {
            return $savePath;
        }

        return $this->saveName;
    }

    /**
     * 获取图片详细信息
     *
     * @param $images
     * @return array|bool
     */
    protected function getImageInfo($images)
    {
        $imageInfo = getimagesize($images);
        if (false !== $imageInfo) {
            $imageExt = strtolower(image_type_to_extension($imageInfo[2]));
            $imageType = substr($imageExt, 1);
            $imageSize = filesize($images);

            return [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
                'ext' => $imageExt,
                'file_type' => $imageType,
                'size' => $imageSize,
                'mime' => $imageInfo['mime'],
            ];
        } else {
            return false;
        }
    }

    /**
     * 创建临时图象
     */
    private function createImageResource()
    {
        switch ($this->type) {
            case 'jpg':
            case 'jpeg':
            case 'pjpeg':
                $this->im = imagecreatefromjpeg($this->srcImages);
                break;

            case 'gif':
                $this->im = imagecreatefromgif($this->srcImages);
                break;

            case 'png':
                $this->im = imagecreatefrompng($this->srcImages);
                break;

            case 'bmp':
                $this->im = imagecreatefromwbmp($this->srcImages);
                break;

            default:
                $this->im = imagecreatefromgd2($this->srcImages);
                break;
        }
    }

    /**
     * 存储图片
     *
     * @param $resource
     * @param $savePath
     * @param $imageType
     * @param int $quality
     * @return bool
     */
    protected function saveImage($resource, $savePath, $imageType, $quality = 100)
    {
        switch ($imageType) {
            case 'jpg':
            case 'jpeg':
            case 'pjpeg':
                $ret = imagejpeg($resource, $savePath, $quality);
                break;

            case 'gif':
                $ret = imagegif($resource, $savePath);
                break;

            case 'png':
                $ret = imagepng($resource, $savePath);
                break;

            default:
                $ret = imagegd2($resource, $savePath);
                break;
        }

        return $ret;
    }

    /**
     * 图象目标地址
     *
     * @return string
     * @throws Exception
     */
    protected function getSavePath()
    {
        $name = $this->getSaveName();
        if (!$name) {
            throw new Exception('请设置缩略图名称');
        }

        $path = $this->getSaveDir();
        if (!$path || !is_dir($path)) {
            throw new Exception('请设置路径');
        }

        return $path . $name . $this->imagesInfo['ext'];
    }

    /**
     * 获取文件名
     *
     * @return string
     */
    private function getSaveName()
    {
        return $this->saveName;
    }

    /**
     * 获取文件保存文件夹
     *
     * @return string
     */
    private function getSaveDir()
    {
        return $this->savePath;
    }
}
