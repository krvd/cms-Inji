<form class ='user_cards_serch' method ='POST' action=''><input type = 'text' name = 'search_str' /><button class = 'search_button'>Искать</button>
    <a class = 'extend_search_button' style = 'cursor:pointer;margin-left:10px;display:inline-block;border-bottom:1px dotted;font-weight:bold;' >Расширеный поиск</a>
    <?php
    if ($_POST)
        $style = '';
    else
        $style = 'style="display: none"';
    ?>
    <div class = 'extend_search' <?php echo $style; ?> >
        <table style = 'float:left;margin-left:20px;margin-top:20px;'>
            <tr>
                <td>Пол: </td><td><select name = 'user_sex'><option value = -1>Не важно</option><option value = 1>Мужской</option><option value = 2>Женский</option></select></td>
            </tr>
            <?php
            echo "<tr><td>Страна: </td><td>";
            echo "<select class = 'user_country' name = 'user_country'>";
            echo "<option value = 0 >Не важно</option>";
            foreach ($countrys as $country) {
                if (!empty($_POST['user_country']) && $_POST['user_country'] == $country['country_id'])
                    $selected = 'selected="selected"';
                else
                    $selected = '';
                echo "<option {$selected} value = '{$country['country_id']}'>{$country['country_name']}</option>";
            }
            echo "</select>";
            echo "</td></tr>";

            echo "<tr><td>Регион: </td><td>";
            echo "<select class = 'user_region' name = 'user_region'>";
            echo "<option value = 0 >Не важно</option>";
            foreach ($regions as $region) {
                if (!empty($_POST['user_region']) && $_POST['user_region'] == $region['region_id'])
                    $selected = 'selected="selected"';
                else
                    $selected = '';
                echo "<option {$selected} value = '{$region['region_id']}'>{$region['region_name']}</option>";
            }
            echo "</select>";
            echo "</td></tr>";

            echo "<tr><td>Город: </td><td>";
            echo "<select class = 'user_citys' name = 'user_city'>";
            echo "<option value = 0 >Не важно</option>";
            foreach ($citys as $city) {
                if (!empty($_POST['user_city']) && $_POST['user_city'] == $city['city_id'])
                    $selected = 'selected="selected"';
                else
                    $selected = '';
                echo "<option {$selected} value = '{$city['city_id']}'>{$city['city_name']}</option>";
            }
            echo "</select>";
            echo "</td></tr>";
            ?>
        </table>
        <table  style = 'float:left;margin-left:20px;margin-top:20px;'>
            <tr><td>Профессии</td><td><select class = 'industry_select_reg'><option value = 0>Выберите отрасль</option>
                        <?php
                        foreach ($industries as $industry)
                            echo "<option value = '{$industry['industry_id']}'>{$industry['industry_name']}</option>";
                        ?>
                    </select><br />
                    <select class = 'profession_select_reg' disabled='disabled'><option value = 0>Выберите отрасль</option></select> <button class = 'set_profession_reg' disabled='disabled'>Добавить</button></td></tr>
            <tr><td></td><td class = 'prof_list_reg'>
                    <?php
                    if (!empty($_POST['professions_list'])) {
                        foreach ($_POST['professions_list'] as $profession) {
                            $profession = explode('_', $profession);
                            echo "<span class = 'profession' pi = '{$profession[0]}_{$profession[1]}' title = '{$industries[$profession[0]]['industry_name']}'>{$professions[$profession[1]]['profession_name']} <input type = 'hidden' name = 'professions_list[]' value = '{$profession[0]}_{$profession[1]}'/> <a class = 'unset_profession_reg'>x</a></span>";
                        }
                    }
                    ?>
                </td></tr>

            <tr><td>Я ищу</td><td><select class = 'need_industry_select_reg'><option value = 0>Выберите отрасль</option>
                        <?php
                        foreach ($industries as $industry)
                            echo "<option value = '{$industry['industry_id']}'>{$industry['industry_name']}</option>";
                        ?>
                    </select><br />
                    <select class = 'need_profession_select_reg' disabled='disabled'><option value = 0>Выберите отрасль</option></select> <button class = 'set_need_profession_reg' disabled='disabled'>Добавить</button></td></tr>
            <tr><td></td><td class = 'need_prof_list_reg'>
                    <?php
                    if (!empty($_POST['need_professions_list'])) {
                        foreach ($_POST['need_professions_list'] as $profession) {
                            $profession = explode('_', $profession);
                            echo "<span class = 'profession' pi = '{$profession[0]}_{$profession[1]}' title = '{$industries[$profession[0]]['industry_name']}'>{$professions[$profession[1]]['profession_name']} <input type = 'hidden' name = 'need_professions_list[]' value = '{$profession[0]}_{$profession[1]}'/> <a class = 'unset_need_profession_reg'>x</a></span>";
                        }
                    }
                    ?>
                </td></tr>
        </table>
        <div class = 'cleaner'></div>
    </div>
</form>
<?php
echo "<div class = 'users_count'>Найдено пользователей: <b>{$users_count}</b></div>";

echo "<table class = 'user_cards'>";
foreach ($users as $user_id => $user) {
    echo "<tr><td class = 'user_card_image'><a href ='/users/profile/{$user_id}'><img src = '{$user['show_photo']}' alt ='{$user['show_name']}' /></a></td>";
    echo "<td class = 'user_card_info'><a href ='/users/profile/{$user_id}'>{$user['show_name']}</a><br />";
    if (!empty($user['user_city'])) {
        $city = $this->citysUI->get_city($user['user_city']);
        echo $city['city_name'];
    }
    echo "</td>";

    echo "<td>Профессии<br />";
    foreach ($user['user_professions'] as $profession) {
        echo "<span class = 'profession' pi = '{$profession['industry_id']}_{$profession['profession_id']}' title = '{$industries[$profession['industry_id']]['industry_name']}'>{$profession['profession_name']}</span>";
    }
    echo "</td>";
    echo "<td>В поиске<br />";
    foreach ($user['need_user_professions'] as $profession) {
        echo "<span class = 'profession' pi = '{$profession['industry_id']}_{$profession['profession_id']}' title = '{$industries[$profession['industry_id']]['industry_name']}'>{$profession['profession_name']}</span>";
    }
    echo "</td>";
    echo "<td class ='user_card_button'><button class = 'new_chat' user_id = {$user_id}>Начать диалог</button></td></tr>";
}
echo "</table>";
echo "<div class = 'materials'>";
if ($users_count > $limit) {
    $pages = ceil($users_count / $limit);
    for ($i = 1; $i <= $pages; $i++)
        echo "<a href = '/users/index/{$i}'>{$i}</a> ";
}
echo "</div>";
?>