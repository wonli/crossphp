<?= $this->makeFileAnnotate($data['author'], $data['name']) ?>


namespace <?= $data['namespace'] ?>;

use Cross\Exception\CoreException;
<?= $this->makeUse($data['use']); ?>


<?= $this->makeClassAnnotate($data['name'], $data['namespace']) ?>
class <?= $data['name'] ?> extends <?= $data['extends'] . PHP_EOL ?>
{
    /**
     * 默认方法
     *
     * @throws CoreException
     */
    function index()
    {
        $this->display($this->data);
    }

}