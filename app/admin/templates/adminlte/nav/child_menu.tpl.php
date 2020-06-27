<ul class="treeview-menu">
    <?php
    if (!empty($data['child'])) {
        foreach ($data['child'] as $m) {
            if (!empty($m['icon'])) {
                $icon = &$m['icon'];
            } else {
                $icon = 'fa dot';
            }

            if ($m['type'] == 1) {
                $link = $this->url("{$data['controller']}:{$m['link']}");
                $target = '_self';
            } else {
                $link = $m['link'];
                $target = '_blank';
            }

            $class = '';
            if (0 === strcasecmp($data['controller'], $data['current_controller']) &&
                0 === strcasecmp($m['link'], $this->action)) {
                $class = 'active';
            }
            ?>
            <li class="<?= $class ?>">
                <a href="<?= $link ?>" target="<?= $target ?>">
                    <i class="<?= $icon ?>"></i>
                    <?= $m['name'] ?>
                </a>
            </li>
            <?php
        }
    }
    ?>
</ul>
