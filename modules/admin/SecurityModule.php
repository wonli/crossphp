<?php
/**
 * @Author: wonli <wonli@live.com>
 */
namespace modules\admin;

use Cross\MVC\Module;

/**
 * 密保卡
 *
 * @Auth: wonli <wonli@live.com>
 * Class SecurityModule
 * @package modules\admin
 */
class SecurityModule extends Module
{
    /**
     * 存储密保卡的表名
     *
     * @var string
     */
    protected $t_sec = 'cp_security_card';

    /**
     * 随机生成密保卡坐标
     *
     * @param
     * @return string
     */
    function shuffleLocation()
    {
        $str_x = '123456789';
        $str_y = 'ABCEDEGHI';
        $code = array();

        for ($i = 0; $i < 9; $i++) {
            for ($k = 0; $k < 9; $k++) {
                $code[] = $str_y[$i] . $str_x[$k];
            }
        }

        shuffle($code);
        $code = array_slice($code, 0, 2);
        return $code[0] . $code[1];
    }

    /**
     * 绑定密保卡
     *
     * @param $bind_user
     * @return int
     */
    function bindCard($bind_user)
    {
        $card_data = $this->makeSecurityCode();
        $is_bind = $this->checkBind($bind_user);

        if ($is_bind) {
            return $this->result(100010);
        } else {
            $data = array(
                'card_data' => $card_data,
                'bind_user' => $bind_user,
            );

            $card_id = $this->link->add($this->t_sec, $data);
            if ($card_id) {
                return $this->result(100011);
            }

            return $this->result(100012);
        }
    }

    /**
     * 更新密保卡
     *
     * @param $bind_user
     * @return int
     */
    function updateCard($bind_user)
    {
        $card_data = self::makeSecurityCode();
        $is_bind = self::checkBind($bind_user);

        if ($is_bind) {
            $data = array(
                'card_data' => $card_data,
            );

            $up_status = $this->link->update($this->t_sec, $data, array('bind_user' => $bind_user));

            if ($up_status) {
                return $this->result(1);
            } else {
                return $this->result(100015);
            }
        } else {
            return $this->result(100013);
        }
    }

    /**
     * 取消绑定
     *
     * @param string $bind_user
     * @return bool;
     */
    function killBind($bind_user)
    {
        $is_bind = self::checkBind($bind_user);
        if ($is_bind) {
            $del_status = $this->link->del($this->t_sec, array('bind_user' => $bind_user));
            if ($del_status) {
                return $this->result(100016);
            } else {
                return $this->result(100017);
            }
        } else {
            return $this->result(100013);
        }
    }

    /**
     * 检查是否绑定过密保卡
     *
     * @param string $bind_user
     * @return bool
     */
    public function checkBind($bind_user)
    {
        $id = $this->link->get($this->t_sec, 'id', array('bind_user' => $bind_user));

        if (!empty($id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回密保卡数据
     *
     * @param $bind_user
     * @return int
     */
    function securityData($bind_user)
    {
        $is_bind = $this->checkBind($bind_user);
        if ($is_bind) {
            $data = $this->getSecurityData($bind_user);
            if ($data[0] != -1) {
                return $this->result(1, $data[1]);
            } else {
                return $this->result(100015);
            }
        } else {
            return $this->result(100013);
        }
    }

    /**
     * 输出密保卡图片
     *
     * @param $bind_user
     * @return array|string|bool
     */
    function makeSecurityCardImage($bind_user)
    {
        $is_bind = $this->checkBind($bind_user);
        if (!$is_bind) {
            return $this->result(100013);
        }

        $data = $this->securityData($bind_user);
        if ($data['status'] != 1) {
            return $this->result($data['status']);
        } else {
            $data = $data['message'];
            $im = imagecreatetruecolor(520, 520);
            // 设置背景为白色
            imagefilledrectangle($im, 31, 31, 520, 520, 0xFFFFFF);

            $_front = 5;
            $_space = 50;
            $_margin = 20;

            $_y = $_x = $_i = 0;
            if (is_array($data)) {
                $color = imagecolorallocate($im, 45, 45, 45);
                $color2 = imagecolorallocate($im, 205, 205, 205);

                imageline($im, $_x + 30, 0, $_x + 30, 480, $color);
                imageline($im, 0, 0, 0, 480, $color);


                imageline($im, 0, $_y + 30, 480, $_x + 30, $color);
                imageline($im, 0, 0, 480, 0, $color);

                foreach ($data as $y => $c) {
                    ++$_i;

                    imagestring($im, $_front, $_margin - 10, $_y + $_space, $y, 0xFFBB00);
                    imagestring($im, $_front, $_x + $_space, $_margin - 10, $_i, 0xFFBB00);

                    $_x = $_y += $_space;

                    $_code_location = 0;
                    foreach ($c as $code) {
                        $_code_location += $_space;
                        imagestring($im, $_front, $_code_location, $_y, $code, 0x336699);
                    }

                    imageline($im, $_x + 30, 0, $_x + 30, 480, $color);
                    imageline($im, 0, $_y + 30, 480, $_x + 30, $color);

                }

                imagestring($im, $_front, 350, $_y + 46, "power by crossphp", 0xCCCCCC);

                imageline($im, 519, 519, 500, 520, $color2);
                imageline($im, 519, 519, 520, 500, $color2);
            }

            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename=' . ucfirst($bind_user) . '_SecurityCard.png');
            imagepng($im);
            return true;
        }
    }

    /**
     * 验证密保卡
     *
     * @param $user
     * @param $location
     * @param $input_code
     * @return bool|int
     */
    function verifyCode($user, $location, $input_code)
    {
        $data = $this->getSecurityData($user);

        if ($data[0] != -1) {
            $code_data = $data[1];
        } else {
            return -1;
        }

        $right_code = $code_data[$location[0]][$location[1]] . $code_data[$location[2]][$location[3]];

        #判断是否相等
        if ($input_code == $right_code) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 创建代码
     *
     * @return mixed
     */
    function createTable()
    {
        $table = $this->getPrefix($this->t_sec);
        $create_sql = "CREATE TABLE `{$table}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `card_data` TEXT NOT NULL COLLATE 'utf8_unicode_ci',
            `bind_user` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
            `ext_time` INT(11) NOT NULL COMMENT '已过期,-1',
            PRIMARY KEY (`id`),
            INDEX `bind_user` (`bind_user`)
        )
        COLLATE='utf8_unicode_ci'
        ENGINE=InnoDB
        AUTO_INCREMENT=1";

        return $this->link->execute($create_sql);
    }

    /**
     * 生成密保卡数据
     *
     * @param bool $is_serialize
     * @internal param $
     * @return array
     */
    private function makeSecurityCode($is_serialize = true)
    {
        $security = array();
        $str = '3456789abcdefghjkmnpqrstuvwxy';

        for ($k = 65; $k < 74; $k++) {
            for ($i = 1; $i <= 9; $i++) {
                $_x = substr(str_shuffle($str), $i, $i + 2);
                $security[chr($k)][$i] = $_x[0] . $_x[1];
            }
        }
        if ($is_serialize === true) {
            return serialize($security);
        }
        return $security;
    }

    /**
     * 取得密保卡数据
     *
     * @param string
     * @return array
     */
    private function getSecurityData($bind_user)
    {
        $is_bind = $this->checkBind($bind_user);

        if ($is_bind) {
            $data = $this->link->get($this->t_sec, '*', array('bind_user' => $bind_user));
            return array($data['ext_time'], unserialize($data['card_data']));
        }

        return false;
    }
}
