<?php
    echo '<form action = "" method = "post" enctype="multipart/form-data">';
        echo '<table class="user_edit">';
            echo "<tr><td>Имя</td><td><input type = 'text' name = 'user_first_name' value = '{$user['user_first_name']}' /></td></tr>";
            echo "<tr><td>Фамилия</td><td><input type = 'text' name = 'user_last_name' value = '{$user['user_last_name']}' /></td></tr>";
            echo "<tr><td>Пол</td><td>";
            echo "<select name = 'user_sex'>";
            if( $user['user_sex'] == 1 ) {
                $select1 = 'selected="selected"';
                $select2 = '';
            }
            else {
                $select1 = '';
                $select2 = 'selected="selected"';
            }
            echo "<option value = 1 {$select1}>Мужской</option>";
            echo "<option value = 2 {$select2}>Женский</option>";
            echo "</select>";

            echo "<tr><td>Фото</td><td><input type = 'file' name = 'user_photo' /><img src = '{$user['show_photo']}' alt = 'фото' /></td></tr>";
            echo "<tr><td>Адрес</td><td><input type = 'text' name = 'user_addr' value = '{$user['user_addr']}' /></td></tr>";
            echo "<tr><td>Фирма</td><td><input type = 'text' name = 'user_firm' value = '{$user['user_firm']}' /></td></tr>";

            echo "<tr><td>Страна</td><td>";
            echo "<select class = 'user_country' name = 'user_country'>";
            echo "<option value = 0 >Выберите страну</option>";
            foreach( $countrys as $country ) {
                if( $user['user_country'] == $country['country_id'] )
                    $selected = 'selected="selected"';
                else
                    $selected = '';
                echo "<option {$selected} value = '{$country['country_id']}'>{$country['country_name']}</option>";
            }
            echo "</select>";
            echo "</td></tr>";

            echo "<tr><td>Регион</td><td>";
            echo "<select class = 'user_region' name = 'user_region'>";
            echo "<option value = 0 >Выберите регион</option>";
            foreach( $regions as $region ) {
                if( $user['user_region'] == $region['region_id'] )
                    $selected = 'selected="selected"';
                else
                    $selected = '';
                echo "<option {$selected} value = '{$region['region_id']}'>{$region['region_name']}</option>";
            }
            echo "</select>";
            echo "</td></tr>";

            echo "<tr><td>Город</td><td>";
            echo "<select class = 'user_citys' name = 'user_city'>";
            echo "<option value = 0 >Выберите город</option>";
            foreach( $citys as $city ) {
                if( $user['user_city'] == $city['city_id'] )
                    $selected = 'selected="selected"';
                else
                    $selected = '';
                echo "<option {$selected} value = '{$city['city_id']}'>{$city['city_name']}</option>";
            }
            echo "</select>";
            echo "</td></tr>";

            echo "<tr><td>Профессии</td><td><select class = 'industry_select'><option value = 0>Выберите отрасль</option>";
            foreach( $industries as $industry )
                echo "<option value = '{$industry['industry_id']}'>{$industry['industry_name']}</option>";
            echo "<option value = -1>Добавить отрасль</option>";
            echo "</select><br />
                   <select class = 'profession_select' disabled='disabled'><option value = 0>Выберите отрасль</option>";

            echo "</select> <button class = 'set_profession' disabled='disabled'>Добавить</button></td></tr>";
            echo "<tr><td></td><td class = 'prof_list'>";
            foreach( $user_professions as $profession ) {
                echo "<span class = 'profession' pi = '{$profession['industry_id']}_{$profession['profession_id']}' title = '{$industries[$profession['industry_id']]['industry_name']}'>{$profession['profession_name']} <a class = 'unset_profession'>x</a></span>";
            }
            echo "</td></tr>";

            echo "<tr><td>Я ищу</td><td><select class = 'need_industry_select'><option value = 0>Выберите отрасль</option>";
            foreach( $industries as $industry )
                echo "<option value = '{$industry['industry_id']}'>{$industry['industry_name']}</option>";
            echo "<option value = -1>Добавить отрасль</option>";
            echo "</select><br />
                   <select class = 'need_profession_select' disabled='disabled'><option value = 0>Выберите отрасль</option>";

            echo "</select> <button class = 'set_need_profession' disabled='disabled'>Добавить</button></td></tr>";
            echo "<tr><td></td><td class = 'need_prof_list'>";
            foreach( $need_user_professions as $profession ) {
                echo "<span class = 'profession' pi = '{$profession['industry_id']}_{$profession['profession_id']}' title = '{$industries[$profession['industry_id']]['industry_name']}'>{$profession['profession_name']} <a class = 'unset_need_profession'>x</a></span>";
            }
            echo "</td></tr>";

            echo "<tr><td>О себе</td><td><textarea name = 'user_about' >".str_replace("<br />","\n", $user['user_about'])."</textarea></td></tr>";
            echo '<tr><td class="user_edit_button_save"  colspan = 2><input type = "submit" value = "Сохранить"/></td></tr>';
        echo '</table>';
    echo "<input type = 'hidden' name = 'action' value = 'edit_profile' />";
    echo '</form>';
?>
