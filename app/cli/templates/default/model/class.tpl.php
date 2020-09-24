<?= '<?php' . PHP_EOL . PHP_EOL ?>
<?php if(!empty($data['namespace'])) : ?>
namespace <?= $data['namespace'] ?>;
<?php endif ?>

<?= $this->makeClassName($data['modelName'], $data['tableNamespace'], $data['type']) ?>
{
    <?php $this->makeModelFields($data['mateData']); ?>
<?php if($data['type'] == 'class') : ?>

    function __construct()
    {
        parent::__construct(new <?= $data['tableClass'] ?>());
    }
<?php endif ?>

    /**
     * @inheritdoc
     */
    protected function autoJoin()
    {

    }

    /**
     * @inheritdoc
     */
    protected function autoProcessData(array &$data)
    {

    }
}