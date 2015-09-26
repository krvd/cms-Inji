<?php $this->helper->start_box('Заливка с другого сайта', 'cogs'); ?>
<a href ='<?= $this->url->module(); ?>/parseWeb/florange'>Florange.ru</a>
<?php $this->helper->end_box();
?>
<?php
if (isset($catalogs)) {

    $this->helper->start_box('Каталоги', 'cogs');
    foreach ($catalogs as $catalogNum => $catalog) {

        echo "<b><a href = '{$this->url->module()}/parseWeb/{$site}/{$catalogNum}'>{$catalog['name']}</a></b> <small><a href = '{$catalog['href']}' target = 'blank'>Перейти</a></small> <button role ='button' class = 'btn btn-sm btn-success startParse' data-catalognum = '{$catalogNum}' data-loading-text = 'Подождите' data-site = '{$site}'>Закачать</button><br />";
    }
    $this->helper->end_box();
}
?>
<script>
    $(function () {
      $('.startParse').click(function () {
        $(this).attr('disabled', true);
        startParse($(this), $(this).data('site'), $(this).data('catalognum'))
      });
      function startParse(btn, site, catalogNum) {
        $.get('/admin/ecommerce/processParseWeb/' + site + '/' + catalogNum, function (data) {
          if (data != 'success') {
            //btn.button('loading');
            startParse(btn, site, catalogNum);
          } else {
            btn.removeAttr('disabled');
            //btn.button('reset');
          }
        });
      }
    });
</script>