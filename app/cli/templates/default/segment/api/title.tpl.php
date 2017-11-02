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
        <ul class="nav navbar-nav navbar-right">
            <li>
                <a id="commonModalSwitch" href="javascript:void(0)">公共参数配置</a>
            </li>
        </ul>
    </div>
</div>
<?php
/**
 * @Auth: wonli <wonli@live.com>
 * title.tpl.php
 */
