<?php if (!empty($data)) : ?>
    <table class="security-card">
        <tr>
            <td></td>
            <?php for ($i = 1; $i <= 9; $i++) : ?>
                <td class="g"><?php echo $i ?></td>
            <?php endfor ?>
        </tr>
        <?php foreach ($data as $k => $v) : ?>
            <tr>
                <td class="g"><?php echo $k ?></td>
                <?php for ($i = 1; $i <= 9; $i++) : ?>
                    <td class="b"><?php echo $v[$i] ?></td>
                <?php endfor ?>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif ?>