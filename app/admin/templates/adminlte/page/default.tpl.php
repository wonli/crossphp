<?php
/**
 * @Auth: wonli <wonli@live.com>
 * default.tpl.php
 */
$page_content = '';
if (!empty($data)) {
    $wrap_class = &$data['pagination_class'];
    if ($data['p'] > $data['half'] + 1) {
        $data['params']['p'] = 1;
        $page_content .= $this->wrap('li')->a(1, $this->url($data['controller'], $data['params']) . $data['anchor']) .
            $this->wrap('li')->a(' ... ', 'javascript:void(0)');
    }

    for ($i = max(1, $data['p'] - $data['half']), $j = min($data['p'] + $data['half'], $data['total_page']); $i <= $j; $i++) {
        if ($i == $data['p']) {
            $href = 'javascript:void(0)';
            $attr = array('class' => 'active');
        } else {
            $data['params']['p'] = $i;
            $attr = array();
            $href = $this->url($data['controller'], $data['params']) . $data['anchor'];
        }
        $page_content .= $this->wrap('li', $attr)->a($i, $href);
    }

    if ($data['p'] + $data ['half'] < $data['total_page']) {
        $data['params']['p'] = $data['total_page'];
        $page_content .= $this->wrap('li')->a(' ... ', 'javascript:void(0)') . $this->wrap('li')->a($data['total_page'], $this->url($data['controller'], $data['params']) . $data['anchor']);
    }

    echo $this->wrap('ul', array('class' => $wrap_class))->html($page_content);
}

