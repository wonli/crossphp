<div class="container">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="" title="生成时间 <?php echo date('Y-m-d H:i:s') ?>">
            <?php echo $data['title'] ?>
            <small><sup>v<?php echo $data['version'] ?></sup></small>
        </a>
    </div>

    <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
            <?php
            if (!empty($data['top_nav'])) {
                foreach ($data['top_nav'] as $name => $url) {
                    if (is_array($url)) {
                        $url['@content'] = $name;
                        echo $this->wrap('li')->wrap('a', $url)->html('');
                    } else {
                        echo $this->wrap('li')->a($name, $url);
                    }
                }
            }
            ?>
        </ul>

        <ul class="nav navbar-nav navbar-right">
            <?php
            if ($data['has_global_params']) {
                echo $this->wrap('li')->a('公共参数配置', 'javascript:void(0)', array(
                    'id' => 'commonModalSwitch'
                ));
            }
            ?>
        </ul>
    </div>
</div>
<?php
/**
 * @author wonli <wonli@live.com>
 * title.tpl.php
 */
