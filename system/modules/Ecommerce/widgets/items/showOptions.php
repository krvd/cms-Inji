<?php
$query = $_GET;
$path = Controller::$cur->method != 'itemList' ? '/ecommerce/itemList' : '';
?>
<div class="ecommerce-showoptions">
  <div class="ecommerce-showoptions-sort">
    <span class="caption">Сортировка:</span>
    <a href="<?= $path; ?>?<?= http_build_query(array_merge($query, ['sort' => ['price' => 'asc']])); ?>">По цене</a> 
    <a href="<?= $path; ?>?<?= http_build_query(array_merge($query, ['sort' => ['sales' => 'desc']])); ?>">По популярности</a>
  </div>
  <div class="ecommerce-showoptions-view">
    <span class="caption">Вид:</span>
    <a href="<?= $path; ?>?<?= http_build_query(array_merge($query, ['limit' => 24])); ?>">24</a> 
    <a href="<?= $path; ?>?<?= http_build_query(array_merge($query, ['limit' => 48])); ?>">48</a> 
    <a href="<?= $path; ?>?<?= http_build_query(array_merge($query, ['limit' => 72])); ?>">72</a> 
    <a href ='#' onclick="inji.onLoad(function(){$('.items-icons').show();$('.items-table').hide();});return false;" class="glyphicon glyphicon-th-large"></a>
    <a href ='#' onclick="inji.onLoad(function(){$('.items-table').show();$('.items-icons').hide();});return false;" class="glyphicon glyphicon-th-list"></a>
  </div>
</div>