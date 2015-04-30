<div class="tab-content">
    <div id="login" class="tab-pane active">
        <form action="" method = 'POST'>
            <p class="text-muted text-center">
                Введите свой логин и пароль
            </p>
            <input type="text" placeholder="Логин" class="form-control top" name = 'user_login' />
            <input type="password" placeholder="Пароль" class="form-control bottom" name = 'user_pass' />
            <input type="hidden"  name = 'autorization' value = '1'>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
        </form>
    </div>
    <div id="forgot" class="tab-pane">
        <form action="" method = 'GET'>
            <p class="text-muted text-center">Введите ваш e-mail</p>
            <input type="email" placeholder="mail@domain.com" class="form-control" name = 'user_mail' />
            <br>
            <input type="hidden"  name = 'passre' value = '1'>
            <button class="btn btn-lg btn-danger btn-block" type="submit">Восстановить пароль</button>
        </form>
    </div>
</div>
<hr>
<div class="text-center">
    <ul class="list-inline">
        <li> <a class="text-muted" href="#login" data-toggle="tab">Вход</a>  </li>
        <li> <a class="text-muted" href="#forgot" data-toggle="tab">Восстановление пароля</a>  </li>
    </ul>
</div>
