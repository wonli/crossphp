<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\supervise;

use Cross\Exception\CoreException;
use Cross\Interactive\ResponseData;

/**
 * 密保卡
 *
 * @author wonli <wonli@live.com>
 * Class SecurityModule
 * @package modules\admin
 */
class SecurityModule extends AdminModule
{
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
        $code = [];

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
     * @param $bindUser
     * @return ResponseData
     * @throws CoreException
     */
    function bindCard($bindUser): ResponseData
    {
        $cardData = $this->makeSecurityCode();
        $isBind = $this->checkBind($bindUser);

        if ($isBind) {
            return $this->responseData(100500);
        } else {
            $data = array(
                'card_data' => $cardData,
                'bind_user' => $bindUser,
            );

            $cardId = $this->link->add($this->t_security_card, $data);
            if (false !== $cardId) {
                return $this->responseData(1);
            }

            return $this->responseData(100501);
        }
    }

    /**
     * 更新密保卡
     *
     * @param string $bindUser
     * @return ResponseData
     * @throws CoreException
     */
    function updateCard(string $bindUser): ResponseData
    {
        $cardData = self::makeSecurityCode();
        $isBind = $this->checkBind($bindUser);

        if ($isBind) {
            $status = $this->link->update($this->t_security_card, ['card_data' => $cardData], ['bind_user' => $bindUser]);
            if ($status !== false) {
                return $this->responseData(1);
            } else {
                return $this->responseData(100502);
            }
        } else {
            return $this->responseData(100503);
        }
    }

    /**
     * 取消绑定
     *
     * @param string $bindUser
     * @param bool $checkUsc
     * @return ResponseData
     * @throws CoreException
     */
    function unBind(string $bindUser, bool $checkUsc = true): ResponseData
    {
        $isBind = $this->checkBind($bindUser);
        if ($isBind) {
            if ($checkUsc) {
                $AU = new AdminUserModule();
                $ai = $AU->getAdminInfo(array('name' => $bindUser));
                if ($ai['usc'] != 1) {
                    return $this->responseData(100521);
                }
            }

            $delStatus = $this->link->del($this->t_security_card, array('bind_user' => $bindUser));
            if ($delStatus) {
                return $this->responseData(1);
            } else {
                return $this->responseData(100520);
            }
        } else {
            return $this->responseData(100503);
        }
    }

    /**
     * 检查是否绑定过密保卡
     *
     * @param string $bindUser
     * @return bool
     * @throws CoreException
     */
    public function checkBind(string $bindUser)
    {
        $id = $this->link->get($this->t_security_card, 'id', ['bind_user' => $bindUser]);
        if (!empty($id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回密保卡数据
     *
     * @param string $bindUser
     * @return ResponseData
     * @throws CoreException
     */
    function securityData(string $bindUser): ResponseData
    {
        $isBind = $this->checkBind($bindUser);
        if ($isBind) {
            $data = $this->getSecurityData($bindUser);
            if ($data[0] != -1) {
                return $this->responseData(1, $data[1]);
            } else {
                return $this->responseData(100510);
            }
        } else {
            return $this->responseData(100503);
        }
    }

    /**
     * 输出密保卡图片
     *
     * @param string $bindUser
     * @return void|ResponseData
     * @throws CoreException
     */
    function makeSecurityCardImage(string $bindUser)
    {
        $isBind = $this->checkBind($bindUser);
        if (!$isBind) {
            return $this->responseData(100503);
        }

        $data = $this->securityData($bindUser);
        if ($data->getStatus() != 1) {
            return $data;
        }

        $data = $data->getDataContent();
        $im = imagecreatetruecolor(520, 520);
        // 设置背景为白色
        imagefilledrectangle($im, 31, 31, 520, 520, 0xFFFFFF);

        $front = 5;
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

                imagestring($im, $front, $_margin - 10, $_y + $_space, $y, 0xFFBB00);
                imagestring($im, $front, $_x + $_space, $_margin - 10, $_i, 0xFFBB00);

                $codeLocation = 0;
                $_x = $_y += $_space;
                foreach ($c as $codeIndex => $code) {
                    if ($_i == $codeIndex) {
                        $charColor = 0x009933;
                    } else {
                        $charColor = 0x666666;
                    }

                    $codeLocation += $_space;
                    imagestring($im, $front, $codeLocation, $_y, $code, $charColor);
                }

                imageline($im, $_x + 30, 0, $_x + 30, 480, $color);
                imageline($im, 0, $_y + 30, 480, $_x + 30, $color);

            }

            imagestring($im, $front, 350, $_y + 46, "power by crossphp", 0xCCCCCC);

            imageline($im, 519, 519, 500, 520, $color2);
            imageline($im, 519, 519, 520, 500, $color2);
        }

        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename=' . ucfirst($bindUser) . '_SecurityCard.png');
        imagepng($im);
    }

    /**
     * 验证密保卡
     *
     * @param $user
     * @param $location
     * @param $inputCode
     * @return bool|int
     * @throws CoreException
     */
    function verifyCode($user, $location, $inputCode)
    {
        $data = $this->getSecurityData($user);

        if ($data[0] != -1) {
            $codeData = $data[1];
        } else {
            return -1;
        }

        $rightCode = $codeData[$location[0]][$location[1]] . $codeData[$location[2]][$location[3]];

        #判断是否相等
        if ($inputCode == $rightCode) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取得密保卡数据
     *
     * @param string
     * @return array|bool
     * @throws CoreException
     */
    function getSecurityData($bindUser)
    {
        $isBind = $this->checkBind($bindUser);

        if ($isBind) {
            $data = $this->link->get($this->t_security_card, '*', array('bind_user' => $bindUser));
            return [$data['ext_time'], json_decode($data['card_data'], true)];
        }

        return false;
    }

    /**
     * 创建代码
     *
     * @return mixed
     * @throws CoreException
     */
    function createTable()
    {
        $table = $this->getPrefix($this->t_security_card);
        $createSql = "CREATE TABLE `{$table}` (
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

        return $this->link->execute($createSql);
    }

    /**
     * 生成密保卡数据
     *
     * @param bool $isSerialize
     * @return array|string
     */
    private function makeSecurityCode($isSerialize = true)
    {
        $security = [];
        $str = '3456789ABCDEFGHJKMNPQRSTUVWXY';

        for ($k = 65; $k < 74; $k++) {
            for ($i = 1; $i <= 9; $i++) {
                $_x = substr(str_shuffle($str), $i, $i + 2);
                $security[chr($k)][$i] = $_x[0] . $_x[1];
            }
        }
        if ($isSerialize) {
            return json_encode($security);
        }
        return $security;
    }
}
