<?php
/**
 * Cross - lightness PHP framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\Images;

use Cross\Exception\CoreException;

/**
 * @author wonli <wonli@live.com>
 * Class ImageThumb
 */
class ImageThumb
{
    /**
     * 剪切后图片宽度
     *
     * @var int
     */
    protected $width;

    /**
     * 剪切后图片高度
     *
     * @var int
     */
    protected $height;

    /**
     * 文件路径
     *
     * @var string
     */
    protected $saveDir;

    /**
     * 原文件路径
     *
     * @var string
     */
    protected $srcImages;

    /**
     * 缩略图文件名
     *
     * @var string
     */
    protected $thumbImageName;

    /**
     * ImageThumb constructor.
     * 
     * @param $srcImages
     */
    function __construct($srcImages)
    {
        $this->srcImages = $srcImages;
    }

    /**
     * 设置文件路径和文件名
     *
     * @param $dir
     * @param $thumbImageName
     * @return $this
     */
    function setFile($dir, $thumbImageName)
    {
        $this->saveDir = $dir;
        $this->thumbImageName = $thumbImageName;

        return $this;
    }

    /**
     * 设置高宽
     *
     * @param int $width
     * @param int $height
     * @return $this
     */
    function setSize($width = 0, $height = 0)
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
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
                'mime' => $imageInfo['mime']
            ];
        } else {
            return false;
        }
    }

    /**
     * 生成缩略图
     *
     * @param bool $interlace
     * @param bool $returnFullPath
     * @param int $quality
     * @return bool|string
     * @throws CoreException
     */
    function thumb($interlace = true, $returnFullPath = false, $quality = 100)
    {
        if (!$this->saveDir || !$this->srcImages) {
            throw new CoreException('请设置文件路径和文件名');
        }

        // 获取原图信息
        $info = $this->getImageInfo($this->srcImages);
        if (!$info) {
            return false;
        }

        $srcWidth = &$info['width'];
        $srcHeight = &$info['height'];
        $type = strtolower($info['file_type']);
        $fileExt = strtolower($info['ext']);
        $thumbFileName = $this->thumbImageName . $fileExt;
        unset($info);

        $x = 0;
        $height = $this->height;
        $width = floor($srcWidth * ($this->height / $srcHeight));
        if ($width > $this->width) {
            $x = floor(($this->width - $width) / 2);
        }

        //载入原图
        $srcImages = $this->createImage($this->srcImages, $type);

        //创建缩略图
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $thumbImages = imagecreatetruecolor($this->width, $this->height);
        } else {
            $thumbImages = imagecreate($this->width, $this->height);
        }

        if ('gif' == $type) {
            $backgroundColor = imagecolorallocate($thumbImages, 0, 0, 0);
            imagecolortransparent($thumbImages, $backgroundColor);
        } elseif ('png' == $type) {
            imagealphablending($thumbImages, false);
            imagesavealpha($thumbImages, true);
        } else {
            imageinterlace($thumbImages, (int)$interlace);
        }

        //复制图片
        if (function_exists('imagecopyresampled')) {
            imagecopyresampled($thumbImages, $srcImages, $x, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
        } else {
            imagecopyresized($thumbImages, $srcImages, $x, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
        }

        //返回缩略图的路径，字符串
        $savePath = $this->saveDir . $thumbFileName;
        $this->saveImage($thumbImages, $savePath, $type, $quality);
        imagedestroy($thumbImages);
        imagedestroy($srcImages);

        if ($returnFullPath) {
            return $savePath;
        }

        return $thumbFileName;
    }

    /**
     * 创建图片
     *
     * @param $image
     * @param $imageType
     * @return resource
     */
    protected function createImage($image, $imageType)
    {
        switch ($imageType) {
            case 'jpg':
            case 'jpeg':
            case 'pjpeg':
                $res = imagecreatefromjpeg($image);
                break;

            case 'gif':
                $res = imagecreatefromgif($image);
                break;

            case 'png':
                $res = imagecreatefrompng($image);
                break;

            case 'bmp':
                $res = imagecreatefromwbmp($image);
                break;

            default:
                $res = imagecreatefromgd2($image);
                break;
        }

        return $res;
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

}


