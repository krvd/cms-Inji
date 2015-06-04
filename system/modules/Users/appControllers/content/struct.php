<?php $this->widget('cabinet_tabs', 'tree'); ?>
<?php
$this->db->where('cmuc_user_id', Users\User::$cur->user_id);
$this->db->where('cmuc_status', 1);
$card = $this->db->select('catalog_marketing_user_cards')->fetch_assoc();
if ($card) {
    ?>
    <pre>Ваша реферальная ссылка: <a href = 'http://<?= $card['cmuc_code']; ?>.<?= INJI_DOMAIN_NAME; ?>'>http://<?= $card['cmuc_code']; ?>.<?= INJI_DOMAIN_NAME; ?></a></pre>
    <?php
}
?>

<h3>Пригласить человека от своего имени</h3>
<form method = 'POST'>
    <div class ='row'>
        <div class ='col-sm-6'>
            <div class ='form-group'>
                <label>E-mail</label>
                <input type ='text' class ='form-control' name ='user_invite' placeholder="mail@mail.ru" />
            </div>
        </div>
        <div class ='col-sm-6'>
            <label>&nbsp;</label>
            <button class ="btn btn-block btn-success">Пригласить</button>
        </div>
    </div>
</form>

<?php
if (Users\User::$cur->parent) {
    if (Users\User::$cur->parent->user_photo) {
        $file = $this->files->get(Users\User::$cur->parent->user_photo);
        $photo = $file['file_path'];
    } else {
        $photo = '/static/images/no-image.png';
    }
    ?>
    <h2>Ваш консультант</h2>
    <div class ='row'>
        <div class ='col-xs-4'>
            <img src ='<?= $photo; ?>?resize=200x200&resize_crop=q' class = 'img-responsive'/>
        </div>
        <div class ='col-xs-8'>
            <h3><?= Users\User::$cur->parent->user_name; ?></h3>
            <p>E-mail: <?= Users\User::$cur->parent->user_mail; ?></p>
            <p>Город: <?= Users\User::$cur->parent->user_city; ?></p>
            <p>Телефон: <?= Users\User::$cur->parent->user_phone; ?></p>
        </div>
    </div>
    <?php
}
?>
<h2>Ваша структура</h2>
<h4>Приглашенных в 8 поколениях: <b><?= $count; ?></b></h4>
<div class ='row'>
    <div class ='col-sm-6'>
        <div class ='form-group'>
            <input autocomplete="off" id ='searchInput2' type="text" class ='form-control' placeholder="Поиск" />
        </div>
        <ul class="nav nav-list-main userList">
            <?php
            foreach ($levels[1] as $user) {
                showLevel($levels, 1, $user);
            }
            ?>
        </ul>
    </div>
    <div class ='col-sm-6 userPanel hidden'>
        <div class ='row'>
            <div class ='col-xs-4'>
                <img src ='<?= $photo; ?>?resize=200x200&resize_crop=q' class = 'img-responsive'/>
            </div>
            <div class ='col-xs-8'>
                <h3><?= Users\User::$cur->parent->user_name; ?></h3>
                <p>E-mail: <span class = 'mail'><?= Users\User::$cur->parent->user_mail; ?></span></p>
                <p>Город: <span class = 'city'><?= Users\User::$cur->parent->user_city; ?></span></p>
                <p>Телефон: <span class = 'tel'><?= Users\User::$cur->parent->user_phone; ?></span></p>
                <h4>Использовано</h4>
                Выгодные рубли: <b class = 'used'></b>
                <h4>Доступно</h4>
                Выгодные рубли: <b class = 'BP'></b><br />
                Условные единицы: <b class = 'YE'></b>
            </div>
        </div>
    </div>
</div>
<?php
$usersSearch = [];
foreach ($levels as $level) {
    foreach ($level as $user) {
        $usersSearch[] = [
            'name' => (($user['cmuc_code']) ? $user['cmuc_code'] : '') . " {$user['user_name']} #{$user['user_id']}",
            'translit' => $this->tools->translit((($user['cmuc_code']) ? $user['cmuc_code'] : '') . " {$user['user_name']}"),
        ];
    }
}
if ($usersSearch) {
    ?>
    <script>
        $(function () {
            $('.userList a').click(function () {

                id = $(this).attr('href');
                $.getJSON('/users/struct/getInfo/' + id, function (data) {
                    $('.userPanel h3').text(data.user_name);
                    $('.userPanel img').attr('src', data.photo + '?resize=200x200&resize_crop=q');
                    $('.userPanel .mail').text(data.user_mail);
                    $('.userPanel .city').text(data.user_city);
                    $('.userPanel .tel').text(data.user_phone);
                    $('.userPanel .used').text(data.used);
                    $('.userPanel .BP').text(data.BP);
                    $('.userPanel .YE').text(data.YE);
                    $('.userPanel').removeClass('hidden');
                });
                return false;
            })
            var $input2 = $('#searchInput2');
            $input2.typeahead({
                source: <?= json_encode($usersSearch); ?>,
                autoSelect: true,
                matcher: function (item) {
                    search = item.name.substr(0, item.name.lastIndexOf('#'));
                    if (search.toLowerCase().indexOf(this.query) !== -1 || item.translit.toLowerCase().indexOf(this.query) !== -1) {
                        return true
                    }
                },
                updater: function (item) {
                    return item.name;
                },
                displayText: function (item) {
                    return item.name.substr(0, item.name.lastIndexOf('#'));
                },
                afterSelect: function (item) {
                    $input2.val('');
                    $('.userList a').css('fontWeight', 400)
                    $('.userList ul[style="display: block;"]').toggle();
                    userid = item.substr(item.lastIndexOf('#') + 1);
                    $('#user' + userid + ' a').css('fontWeight', 'bold');
                    parent = $('#user' + userid).parent('ul');
                    while (parent.length > 0) {
                        if (parent.css('display') == 'none') {
                            parent.toggle();
                        }
                        parent = $(parent).parent().parent('ul');
                        console.log(parent);
                    }
                }

            });
        });


    </script>
    <?php
}

function showLevel($levels, $i, $user)
{

    $isset = false;
    if (isset($levels[$i + 1]))
        foreach ($levels[$i + 1] as $userChild) {
            if ($userChild['user_parent_id'] == $user['user_id']) {
                if (!$isset) {
                    $isset = true;

                    echo "<li id = 'user{$user['user_id']}'>
                            <label class='nav-toggle nav-header'>
                            <span class='nav-toggle-icon glyphicon glyphicon-chevron-right'></span> 
                            <a type = 'button' href='{$user['user_id']}'> " . showIcon($user) . " " . (($user['cmuc_code']) ? $user['cmuc_code'] : '') . " {$user['user_name']}</a></label>
                            <ul class='nav nav-list nav-left-ml'>";
                }
                showLevel($levels, $i + 1, $userChild);
            }
        }

    if ($isset) {
        echo '</ul>
                    </li>';
    } else {
        echo '<li id = "user' . $user['user_id'] . '">
            <label class="nav-header">
            <span  class=" nav-toggle-icon fa fa-minus"></span>&nbsp;
            <a type = "button" href="' . $user['user_id'] . '"> ' . showIcon($user) . ' ' . (($user['cmuc_code']) ? $user['cmuc_code'] : '') . ' ' . $user['user_name'] . '</a></label></li>';
    }
}

function showIcon($user)
{
    $roleicons = [
        2 => '<span class = "fa fa-circle-thin"></span>',
        3 => '<span class = "fa fa-circle" style = "color:#000;"></span>',
        4 => '<span class = "fa fa-circle" style = "color:#5775B7;"></span>',
        5 => '<span class = "fa fa-circle" style = "color:#E97347;"></span>',
    ];
    return $roleicons[$user['user_role_id']];
}
