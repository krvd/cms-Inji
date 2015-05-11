<?php
switch ($step) {
    case 1:
        ?>
        <form action = '' method = 'POST' style ='display:block;width:300px;margin:0 auto;'>
            <div class ='form-group'>       
                <label>Введите свой E-mail</label>
                <input type = 'text' name = 'user_mail' class = 'form-control' />
            </div>
            <div class ='form-group'>       
                <button class ='btn btn-primary'>Восстановить</button>
            </div>
        </form>
        <?php
        break;
    case 3:
        ?>
        <form action = '' method = 'POST' style ='display:block;width:300px;margin:0 auto;'>
            <div class ='form-group'>       
                <label>Введите новый пароль</label>
                <input type = 'password' name = 'user_pass[]' class = 'form-control' />            
            </div>
            <div class ='form-group'> 
                <label>Повторите ввод</label>
                <input type = 'password' name = 'user_pass[]' class = 'form-control' />
            </div>
            <button class ='btn btn-primary'>Изменить</button>
        </form>
        <?php
        break;
}
?>