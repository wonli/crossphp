<?= '<?php' . PHP_EOL . PHP_EOL ?>
<?php if(!empty($data['namespace'])) : ?>
namespace <?= $data['namespace'] ?>;
<?php endif ?>

<?= $this->makeClassName($data['name'], $data['modelNamespace'], $data['type']) ?>
{
    <?php $this->makeModelFields($data['mate_data']); ?>
<?php if($data['type'] == 'class') : ?>

    function __construct()
    {
        parent::__construct(new <?= $data['modelClass'] ?>());
    }
<?php endif ?>
}