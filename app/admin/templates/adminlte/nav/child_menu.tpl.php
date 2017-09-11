<ul class="treeview-menu">
    <?php
    if (!empty($data['child'])) {
        foreach ($data['child'] as $m) {
            $icon = 'fa fa-circle-o';
            if (!empty($m['icon'])) {
                $icon = &$m['icon'];
            }

            if ($m['type'] == 1) {
                $link = $this->url("{$data['controller']}:{$m['link']}");
                $target = '_self';
            } else {
                $link = $m['link'];
                $target = '_blank';
            }

            $class = '';
            if (0 === strcasecmp($m['link'], $this->action)) {
                $data['action_menu_name'] = $m['name'];
                $class = 'active';
            }
            ?>
            <li class="<?php echo $class ?>">
                <a href="<?php echo $link ?>" target="<?php echo $target ?>">
                    <i class="<?php echo $icon ?>"></i>
                    <?php echo $m['name'] ?>
                </a>
            </li>
            <?php
        }
    }
    ?>
</ul>
