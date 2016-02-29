<div class="ecommerce-sidebar-categorys">
  <?php
  $tree = new Ui\Tree();
  $tree->ul('\Ecommerce\Category', 0, function($categoryItem) use($category) {
      if ($category->id == $categoryItem->id) {
          $class = 'active';
      } else {
          $class = '';
      }
      return [
          'class' => $class,
          'text' => "<a {$class} href = '/ecommerce/itemList/{$categoryItem->id}'>{$categoryItem->name}</a>"
      ];
  });
  ?>
</div>