<div class="panel panel-nav-case">
    <div class="panel-heading">
        <a href="javascript:void(0)" class="nav-title"
           onclick="apiClassList('<?= $data['group_key'] ?? '' ?>')">
            </i><?= $data['group_name'] ?? '' ?>
        </a>
    </div>
    <div class="panel-body menu-list" id="category<?= ($data['group_key'] ?? '') ?>" style="display: none">
        <?php
        if (!empty($data['children'])) {
            foreach ($data['children'] as $api) {
                $list_container_id = $api['group_key'] . '/' . $api['id'];
                ?>
                <div class="row a-nav-menu">
                    <i class="fa dot"></i>
                    <a href="javascript:void(0)" class="<?= $list_container_id ?>"
                       onclick="getTestCase('<?= $api['group_key'] ?>', '<?= $api['id'] ?>')">
                        <?= $api['api_name'] ?? '' ?>
                    </a>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>
