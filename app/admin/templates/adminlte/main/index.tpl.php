<div class="form-group has-feedback">
    <input type="text" class="form-control" name="user" placeholder="用户名">
    <span class="glyphicon glyphicon-user form-control-feedback"></span>
</div>
<div class="form-group has-feedback">
    <input type="password" class="form-control" name="pwd" placeholder="密码">
    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
</div>

<div class="form-group has-feedback">
    <div class="input-group">
        <div class="input-group-btn">
            <button type="button" class="btn btn-default"><?php echo $data['v'] ?></button>
        </div>
        <input type="hidden" name="v" value="<?php echo $data['v'] ?>">
        <input type="text" class="form-control" name="vv" placeholder="密保卡坐标对应的值">
    </div>
</div>



