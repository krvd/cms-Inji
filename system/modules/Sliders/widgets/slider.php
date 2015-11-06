<?php
if (!empty($params[0])) {
    $sliderId = $params[0];
}
$slider = \Sliders\Slider::get($sliderId);
?>
<div id="sliderWidget-<?= $slider; ?>" class="carousel slide" data-ride="carousel">
  <!-- Indicators -->
  <ol class="carousel-indicators">
    <?php
    $i = 0;
    foreach ($slider->slides as $item) {
        ?>
        <li data-target="#sliderWidget-<?= $slider; ?>" data-slide-to="<?= $i; ?>" <?= !$i ? 'class="active"' : ''; ?>></li>
        <?php
        $i++;
    }
    ?>

  </ol>

  <!-- Wrapper for slides -->
  <div class="carousel-inner" role="listbox">
    <?php
    $i = 0;
    foreach ($slider->slides as $item) {
        ?>
        <div class="item <?= !$i ? 'active' : ''; ?>">
          <img src="<?= Statics::file($item->image->path); ?>" alt="<?= $item->name; ?>">
          <div class="carousel-caption">
            <?= $item->description; ?>
          </div>
        </div>
        <?php
        $i++;
    }
    ?>
  </div>

  <!-- Controls -->
  <a class="left carousel-control" href="#sliderWidget-<?= $slider; ?>" role="button" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#sliderWidget-<?= $slider; ?>" role="button" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>