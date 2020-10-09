<?php
/**
 * Class Tree
 */

namespace lib\Tree;

class Tree
{
    /**
     * 数据
     *
     * @var array
     */
    public $data = [];

    /**
     * 父亲节点与孩子节点的关系映射
     *
     * @var array
     */
    public $child = [-1 => []];

    /**
     * 初始节点为0
     *
     * @var array
     */
    public $layer = [0 => 0];

    /**
     * 非叶子节点的节点，也就是有孩子节点的节点
     *
     * @var array
     */
    public $parent = [];

    /**
     * 子节点的名称
     *
     * @var string
     */
    public $idField = '';

    /**
     * 一般是分类名称
     *
     * @var string
     */
    public $valueField = '';

    /**
     * 父节点的名称
     *
     * @var string
     */
    public $parentField = '';

    /**
     * 子节点名称
     *
     * @var string
     */
    protected $childrenNodeName = 'children';

    /**
     * 构造函数
     *
     * @param string $value
     */
    function __construct($value = 'root')
    {
        $this->setNode(0, -1, $value);
    }

    /**
     * 设置子节点名称
     *
     * @param string $name
     * @return Tree
     */
    function setChildrenNodeName(string $name): self
    {
        $this->childrenNodeName = $name;
        return $this;
    }

    /**
     * 构造树
     *
     * @param array $nodes 结点数组
     * @param string $idField
     * @param string $parentField
     * @param string $valueField
     */
    function setTree(array $nodes, string $idField, string $parentField, string $valueField)
    {
        $this->idField = $idField;
        $this->valueField = $valueField;
        $this->parentField = $parentField;
        foreach ($nodes as $node) {
            $this->setNode($node[$this->idField], $node[$this->parentField], $node);
        }

        $this->setLayer();
    }

    /**
     * 先根遍历，数组格式 id,子id,value对应的值
     * <pre>
     * [
     *     'id' => '',
     *     'value' => '',
     *     'children' => [
     *          ['id' => '', 'value' => '', children => []],
     *      ]
     * ]
     * </pre>
     *
     * @param int $root
     * @param null $layer
     * @param bool $clear
     * @return array
     */
    function getArrayList($root = 0, $layer = null, $clear = false)
    {
        $data = [];
        foreach ($this->child[$root] as $id) {
            if ($layer && $this->layer[$this->parent[$id]] > $layer - 1) {
                continue;
            }

            $childrenNodeData = $this->child[$id] ? $this->getArrayList($id, $layer) : [];
            if (true === $clear) {
                $data[] = [
                    $this->idField => $id,
                    $this->valueField => $this->getValue($id),
                    $this->childrenNodeName => $childrenNodeData
                ];
            } else {
                $data[] = array_merge($this->data[$id], [
                    $this->childrenNodeName => $childrenNodeData
                ]);
            }
        }

        return $data;
    }

    /**
     * 获取父节点
     *
     * @param $id
     * @return mixed
     */
    function getParent($id)
    {
        return $this->parent[$id];
    }

    /**
     * 取得祖先，不包括自身
     *
     * @param $id
     * @return mixed
     */
    function getParents($id)
    {
        while ($this->parent[$id] != -1) {
            $id = $parent[$this->layer[$id]] = $this->parent[$id];
        }

        ksort($parent);
        reset($parent);

        return $parent;
    }

    /**
     * 获取子节点
     *
     * @param $id
     * @return mixed
     */
    function getChild($id)
    {
        return $this->child[$id];
    }

    /**
     * 取得子孙，包括自身，先根遍历
     *
     * @param int $id
     * @param null $except
     * @return array
     */
    function getChildren($id = 0, $except = null)
    {
        $child = array($id);
        $this->getList($child, $id, $except);
        unset($child[0]);

        return $child;
    }

    /**
     * 生成HTML表单options选项
     *
     * @param int $layer
     * @param int $root
     * @param null $except
     * @param string $space
     * @return array
     */
    function makeSelectOptions($layer = 0, $root = 0, $except = null, $space = '&nbsp;&nbsp;')
    {
        $options = [];
        $children = $this->getChildren($root, $except);
        foreach ($children as $id) {
            if ($id > 0 && ($layer <= 0 || $this->getLayer($id) <= $layer)) {
                $options[$id] = $this->getLayer($id, $space) . htmlspecialchars($this->getValue($id));
            }
        }

        return $options;
    }

    /**
     * 设置结点
     *
     * @param $id
     * @param $parent
     * @param $value
     */
    private function setNode($id, $parent, $value)
    {
        $parent = $parent ? $parent : 0;

        $this->data[$id] = $value;
        if (!isset($this->child[$id])) {
            $this->child[$id] = [];
        }

        if (isset($this->child[$parent])) {
            $this->child[$parent][] = $id;
        } else {
            $this->child[$parent] = [$id];
        }

        $this->parent[$id] = $parent;
    }

    /**
     * 计算layer
     *
     * @param int $root
     */
    private function setLayer($root = 0)
    {
        foreach ($this->child[$root] as $id) {
            $this->layer[$id] = $this->layer[$this->parent[$id]] + 1;
            if ($this->child[$id]) {
                $this->setLayer($id);
            }
        }
    }

    /**
     * 先根遍历，不包括root
     *
     * @param $tree
     * @param int $root
     * @param null $except 除外的结点，用于编辑结点时，上级不能选择自身及子结点
     */
    private function getList(&$tree, $root = 0, $except = null)
    {
        foreach ($this->child[$root] as $id) {
            if ($id == $except) {
                continue;
            }

            $tree[] = $id;
            if ($this->child[$id]) {
                $this->getList($tree, $id, $except);
            }
        }
    }

    /**
     * 数据id的值
     *
     * @param $id
     * @return mixed
     */
    private function getValue($id)
    {
        return $this->data[$id][$this->valueField];
    }

    /**
     * 对应关系
     *
     * @param $id
     * @param bool $space
     * @return string
     */
    private function getLayer($id, $space = false)
    {
        return $space ? str_repeat($space, $this->layer[$id]) : $this->layer[$id];
    }
}
