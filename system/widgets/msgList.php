<?php
foreach (Msg::get() as $msg) {
    ?>
    <div class="alert alert-<?= $msg['status']; ?> alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>Внимание!</strong> <?= $msg['text']; ?>
    </div>
    <?php
}
Msg::flush();
?>
