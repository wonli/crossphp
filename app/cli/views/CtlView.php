<?php
/**
 * @author ideaa <ideaa@qq.com>
 * CtlView.php
 */

namespace app\cli\views;


/**
 * Class CtlView
 * @package app\cli\views
 */
class CtlView extends CliView
{

    /**
     * 生成默认配置文件
     *
     * @param string $propertyFile
     * @return bool|int
     */
    function makeModelFile(string $propertyFile)
    {
        $content = $this->tpl('ctl/ctl.config', true, false);
        return file_put_contents($propertyFile, $content);
    }

    /**
     * 生成控制器
     *
     * @param string $dest 目标文件绝对路径
     * @param array $config
     * @return bool|int
     */
    function makeController(string $dest, array $config)
    {
        $data = array(
            'app' => $config['app'],

            'use' => $config['controllerUse'],
            'name' => $config['controllerName'],
            'namespace' => $config['controllerNamespace'],
            'extends' => $config['extends'],

            'author' => $config['author'],
        );

        $content = $this->obRenderTpl('ctl/controller', $data);
        return file_put_contents($dest, $content);
    }

    /**
     * 生成视图控制器
     *
     * @param string $dest 目标文件绝对路径
     * @param array $config
     * @return bool|int
     */
    function makeViewController(string $dest, array $config)
    {
        $data = array(
            'app' => $config['app'],

            'use' => $config['viewControllerUse'],
            'name' => $config['viewControllerName'],
            'namespace' => $config['viewControllerNamespace'],
            'extends' => $config['viewExtends'],

            'author' => $config['author'],

            //是否使用模板
            'useTpl' => $config['makeTpl'],
            'tplName' => $config['tplName']
        );

        $content = $this->obRenderTpl('ctl/view', $data);
        return file_put_contents($dest, $content);
    }

    /**
     * 保存模板
     *
     * @param string $dest 目标文件绝对路径
     * @param array $config
     * @return bool|int
     */
    function makeTpl(string $dest, array $config)
    {
        $data = array(
            'path' => $dest,
            'author' => &$config['author']
        );

        $content = $this->obRenderTpl('ctl/empty', $data);
        return file_put_contents($dest, $content);
    }

    /**
     * 输出文件注释
     *
     * @param string $author
     * @param string $filename
     * @return string
     */
    protected function makeFileAnnotate(string $author, string $filename)
    {
        $content = array(
            '<?php',
            '/**',
            ' * @author ' . $author,
            ' * ' . $filename . '.php',
            ' */',
        );

        return $this->makeLines($content);
    }

    /**
     * 输出类注释
     *
     * @param string $name
     * @param string $namespace
     * @return string
     */
    protected function makeClassAnnotate(string $name, string $namespace)
    {
        $content = array(
            '/**',
            ' * Class ' . $name,
            ' * @package ' . $namespace,
            ' */',
            ''
        );

        return $this->makeLines($content);
    }

    /**
     * 生成方法注释
     *
     * @return string
     */
    protected function makeActionAnnotate()
    {
        $content = array(
            '/**',
            ' * 默认方法',
            ' *',
            ' * @param array $data',
            ' */'
        );

        return $this->makeLines($content, 4) . PHP_EOL;
    }

    /**
     * 生成方法体
     *
     * @param string $tplName
     * @return string
     */
    protected function makeActionBody(string $tplName)
    {
        if (!empty($tplName)) {
            return $this->makeLines(array(
                '$this->renderTpl(\'' . $tplName . '\', $data);',
            ), 8);
        }

        return '';
    }

    /**
     * 生成use
     *
     * @param string|array $content
     * @return string
     */
    protected function makeUse($content)
    {
        if (empty($content)) {
            return '';
        }

        if (!is_array($content)) {
            $content = array($content);
        }

        array_walk($content, function (&$d) {
            $d = "use {$d};";
        });

        return $this->makeLines($content);
    }

    /**
     * 将数组中的内容一行一行的输入到模板中
     *
     * @param array $contents
     * @param int $space
     * @return string
     */
    protected function makeLines(array $contents, $space = 0)
    {
        $lines = '';
        if (!empty($contents)) {
            if ($space > 0) {
                $addSpace = str_pad(' ', $space, ' ');
                array_walk($contents, function (&$d, $k) use ($addSpace) {
                    //为了保持代码整洁第一行不会追加空格
                    //请在模版中的缩进位置调用此方法
                    if ($k > 0) {
                        $d = $addSpace . $d;
                    }
                });
            }
            $lines = implode(PHP_EOL, $contents);
        }

        return $lines;
    }

}