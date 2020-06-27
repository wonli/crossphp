<?= $this->makeFileAnnotate($data['author'], $data['name']) ?>


namespace <?= $data['namespace'] ?>;

<?= $this->makeUse($data['use']); ?>

<?= $this->makeClassAnnotate($data['name'], $data['namespace']) ?>
class <?= $data['name'] ?> extends <?= $data['extends'] . PHP_EOL ?>
{

    <?= $this->makeActionAnnotate() ?>
    function index($data = [])
    {
        <?= $this->makeActionBody($data['tplName']).PHP_EOL ?>
    }

}