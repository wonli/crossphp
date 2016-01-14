<div class="box">
    <div class="box-body table-responsive">
        <table
            style="width:450px;height:450px;text-align:center;border:1px solid #151428;margin:20px;border-collapse: collapse;">
            <tr>
                <td></td>
                <?php for ($i = 1; $i <= 9; $i++) : ?>
                    <td style="background:#151428;color:#ffffff;width:40px;border:1px solid #808080"><?php echo $i ?></td>
                <?php endfor ?>
            </tr>
            <?php foreach ($data as $k => $v) : ?>
                <tr>
                    <td style="background:#151428;color:#ffffff;width:40px;border:1px solid #808080"><?php echo $k ?></td>
                    <?php for ($i = 1; $i <= 9; $i++) : ?>
                        <td style="border:1px solid #808080"><?php echo $v[$i] ?></td>
                    <?php endfor ?>
                </tr>
            <?php endforeach ?>
        </table>
    </div>
    <div class="box-footer">
        绑定密码卡后, 登录时需要输入密保卡坐标上对应的数值
    </div>
</div>

