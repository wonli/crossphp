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
    protected $tAdmin = 'cpa_admin';

    /**
     * 角色表名
     *
     * @var string
     */
    protected $tRole = 'cpa_acl_role';

    /**
     * 操作日志
     *
     * @var string
     */
    protected $tActLog = 'cpa_act_log';

    /**
     * 权限表
     *
     * @var string
     */
    protected $tAclMenu = 'cpa_acl_menu';

    /**
     * API文档
     *
     * @var string
     */
    protected $tApiDoc = 'cpa_doc';

    /**
     * API文档用户数据
     *
     * @var string
     */
    protected $tApiDocUser = 'cpa_doc_user';

    /**
     * 接口数据
     *
     * @var string
     */
    protected $tApiDocData = 'cpa_doc_data';

    /**
     * 存储密保卡的表名
     *
     * @var string
     */
    protected $tSecurityCard = 'cpa_security_card';
}

