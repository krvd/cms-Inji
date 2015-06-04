<h3>Мой профиль</h3>
<?php

$form->draw('profile');
/*
?>
<form action = '' method = 'POST' enctype="multipart/form-data">
    <div class ='row'>
        <div class="col-sm-6">
            <div class ='form-group'>
                <label>ФИО</label>
                <input type ='text' name ='user_name' class ='form-control' placeholder ='Например: Иван' value ="<?= Users\User::$cur->user_name; ?>" required />
            </div>
            <div class ='form-group'>
                <label>Дата рождения</label>
                <input type ='text' name ='user_birthday' class ='form-control datepicker' placeholder ='Например: 1985-02-07' value ="<?= Users\User::$cur->user_birthday; ?>" required />
            </div>
            <div class ='form-group'>
                <label>Город</label>
                <input type ='text' name ='user_city' class ='form-control' placeholder ='Например: Санкт-Петербург' value ="<?= Users\User::$cur->user_city; ?>" required />
            </div>
            <div class ='form-group'>
                <label>Электронная почта</label>
                <input type ='email' name ='user_mail' class ='form-control' placeholder ='Например: mail@mail.ru' value ="<?= Users\User::$cur->user_mail; ?>" disabled />
            </div>
            <div class ='form-group'>
                <label>Телефон</label>
                <input type ='text' name ='user_phone' class ='form-control' placeholder ='Например: +79876543210' value ="<?= Users\User::$cur->user_phone; ?>" />
            </div>
        </div>
        <div class="col-sm-6">
            <div class ='form-group'>
                <label>Загрузите ваше фото</label>
                <img src ='<?= Users\User::$cur->photo ? Users\User::$cur->photo->path : '/static/system/images/no-image.png'; ?>?resize=200x200&resize_crop=q'  class ='img-responsive'/>
                <input type ='file' name ='user_photo'/>
            </div>

        </div>
    </div>
    <h3>Социальные сети</h3>
    <div class ='row'>
        <div class ='col-sm-6'>
            <div class ='form-group'>
                <label>Вконтакте</label>
                <input type ='text' name ='user_vk_href' class ='form-control' placeholder ='Например: vk.com/id1000' value ="<?= Users\User::$cur->user_vk_href; ?>" />
            </div>
        </div>
        <div class ='col-sm-6'>
            <div class ='form-group'>
                <label>Однокласники</label>
                <input type ='text' name ='user_ok_href' class ='form-control' placeholder ='Например: ok.ru/profile/1000' value ="<?= Users\User::$cur->user_ok_href; ?>" />
            </div>
        </div>
    </div>
    <h3>Расскажите о себе</h3>
    <div class ='row'>
        <div class ='col-sm-12'>
            <textarea name = 'user_about' class = 'form-control' rows='5'><?= Users\User::$cur->user_about; ?></textarea>
        </div>
    </div>
    <h3>Смена пароля</h3>
    <div class ='row'>
        <div class ='col-sm-6'>
            <div class ='form-group'>
                <label>Новый пароль</label>
                <input type ='password' class ='form-control' name ='user_pass[0]' />
            </div>
        </div>
        <div class ='col-sm-6'>
            <div class ='form-group'>
                <label>Введите ещё раз</label>
                <input type ='password' class ='form-control' name ='user_pass[1]' />
            </div>
        </div>
    </div>
    <div class="form-actions text-center">
        <button class ="btn btn-success" >Изменить</button>
    </div>
</form>
 * 
 */