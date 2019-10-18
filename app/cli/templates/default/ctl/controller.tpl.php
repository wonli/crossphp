<?php echo $this->makeFileAnnotate($data['author'], $data['name']) ?>


namespace <?php echo $data['namespace'] ?>;

use Cross\Exception\CoreException;
<?php echo $this->makeUse($data['use']); ?>


<?php echo $this->makeClassAnnotate($data['name'], $data['namespace']) ?>
class <?php echo $data['name'] ?> extends <?php echo $data['extends'] . PHP_EOL ?>
{
    /**
     * 默认方法
     * @throws CoreException
     */
    function index()
    {
        return $this->display($this->data);
    }

}