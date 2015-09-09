<form method = 'POST'>
    <div class="row">
        <div class="col-sm-4">
            <?php if (!Users\User::$cur->id) { ?>
                <fieldset id="account">
                    <h4>Ваш аккаунт</h4>
                    <div class="form-group required">
                        <label class="control-label">E-Mail</label>
                        <input required type="text" name="user_mail" value="<?= (!empty($_POST['user_mail'])) ? $_POST['user_mail'] : (($cart->email) ? $cart->email : ''); ?>" placeholder="E-Mail" class="form-control"/>
                    </div>
                </fieldset>
            <?php } ?>
            <fieldset id="address">
                <h4>Контактная информация</h4>
                <?php
                $form = new Ui\Form;
                foreach (Ecommerce\UserAdds\Field::getList() as $field) {
                    ?>
                    <div class="form-group required">
                        <?php
                        $form->input($field->type, "userAdds[fields][{$field->id}]", $field->name, ['noContainer' => true, 'required' => $field->required]);
                        ?>
                    </div>
                    <?php
                }
                ?>
            </fieldset>                                
        </div>

        <div class="col-sm-8">                        
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-left" colspan="2">Карта</th>
                            <th class="text-left">Уровни</th>
                            <th class="text-left">Стоимость</th>
                        </tr>
                    </thead>
                    <tbody class="cartitems">
                        <?php
                        $first = true;
                        foreach (\Ecommerce\Card::getList() as $card) {
                            $checked = $first;
                            $first = false;
                            ?>
                            <tr>
                                <td>
                                    <input type="radio" name ="card_id" value ="<?=$card->id;?>" <?= $checked ? 'checked' : ''; ?> />
                                </td>
                                <td>
                                    <h4><?= $card->name; ?></h4>
                                    <img class="img-responsive" src="<?= $card->image->path; ?>?resize=200x200" />
                                </td>
                                <td>
                                    <ul>
                                        <?php
                                        foreach ($card->levels as $level) {
                                            ?>
                                            <li><?= $level->name; ?> - <?= $level->discount->name; ?></li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </td>
                                <td>
                                    <?= $card->price; ?>&nbsp;руб.
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="checkout-content confirm-section">
                <h3>Вы можете добавить комментарий</h3>
                <div class="form-group">    
                    <textarea name="comment" rows="5" class="form-control"><?= (!empty($_POST['comment'])) ? $_POST['comment'] : ''; ?></textarea>
                </div>
                <div class="confirm-order">
                    <button data-loading-text="Подождите.." class="btn btn-primary">Подтверждаю покупку карты</button>
                </div>
            </div>
        </div>

    </div>
</form>