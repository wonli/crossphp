<?php
/**
 * 生成图片验证码
 *
 * Class Captcha
 */
namespace lib\Images;

use Cross\Exception\CoreException;
use Cross\Core\Helper;

class Captcha
{
    /**
     * 宽
     *
     * @var int
     */
    private $width;

    /**
     * 高
     *
     * @var int
     */
    private $height;

    /**
     * 图片资源
     *
     * @var resource
     */
    private $img;

    /**
     * 文字
     *
     * @var string
     */
    private $text;

    /**
     * 验证码字体
     *
     * @var array
     */
    private $fontFamily = [];

    /**
     * 设置背景色
     *
     * @var array
     */
    private $backgroundColor = [];

    /**
     * 画多少个干扰点
     *
     * @var bool
     */
    private $withPix = 10;

    /**
     * 最大画多少根干扰线
     *
     * @var bool
     */
    private $withArc = 5;

    /**
     * 字体大小
     *
     * @var int
     */
    private $fontSize;

    /**
     * 文字个数
     *
     * @var int
     */
    private $num;

    /**
     * 设置验证码图片高宽
     *
     * @param int $width
     * @param int $height
     */
    public function __construct($width = 120, $height = 40)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * 设置文字
     *
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * 设置字体
     *
     * @param string|array $fontFamily
     * @param int $fontSize
     * @return $this
     */
    public function setTypeface($fontFamily, $fontSize = 20)
    {
        $this->fontFamily[] = $fontFamily;
        $this->fontSize = $fontSize;
        return $this;
    }

    /**
     * 设置字体文件夹
     *
     * @param string $dir
     * @param int $fontSize
     * @return $this
     */
    public function setTypefaceDir($dir, $fontSize = 20)
    {
        $this->fontSize = $fontSize;
        if (is_dir($dir)) {
            $this->fontFamily = glob("{$dir}*.ttf");
        }

        return $this;
    }

    /**
     * 设置背景色
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return $this
     */
    public function setBackGround($r, $g, $b)
    {
        $this->backgroundColor = array('r' => $r, 'g' => $g, 'b' => $b);
        return $this;
    }

    /**
     * 是否画干扰点
     *
     * @param int $num
     * @return $this
     */
    public function withPix($num)
    {
        $this->withPix = (int)$num;
        return $this;
    }

    /**
     * 是否画干扰线
     *
     * @param $num
     * @return $this
     */
    public function withArc($num)
    {
        $this->withArc = $num;
        return $this;
    }

    /**
     * @see output()
     *
     * @throws CoreException
     */
    public function getImage()
    {
        $this->initCreateImages();
        $this->output();
    }

    /**
     * @see base64encode()
     *
     * @return string
     * @throws CoreException
     */
    function getImageBase64encode()
    {
        $this->initCreateImages();
        return $this->base64encode();
    }

    /**
     * 初始化图片数据
     *
     * @throws CoreException
     */
    protected function initCreateImages()
    {
        $this->createImg();
        $this->filledColor();
        $this->pix();
        $this->arc();
        $this->write();
    }

    /**
     * 获取文字
     *
     * @return array
     * @throws CoreException
     */
    protected function getText()
    {
        if ($this->text) {
            $texts = [];
            $this->num = Helper::strLen($this->text);
            for ($i = 0; $i < $this->num; $i++) {
                $texts[] = mb_substr($this->text, $i, 1, 'UTF-8');
            }

            return $texts;
        } else {
            throw new CoreException('please set captcha text!');
        }
    }

    /**
     * 创建临时图片
     */
    protected function createImg()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);
    }

    /**
     * 产生背景颜色, 颜色值越大越浅, 越小越深
     *
     * @return int
     */
    protected function bgColor()
    {
        if ($this->backgroundColor) {
            return imagecolorallocate($this->img, $this->backgroundColor['r'], $this->backgroundColor['g'], $this->backgroundColor['b']);
        }

        return imagecolorallocate($this->img, mt_rand(235, 255), mt_rand(245, 255), 255);
    }

    /**
     * 字的颜色
     *
     * @return int
     */
    protected function fontColor()
    {
        return imagecolorallocate($this->img, mt_rand(0, 90), mt_rand(0, 100), mt_rand(0, 120));
    }

    /**
     * 填充背景颜色
     */
    protected function filledColor()
    {
        imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $this->bgColor());
    }

    /**
     * 画上干扰点
     */
    protected function pix()
    {
        if ($this->withPix > 0) {
            for ($i = 0; $i < $this->withPix; $i++) {
                imagesetpixel($this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), $this->fontColor());
            }
        }
    }

    /**
     * 画上干扰线
     */
    protected function arc()
    {
        if ($this->withArc > 0) {
            for ($i = 0; $i < mt_rand(1, $this->withArc); $i++) {
                imagesetthickness($this->img, mt_rand(1, 3));
                imagearc(
                    $this->img,
                    mt_rand(0, $this->width),
                    mt_rand(0, $this->height),
                    mt_rand($this->width, ceil($this->width * 1.5)),
                    mt_rand($this->height, ceil($this->height / 0.5)),
                    mt_rand(0, 90),
                    mt_rand(180, 360),
                    $this->fontColor()
                );
            }
        }
    }

    /**
     * 写字
     *
     * @throws CoreException
     */
    protected function write()
    {
        $texts = $this->getText();
        $fontFamilyCount = count($this->fontFamily);
        $preWidth = ceil(($this->width - 10) / $this->num);

        for ($i = 0; $i < $this->num; $i++) {
            $fontSize = mt_rand($this->fontSize - 5, $this->fontSize + 5);
            if ($fontFamilyCount) {
                if ($fontFamilyCount > 1) {
                    shuffle($this->fontFamily);
                }

                $font = current($this->fontFamily);
                $box = imagettfbbox($fontSize, 0, $font, $texts[$i]);
                $textWidth = $box[2] - $box[0];
                $textHeight = $box[1] - $box[7];

                $x = $preWidth * $i + ($preWidth / 2 - $textWidth / 2);
                $y = ($this->height - $textHeight) / 2 + $textHeight;

                $angle = mt_rand(-15, 15);
                imagettftext(
                    $this->img,
                    $fontSize,
                    $angle,
                    $x,
                    $y,
                    $this->fontColor(),
                    $font,
                    $texts[$i]
                );
            } else {
                $x = max(5, floor($this->width / $this->num) * $i);
                $y = mt_rand(5, floor($this->height * 0.65));
                imagechar($this->img, 5, $x, $y, $texts[$i], $this->fontColor());
            }
        }
    }

    /**
     * 输出图片
     */
    protected function output()
    {
        header("Content-type:image/jpeg");
        imagejpeg($this->img);
        exit(0);
    }

    /**
     * 生成图片的base64编码数据
     *
     * @return string
     */
    protected function base64encode()
    {
        ob_start();
        imagejpeg($this->img);
        $content = ob_get_clean();
        return chunk_split(sprintf("data:image/jpeg;base64,%s", base64_encode($content)));
    }

    /**
     * 销毁内存中的临时图片
     */
    function __destruct()
    {
        if (!empty($this->img)) {
            imagedestroy($this->img);
        }
    }
}
