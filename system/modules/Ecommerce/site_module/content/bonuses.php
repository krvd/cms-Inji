<h1>Мои бонусы</h1>
<div class ="row">
    <div class="<?php if (!($cart && $cart['cc_status'] == 5)) { ?>col-sm-6<?php } ?>">
        <h2>Накопления</h2>
        <table>
            <tr>
                <td></td>
            </tr>
        </table>
        <?php
        $date = new DateTime();
        $this->db->where('cub_date', $date->format('Y-m-1'), '>=');
        $this->db->where('cub_user_id', $this->users->cur->user_id);
        $this->db->where('cub_true', 1);
        $this->db->where('cub_profit', 0);
        $this->db->group('cub_curency');
        $this->db->cols = '`cub_curency`, SUM(cub_sum)as `count`';
        $cubs = $this->db->result_array($this->db->select('catalog_user_bonuses'), 'cub_curency');
        if (!empty($cubs['ВР']['count']))
            echo "Выгодные рубли: <b>{$cubs['ВР']['count']}</b><br />";
        else
            echo "Выгодные рубли: <b>0</b><br />";
        if (!empty($cubs['УЕ']['count']))
            echo "Условные единицы: <b>{$cubs['УЕ']['count']}</b>";
        else
            echo "Условные единицы: <b>0</b>";
        ?>
        <h2>Последние начисления</h2>
        <?php
        $date = new DateTime();
        $this->db->where('cub_user_id', $this->users->cur->user_id);
        $this->db->order('cub_date', 'desc');
        $this->db->where('cub_profit', 0);
        $this->db->limit(0, 20);
        $cubs = $this->db->result_array($this->db->select('catalog_user_bonuses'));
        if (!$cubs) {
            echo 'Начислений нет';
        } else {
            echo '<table class = "table table-stripped">'
            . '<tr>'
            . '<th>Тип</th>'
            . '<th>Сумма</th>'
            . '<th>Валюта</th>'
            . '<th>Источник</th>'
            . '<th>Время</th>'
            . '</tr>';
            foreach ($cubs as $cub) {

                if ($cub['cub_true'])
                    $class = 'class ="text-muted"';
                else
                    $class = '';
                if (is_numeric($cub['cub_marketing_type'])) {
                    $catalog = Catalog::get($cub['cub_marketing_type']);
                    $cub['cub_marketing_type'] = $catalog->catalog_name;
                }
                echo "<tr>";
                echo "<td {$class}>{$cub['cub_type']}</td>";
                echo "<td {$class}>{$cub['cub_sum']}</td>";

                echo "<td {$class}>{$cub['cub_curency']}</td>";
                echo "<td {$class}>{$cub['cub_marketing_type']}</td>";
                echo "<td {$class}>{$cub['cub_date']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>

    </div>
    <?php if (!($cart && $cart['cc_status'] == 5)) { ?><div class="col-sm-6">
        <?php
        if ($cart && $cart['cc_status'] != 5) {
            echo 'Заявка на получение уже отправлена, вам необходимо оплатить 250р';
        } else {
            ?>
                <form action ="" method ="post">
                    <h2>Оформить карту участника клуба</h2>
                    <p>Эта карта позволит вам накапливать бонусные рубли, которыми вы сможете раплатить за товары на сайте! Стоимость карты: 250р</p>
                    <?php if (!$this->users->cur->user_id || !$this->users->cur->user_phone) {
                        ?>
                        <div class ='form-group'>
                            <label>Ваш номер телефона</label>
                            <input type ='text' name ='user_phone'  class = 'form-control' required  placeholder = '+79876543210' value ='<?= (!empty($_POST['user_phone'])) ? $_POST['user_phone'] : ''; ?>'  /> 
                        </div>
                    <?php } ?>

                    <h3>Адрес доставки</h3>
                    <div class ='row'>
                        <div class ='col-md-6'>
                            <div class ='form-group'>
                                <label>Город</label>
                                <input type ='text' name ='city'  class = 'form-control'  required  placeholder = 'Например: Красноярск' value ='<?= (!empty($_POST['city'])) ? $_POST['city'] : ''; ?>' /> 
                            </div>
                        </div>                                              
                        <div class ='col-md-6'>
                            <div class ='form-group'>
                                <label>Улица</label>
                                <input type ='text' name ='street'  class = 'form-control'  required  placeholder = 'Например: Ленина' value ='<?= (!empty($_POST['street'])) ? $_POST['street'] : ''; ?>' /> 
                            </div>
                        </div>   
                    </div>
                    <div class ='row'> 
                        <div class ='col-md-6'>
                            <div class ='form-group'>
                                <label>Дом</label>
                                <input type ='text' name ='house'  class = 'form-control'  required  placeholder = 'Например: 1' value ='<?= (!empty($_POST['house'])) ? $_POST['house'] : ''; ?>' /> 
                            </div>
                        </div>                                              

                        <div class ='col-md-6'>
                            <div class ='form-group'>
                                <label>Квартира</label>
                                <input type ='text' name ='cc_kvart' class = 'form-control'  required  placeholder = 'Например: 7' value ='<?= (!empty($_POST['cc_kvart'])) ? $_POST['cc_kvart'] : ''; ?>' /> 
                            </div>
                        </div>
                    </div>
                    <div class ='row'> 
                        <div class ='col-md-6'>
                            <div class ='form-group'>
                                <label>День доставки</label>
                                <input type ='text' name ='cc_day' class = 'form-control'  required  placeholder = 'Например: 31.12.14' value ='<?= (!empty($_POST['cc_day'])) ? $_POST['cc_day'] : ''; ?>' /> 
                            </div>
                        </div>
                        <div class ='col-md-6'>
                            <div class ='form-group'>
                                <label>Время доставки</label>
                                <input type ='text' name ='cc_time' class = 'form-control'  required  placeholder = 'Например: 14-15' value ='<?= (!empty($_POST['cc_time'])) ? $_POST['cc_time'] : ''; ?>' /> 
                            </div>
                        </div>
                    </div>
                    <div class ="form-group">
                        <label>Комментарий</label>
                        <textarea class = 'form-control' name ='cc_comment'><?= (!empty($_POST['cc_comment'])) ? $_POST['cc_comment'] : ''; ?></textarea>
                    </div>
                    <button class="btn btn-block btn-primary"><i class="fa fa-fw fa-check"></i> Оформить заказ</button>
                </form>
            <?php } ?>

        </div><?php } ?>
</div>