
    <div class ='row'>
        <div class = 'page_text col-md-8 col-md-offset-2'>
            <?php
            $this->parseSource($material->material_text);
            ?>
            <form method="post">
                <div class ='row'>
                    <div class ='col-sm-4'>
                        <div class ='form-group'>
                            <label>Имя</label>
                            <input name="Forms[3][input6]" type="text" class ='form-control' required />
                        </div>
                    </div>
                    <div class ='col-sm-4'>
                        <div class ='form-group'>
                            <label>Электронный адрес</label>
                            <input name="Forms[3][input7]" type="text" class ='form-control' required />
                        </div>
                    </div>
                    <div class ='col-sm-4'>
                        <div class ='form-group'>
                            <label>Мобильный телефон</label>
                            <input name="Forms[3][input8]" type="text" class ='form-control' required />
                        </div>
                    </div>
                </div>
                <input name="sub" type="hidden" value="1" />
                <div class ='form-group'>
                <button class = 'btn btn-primary'>Отправить</button>
                </div>
            </form>
            <p>Если вы хотите не только узнать информацию о товаре и компании Florange, но и стать стилистом и зарабатывать хорошие деньги легко и с удовольствием, перейдите в раздел и узнайте прямо сейчас:</p>

            <?php
            $nexts = json_decode($material->material_nexts, true);

            if ($nexts) {
                echo '<p style = "text-align:center">';
                foreach ($nexts as $next) {
                    $nextpage = Material::get($next['material_id']);
                    echo "<a href = '{$nextpage->material_chpu}' class = 'nextbtn'>{$next['name']}</a>";
                }
                echo '</p>';
            }
            ?>
            <div style="height:50px;">&nbsp;</div>
        </div>
    </div>
