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
<ul class="list-unstyled clearfix">
    <?php
    if (!empty($themes)) {
        foreach ($themes as $name => $d) {
            $act = $this->url('security:profile', array('act' => 'setTheme', 'theme' => $name));
            ?>
            <li style="float:left;width:16.6666%;padding:5px 10px 5px 0">
                <a href="<?php echo $act ?>"
                   style="display:block;border:1px solid #cecece;box-shadow:-1px 1px 2px 0 rgba(0,0,0,0.2)"
                   class="clearfix full-opacity-hover">
                    <div>
                        <span style="display:block;width:20%;float:left;height:10px;background:<?php echo $d['tlc'] ?> !important"></span>
                        <span style="display:block;width:80%;float:left;height:10px;background:<?php echo $d['trc'] ?> !important"></span>
                    </div>
                    <div>
                        <span style="display:block;width:20%;float:left;height:20px;background:<?php echo $d['lc'] ?> !important"></span>
                        <span style="display:block;width:80%;float:left;height:20px;background:#f4f5f7"></span>
                    </div>
                </a>
            </li>
            <?php
        }
    }
    ?>
</ul>