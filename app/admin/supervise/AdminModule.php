<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\supervise;

use Cross\MVC\Module;

/**
 * cpa基类
 *
 * @author wonli <wonli@live.com>
 * Class AdminModule
 * @package modules\admin
 */
class AdminModule extends Module
{
    /**
     * 管理员表
     *
     * @var string
     */
    protected $t_admin = 'cp_admin';

    /**
     * 角色表名
     *
     * @var string
     */
    protected $t_role = 'cp_acl_role';

    /**
     * 权限表
     *
     * @var string
     */
    protected $t_acl_menu = 'cp_acl_menu';

    /**
     * 存储密保卡的表名
     *
     * @var string
     */
    protected $t_security_card = 'cp_security_card';
}

