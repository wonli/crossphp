<?php echo '<?php' . PHP_EOL . PHP_EOL ?>
<?php if(!empty($data['namespace'])) : ?>
namespace <?php echo $data['namespace'] ?>;
<?php endif ?>

use Cross\Exception\CoreException;
use Cross\MVC\Module;
use PDO;

<?php echo $data['propertyType'] ?> <?php echo $data['className'] . PHP_EOL ?>
{
    <?php $this->makePropertyFields($data['mate_data']); ?>

    /**
     * 表名
     *
     * @var string
     */
    private $table;

    /**
     * 自定义索引
     *
     * @var array
     */
    private $index;

    /**
     * 数据库模型配置名称
     *
     * @var mixed
     */
    private $modeName;

    /**
     * 模型信息
     *
     * @var array
     */
    private $modelInfo = array(
        'mode' => '<?php echo $data['link_type'] ?>:<?php echo $data['link_name'] ?>',
        'table' => '<?php echo $data['table'] ?>',
        'link_type' => '<?php echo $data['link_type'] ?>',
        'link_name' => '<?php echo $data['link_name'] ?>',
        'primary_key' => '<?php echo $data['primary_key'] ?>'
    );

    /**
     * 字段属性
     *
     * @var array
     */
    private static $propertyInfo = array(
        <?php $this->makePropertyInfo($data['mate_data']) ?>
    );

    /**
     * User constructor.
     *
     * @param string $modeName
     */
    function __construct($modeName = '')
    {
        if (empty($mode)) {
            $this->modeName = $this->modelInfo['mode'];
        }
    }

    /**
     * 获取单条数据
     *
     * @param array $where
     * @param string $fields
     * @return mixed
     * @throws CoreException
     */
    function get($where = array(), $fields = '*')
    {
        if (empty($where)) {
            $where = $this->getDefaultCondition();
        }

        return $this->db()->get($this->getTable(), $fields, $where);
    }

    /**
     * 添加
     *
     * @throws CoreException
     */
    function add()
    {
        return $this->db()->add($this->getTable(), $this->makeInsertData());
    }

    /**
     * 更新
     *
     * @param array $condition
     * @param array $data
     * @return bool
     * @throws CoreException
     */
    function update($condition = array(), $data = array())
    {
        if (empty($data)) {
            $data = $this->getModifiedData();
        }

        if (empty($condition)) {
            $condition = $this->getDefaultCondition();
        }

        return $this->db()->update($this->getTable(), $data, $condition);
    }

    /**
     * 删除
     *
     * @param array $condition
     * @return bool
     * @throws CoreException
     */
    function del($condition = array())
    {
        if (empty($condition)) {
            $condition = $this->getDefaultCondition();
        }

        return $this->db()->del($this->getTable(), $condition);
    }

    /**
     * 获取数据
     *
     * @param array $where
     * @param string $fields
     * @param string $order
     * @param string $group_by
     * @param int $limit
     * @return mixed
     * @throws CoreException
     */
    function getAll($where = array(), $fields = '*', $order = '1', $group_by = '1', $limit = 0)
    {
        return $this->db()->getAll($this->getTable(), $fields, $where, $order, $group_by, $limit);
    }

    /**
     * 按分页获取数据
     *
     * @param array $page
     * @param array $where
     * @param string $fields
     * @param string $order
     * @param string $group_by
     * @return mixed
     * @throws CoreException
     */
    function find(&$page = array('p' => 1, 'limit' => 50), $where = array(), $fields = '*', $order = '1', $group_by = '1')
    {
        return $this->db()->find($this->getTable(), $fields, $where, $order, $page, $group_by);
    }

    /**
     * 查询数据, 并更新本类属性
     *
     * @param array $where
     * @return mixed
     * @throws CoreException
     */
    function property($where = array())
    {
        if (empty($where)) {
            $where = $this->getDefaultCondition();
        }

        $stmt = $this->db()->select('*')->from($this->getTable())->where($where)->stmt();
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
    }

    /**
     * 获取数据库链接
     *
     * @return \Cross\Cache\Driver\RedisDriver|\Cross\DB\Drivers\CouchDriver|\Cross\DB\Drivers\MongoDriver|\Cross\DB\Drivers\PDOSqlDriver
     * @throws CoreException
     */
    function db()
    {
        return $this->getModuleInstance()->link;
    }

    /**
     * 自定义表名(包含前缀的完整名称)
     *
     * @param string $table
     */
    function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * 指定索引
     *
     * @param string $indexName
     * @param $indexValue
     * @throws CoreException
     */
    function useIndex($indexName, $indexValue = '')
    {
        if (!property_exists($this, $indexName)) {
            throw new CoreException('不支持的索引名称');
        }

        $this->{$indexName} = $indexValue;
        $this->index[$indexName] = $indexName;
    }

    /**
     * 获取表名
     *
     * @return array|mixed
     * @throws CoreException
     */
    function getTable()
    {
        if (!$this->table) {
            $this->table = $this->getModuleInstance()->getPrefix($this->modelInfo['table']);
        }

        return $this->table;
    }

    /**
     * 获取模型信息
     *
     * @param string $key
     * @return mixed
     */
    function getModelInfo($key = null)
    {
        if (null === $key) {
            return $this->modelInfo;
        } elseif (isset($this->modelInfo[$key])) {
            return $this->modelInfo[$key];
        } else {
            return false;
        }
    }

    /**
     * 获取字段属性
     *
     * @param string $property
     * @return bool|mixed
     */
    function getPropertyInfo($property = null)
    {
        if (null === $property) {
            return self::$propertyInfo;
        } elseif (isset(self::$propertyInfo[$property])) {
            return self::$propertyInfo[$property];
        } else {
            return false;
        }
    }

    /**
     * 更新属性值
     *
     * @param array $data
     */
    function updateProperty(array $data)
    {
        if (!empty($data)) {
            foreach ($data as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        }
    }

    /**
     * 获取默认值
     *
     * @return array
     */
    function getDefaultData()
    {
        $data = array();
        foreach (self::$propertyInfo as $p => $c) {
            if (0 === strcasecmp($c['default_value'], 'CURRENT_TIMESTAMP')) {
                continue;
            }

            $data[$p] = $c['default_value'];
        }

        return $data;
    }

    /**
     * 获取属性数组
     *
     * @return array
     */
    function getArrayData()
    {
        $data = array();
        foreach (self::$propertyInfo as $p => $c) {
            if (0 === strcasecmp($c['default_value'], 'CURRENT_TIMESTAMP')) {
                continue;
            }

            $data[$p] = $this->{$p};
        }

        return $data;
    }

    /**
     * 获取修改过的数据
     *
     * @return array
     */
    protected function getModifiedData()
    {
        $data = array();
        foreach (self::$propertyInfo as $p => $c) {
            if (0 === strcasecmp($c['default_value'], 'CURRENT_TIMESTAMP')) {
                continue;
            }

            $value = $this->{$p};
            if (null !== $value) {
                $data["`{$p}`"] = $value;
            }
        }

        return $data;
    }

    /**
     * 获取待插入数据
     *
     * @return array
     */
    private function makeInsertData()
    {
        $data = array();
        foreach (self::$propertyInfo as $p => $c) {
            $value = $this->{$p};
            if ($c['auto_increment'] || (0 === strcasecmp($c['default_value'], 'CURRENT_TIMESTAMP'))) {
                continue;
            }

            if (null === $value) {
                $value = $c['default_value'];
            }

            $data["`{$p}`"] = $value;
        }

        return $data;
    }

    /**
     * 获取默认条件
     *
     * @return mixed
     * @throws CoreException
     */
    private function getDefaultCondition()
    {
        if (empty($this->index)) {
            $indexName = &$this->modelInfo['primary_key'];
            $this->index[$indexName] = $indexName;
        }

        if (empty($this->index)) {
            throw new CoreException("请为表 {$this->modelInfo['table']} 指定索引");
        }

        $index = [];
        foreach ($this->index as $indexName) {
            $indexValue = $this->{$indexName};
            if (null === $indexValue || '' === $indexValue) {
                throw new CoreException("{$indexName} 的值不能为空");
            }

            $index[$indexName] = $indexValue;
        }

        return $index;
    }

    /**
     * 链接数据库
     *
     * @return Module
     * @throws CoreException
     */
    private function getModuleInstance()
    {
        static $model = null;
        if (null === $model) {
            $model = new Module($this->modeName);
        }

        return $model;
    }
}