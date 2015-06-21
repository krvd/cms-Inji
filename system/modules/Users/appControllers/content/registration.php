<div class='row'>
    <div class = 'box col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1'>
        <h3>Регистрация</h3>
        <form action = '' method = 'POST' enctype="multipart/form-data" >
            <div class ='row'>
                <div class="col-sm-6">
                    <div class ='form-group'>
                        <label>ФИО</label>
                        <input type ='text' name ='user_name' class ='form-control' placeholder ='Например: Иван' value ="<?= (isset($_POST['user_name']) ? $_POST['user_name'] : ''); ?>" required />
                    </div>
                    <div class ='form-group'>
                        <label>Дата рождения</label>
                        <input type ='text' name ='user_birthday' class ='form-control datepicker' placeholder ='Например: 1985-02-07' value ="<?= (isset($_POST['user_birthday']) ? $_POST['user_birthday'] : ''); ?>" required />
                    </div>
                    <div class ='form-group'>
                        <label>Город</label>
                        <input type ='text' name ='user_city' class ='form-control' placeholder ='Например: Санкт-Петербург' value ="<?= (isset($_POST['user_city']) ? $_POST['user_city'] : ''); ?>"  required />
                    </div>
                    <div class ='form-group'>
                        <label>Электронная почта</label>
                        <input type ='email' name ='user_mail' class ='form-control' placeholder ='Например: mail@mail.ru' value ="<?= (isset($_POST['user_mail']) ? $_POST['user_mail'] : ''); ?>" required />
                    </div>
                    <?php
                    if (!empty($this->users->modConf['sponsors'])) {
                        ?>
                        <div class ='form-group'>
                            <label>ID спонсора</label>
                            <input type ='number' name ='user_parent_id' class ='form-control' placeholder ='Например: 256' value ="<?= (isset($_POST['user_parent_id']) ? $_POST['user_parent_id'] : ((!empty($_COOKIE['partnerId']) ? $_COOKIE['partnerId'] : ''))); ?>" required />
                        </div>               
                        <?php
                    }
                    ?>
                </div>
                <div class="col-sm-6">
                    <div class ='form-group'>
                        <label>Загрузите ваше фото</label>
                        <img src ='/static/system/images/no-image.png' class ='img-responsive' />
                        <input type ='file' name ='user_photo'/>
                    </div>
                </div>

            </div>
            <div class ='form-group'>
                <?php App::$cur->Recaptcha->show(); ?>
            </div>
            <!--<div class="checkbox">
                <label>
                    <input type="checkbox" name = 'accept_license' required> Я принимаю <a href = '#userLicense' type="button" data-toggle="modal" data-target="#userLicense">Пользовательское соглашение</a>
                </label>
            </div>-->
            <div class="form-actions text-center">
                <button class ="btn btn-success" >Зарегистрироваться</button>
            </div>
        </form>
    </div>

</div>

<!-- userLicense -->
<div class="modal fade" id="userLicense" tabindex="-1" role="dialog" aria-labelledby="Пользовательское соглашение" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Пользовательское соглашение</h4>
            </div>
            <div class="modal-body">
                <p>На основании Федерального Закона Российской Федерации от 27 июля 2006 г N 152-ФЗ &laquo;О персональных данных&raquo; я подтверждаю, что указанные мною при регистрации персональные данные, а именно: Ф.И.О., контактный телефон, e-mail, адрес моего нахождения (для доставки заказа), а также иные сведения, которые я сообщу Обществу с ограниченной ответственностью &laquo;РОДНИК&raquo; (интернет-магазин &laquo;РОДНИК&raquo;) (далее - Общество), юридический/фактический адрес: 660135, г. Красноярск, ул. Молокова, д. 66, пом.344, сообщены мною по своей воле и в своем интересе путем заполнения электронной анкеты на сайте <a href="http://www.<?= INJI_DOMAIN_NAME; ?>">www.<?= INJI_DOMAIN_NAME; ?></a> в целях получения от Общества услуг и приобретения товаров в интернет-магазине &laquo;РОДНИК&raquo;, создания информационной системы (хранения как на материальном носителе, так и на электронном), для обработки таких данных любым не запрещенным законом способом (включая сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (распространение, предоставление, доступ), обезличивание, блокирование, удаление, уничтожение), в том числе, для обеспечения проведения маркетинговых акций, продвижения товаров, работ и услуг путем осуществления со мной прямых контактов по различным средствам связи с использованием средств автоматизации и без таковых.</p>

                <p>Настоящим я признаю и подтверждаю, что в случае необходимости предоставления Персональных данных для достижения указанных выше целей третьему лицу (в том числе Партнерам Общества), а равно как при привлечении третьих лиц к оказанию услуг в указанных целях, передачи Обществом принадлежащих ему функций и полномочий иному лицу, Общество вправе в необходимом объеме раскрывать для совершения вышеуказанных действий информацию обо мне лично (включая мои Персональные данные) таким третьим лицам, их агентам и иным уполномоченным ими лицам, а также предоставлять таким лицам соответствующие документы, содержащие такую информацию. Также настоящим признаю и подтверждаю, что настоящее согласие считается данным мною любым третьим лицам, указанным выше, с учетом соответствующих изменений, и любые такие третьи лица имеют право на обработку Персональных данных на основании настоящего согласия.</p>

                <p><strong>Настоящим в соответствии с п. 1. ст. 18 Федерального закона Российской Федерации от 13 марта 2006 г. N 38-Ф3 &ldquo;О рекламе&rdquo; я безусловно соглашаюсь на отправку на мой телефон и адрес электронной почты сообщений о статусе заказа, новостях и акциях Общества.</strong></p>

                <p>Срок, в течение которого действует настоящее Согласие, составляет 1 (один) год и длится до моего отзыва Согласия, который я должен(на) буду предоставить в письменном виде под роспись о получении уполномоченному представителю Общества, по адресу: 660135, г. Красноярск, ул.Молокова, д. 66, пом.344 с требованием о прекращении обработки указанных персональных данных (кроме данных, подлежащих безусловному хранению в соответствии с законодательством РФ), которое должно быть исполнено не позднее 60 календарных дней с даты вручения отзыва Согласия. В случае если в течение указанного срока действия Согласия от меня не поступит отзыва Согласия, то данное согласие автоматически продлевается еще на один год. Количество продлений не ограниченно.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>