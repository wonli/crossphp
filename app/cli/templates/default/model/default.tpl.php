<?= '<?php' . PHP_EOL . PHP_EOL ?>
<?php if(!empty($data['namespace'])) : ?>
namespace <?= $data['namespace'] ?>;
<?php endif ?>

<?php $this->makeObjectName($data['name'], $data['type']) ?>
{
    <?php $this->makeModelFields($data['mate_data']); ?>

    /**
     * 模型信息
     *
     * @var array
     */
    protected $modelInfo = [
        <?php $this->makeArrayProperty($data['model_info']) ?>
    ];
<?php if(!empty($data['split_info'])) : ?>

    /**
     * 分表配置
     *
     * @var array
     */
    protected $splitConfig = [
        <?php $this->makeArrayProperty($data['split_info']) ?>
    ];
<?php endif ?>

    /**
     * 表字段属性
     *
     * @var array
     */
    protected static $fieldsInfo = [
        <?php $this->makeModelInfo($data['mate_data']) ?>
    ];

    /**
     * ⚠️以上代码是自动生的，任何修改都将被覆盖
     * ⚠️请在此成员变量之后编写业务代码
     * ⚠️请不要修改或使用此成员变量
     *
     * @var mixed
     */
    private $autoGenCodeFlag;
}