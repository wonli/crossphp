<?= '<?php' . PHP_EOL . PHP_EOL ?>
<?php if(!empty($data['namespace'])) : ?>
namespace <?= $data['namespace'] ?>;
<?php endif ?>

<?= $this->makeModelInfoClassName($data['name'], $data['type']) ?>
{
    /**
     * 主键名
     *
     * @var string
     */
    protected $pk = '<?= $data['pk'] ?>';

    /**
     * 模型信息
     *
     * @var array
     */
    protected $modelInfo = [
        <?= $this->makeArrayProperty($data['model_info'], 8) . PHP_EOL ?>
    ];
<?php if(!empty($data['split_info'])) : ?>

    /**
     * 分表配置
     *
     * @var array
     */
    protected $splitConfig = [
        <?= $this->makeArrayProperty($data['split_info'], 8) . PHP_EOL ?>
    ];
<?php else : ?>

    /**
     * 分表配置
     *
     * @var array
     */
    protected $splitConfig = [];
<?php endif ?>

    /**
     * 表字段属性
     *
     * @var array
     */
    protected $fieldsInfo = [
        <?php $this->makeModelInfo($data['mate_data']) ?>
    ];

    /**
     * 获取表主键
     *
     * @return string
     */
    function getPK(): string
    {
        return $this->pk;
    }

    /**
     * 获取模型信息
     *
     * @return array
     */
    function getModelInfo(): array
    {
        return $this->modelInfo;
    }

    /**
     * 获取表字段信息
     *
     * @return array
     */
    function getFieldInfo(): array
    {
        return $this->fieldsInfo;
    }

    /**
     * 获取分表配置
     *
     * @return array
     */
    function getSplitConfig(): array
    {
        return $this->splitConfig;
    }

    /**
     * 获取数据库配置文件地址
     *
     * @return string
     */
    function getConfigFile(): string
    {
        return __DIR__ . '/<?= ltrim($data['db_config_path'], '/') ?>';
    }
}