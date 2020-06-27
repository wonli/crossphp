<?php if (!empty($data)) : ?>
    <table class="security-card">
        <tr>
            <td></td>
            <?php for ($i = 1; $i <= 9; $i++) : ?>
                <td class="g"><?= $i ?></td>
            <?php endfor ?>
        </tr>
        <?php foreach ($data as $k => $v) : ?>
            <tr>
                <td class="g"><?= $k ?></td>
                <?php for ($i = 1; $i <= 9; $i++) : ?>
                    <td class="b"><?= $v[$i] ?></td>
                <?php endfor ?>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif ?>