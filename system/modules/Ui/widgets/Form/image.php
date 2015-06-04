<div class ='form-group'>
    <label><?= $label; ?></label>
    <img src ='<?= !empty($options['value']) ? $options['value'] : '/static/system/images/no-image.png'; ?>?resize=50x50&resize_crop=q' class="pull-right" />
    <input type ='file' name ='<?= $name; ?>'/>
    <div class="clearfix"></div>
</div>