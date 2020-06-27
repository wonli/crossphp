<?php
/**
 * @author wonli <wonli@live.com>
 * theme.tpl.php
 */

$themes = &$data['themes'];
$defaultTheme = '';
if (isset($data['default'])) {
    $defaultTheme = $themes[$data['default']];
}
?>
<ul class="list-unstyled clearfix choose-theme">
    <?php
    if (!empty($themes)) {
        foreach ($themes as $name => $d) {
            $act = $this->url('security:profile', array('act' => 'setTheme', 'theme' => $name));
            ?>
            <li style="float:left;width:16.6666%;padding:5px 10px 5px 0">
                <a href="<?= $act ?>"
                   style="display:block;border:1px solid #f2f2f2;" class="clearfix">
                    <div>
                        <span style="display:block;width:20%;float:left;height:10px;background:<?= $d['tlc'] ?> !important"></span>
                        <span style="display:block;width:80%;float:left;height:10px;background:<?= $d['trc'] ?> !important"></span>
                    </div>
                    <div>
                        <span style="display:block;width:20%;float:left;height:20px;background:<?= $d['lc'] ?> !important"></span>
                        <span style="display:block;width:80%;float:left;height:20px;background:#f4f5f7"></span>
                    </div>
                </a>
            </li>
            <?php
        }
    }
    ?>
</ul>