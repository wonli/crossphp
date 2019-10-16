<?php echo '<?php' . PHP_EOL . PHP_EOL ?>
<?php if(!empty($data['namespace'])) : ?>
namespace <?php echo $data['namespace'] ?>;
<?php endif ?>

use Cross\DB\Drivers\PDOSqlDriver;
use Cross\Exception\CoreException;
use Cross\MVC\Module;
use PDO;

<?php echo $data['type'] ?> <?php echo $data['name'] . PHP_EOL ?>
{
    <?php $this->makeModelFields($data['mate_data']); ?>

    /**
     * 表名
     *
     * @var string
     */
    private $table;

    /**
     * 连表数组
     *
     * @var array
     */
    private $joinTables = array();

    /**
     * 自定义索引
     *
     * @var array
     */
    private $index = array();

    /**
     * 在事务中获取单条数据时是否加锁
     *
     * @var bool
     */
    private $useLock = false;

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
        'table' => '<?php echo $data['database_table_name'] ?>',
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
        <?php $this->makeModelInfo($data['mate_data']) ?>
    );

    /**
     * User constructor.
     *
     * @param string $modeName
     */
    function __construct($modeName = '')
    {
        if (empty($modeName)) {
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

        $query = $data = $this->db()->select($fields)->from($this->getTable());
        if ($this->useLock) {
            $params = [];
            $where = $this->db()->getSQLAssembler()->parseWhere($where, $params);
            $query->where([$where . ' for UPDATE', $params]);
        } else {
            $query->where($where);
        }

        return $query->stmt()->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 添加
     *
     * @throws CoreException
     */
    function add()
    {
        $insertId = $this->db()->add($this->getTable(), $this->makeInsertData());
        if (false !== $insertId) {
            $primaryKey = &$this->modelInfo['primary_key'];
            if ($primaryKey) {
                $this->{$primaryKey} = $insertId;
            }
        }

        return $insertId;
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
     * @param string|int $order
     * @param string|int $group_by
     * @param int $limit
     * @return mixed
     * @throws CoreException
     */
    function getAll($where = array(), $fields = '*', $order = 1, $group_by = 1, $limit = 0)
    {
        return $this->db()->getAll($this->getTable(), $fields, $where, $order, $group_by, $limit);
    }

    /**
     * 按分页获取数据
     *
     * @param array $page
     * @param array $where
     * @param string $fields
     * @param string|int $order
     * @param string|int $group_by
     * @return mixed
     * @throws CoreException
     */
    function find(&$page = array('p' => 1, 'limit' => 50), $where = array(), $fields = '*', $order = 1, $group_by = 1)
    {
        return $this->db()->find($this->getTable(), $fields, $where, $order, $page, $group_by);
    }

    /**
     * 查询数据, 并更新本类属性
     *
     * @param array $where
     * @return $this
     * @throws CoreException
     */
    function property($where = array())
    {
        $data = $this->get($where);
        if (!empty($data)) {
            $this->updateProperty($data);
        }

        return $this;
    }

    /**
     * 获取数据库链接
     *
     * @return PDOSqlDriver
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
     * 连表查询
     *
     * @param string $table 表名
     * @param string $on 当前类表别名为a, 依次为b,c,d,e...
     * @param string $type 默认左联
     * @return $this
     */
    function join($table, $on, $type = 'left')
    {
        $this->joinTables[] = [
            'name' => $table,
            'on' => $on,
            't' => strtoupper($type),
        ];

        return $this;
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
     * 仅在事务中调用get方法时生效
     *
     * @return $this
     */
    function useLock()
    {
        $this->useLock = true;
        return $this;
    }

    /**
     * 获取表名
     *
     * @return string
     * @throws CoreException
     */
    function getTable()
    {
        if (!$this->table) {
            $table = $this->getModuleInstance()->getPrefix($this->modelInfo['table']);
            if (!empty($this->joinTables)) {
                $i = 98;
                $joinTables[] = "{$table} a";
                array_map(function ($d) use (&$joinTables, &$i) {
                    $joinTables[] = sprintf("%s JOIN %s %s ON %s", $d['t'], $d['name'], chr($i), $d['on']);
                    $i++;
                }, $this->joinTables);

                $table = implode(' ', $joinTables);
            }

            $this->table = $table;
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
     * 获取数据库表字段
     *
     * @param string $alias 别名
     * @param bool $asPrefix 是否把别名加在字段名之前
     * @return string
     */
    function getFields($alias = '', $asPrefix = false)
    {
        $fieldsList = array_keys(self::$propertyInfo);
        if (!empty($alias)) {
            array_walk($fieldsList, function (&$d) use ($alias, $asPrefix) {
                if ($asPrefix) {
                    $d = "{$alias}.{$d} {$alias}_{$d}";
                } else {
                    $d = "{$alias}.{$d}";
                }
            });
        }

        return implode(', ', $fieldsList);
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
     * 获取属性数据
     *
     * @param bool $hasValue
     * @return array
     */
    function getArrayData($hasValue = false)
    {
        $data = array();
        foreach (self::$propertyInfo as $p => $c) {
            if (0 === strcasecmp($c['default_value'], 'CURRENT_TIMESTAMP')) {
                continue;
            }

            if ($hasValue && null === $this->{$p}) {
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
            throw new CoreException("请为表 {$this->modelInfo['table']} 创建索引");
        }

        $index = [];
        foreach ($this->index as $indexName) {
            $indexValue = $this->{$indexName};
            if (null === $indexValue) {
                throw new CoreException("索引 {$indexName} 的值不能为null");
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