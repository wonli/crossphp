<?php echo $this->makeFileAnnotate($data['author'], $data['name']) ?>


namespace <?php echo $data['namespace'] ?>;

<?php echo $this->makeUse($data['use']); ?>

<?php echo $this->makeClassAnnotate($data['name'], $data['namespace']) ?>
class <?php echo $data['name'] ?> extends <?php echo $data['extends'] . PHP_EOL ?>
{

    <?php echo $this->makeActionAnnotate() ?>
    function index($data = [])
    {
        <?php echo $this->makeActionBody($data['tplName']).PHP_EOL ?>
    }

}