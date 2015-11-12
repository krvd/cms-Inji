<h2>Личный кабинет</h2>
<div class="row">
  <div class="col-sm-3">
    <ul class="nav nav-pills nav-stacked">
      <?php
      foreach ($sections as $sectionName => $section) {
          if (!empty($section['fullWidget']) || !empty($section['href'])) {
              ?>
              <li role="presentation" <?= $activeSection == $sectionName ? 'class="active"' : ''; ?>>
                <a href="<?= !empty($section['href']) ? $section['href'] : "/users/cabinet/{$sectionName}"; ?>"><?= $section['name']; ?></a>
              </li>
              <?php
          }
      }
      ?>
      <li role="presentation">
        <a href="?logout">Выход</a>
      </li>
    </ul>
  </div>
  <div class="col-sm-9">
    <?php
    if (empty($activeSection) || empty($sections[$activeSection]['fullWidget'])) {
        foreach ($sections as $section) {
            if (!empty($section['smallWidget'])) {
                ?>
                <div class="col-sm-4"><?= $this->widget($section['smallWidget']); ?></div>
                <?php
            }
        }
    } else {
        $this->widget($sections[$activeSection]['fullWidget']);
    }
    ?>
  </div>
</div>