<?php

return [
    'name' => 'Последняя покупка не позднее 90 дней',
    'checker' => function($user) {
        $query = 'SELECT * FROM inji_ecommerce_cart iec WHERE iec.cart_cart_status_id >= 5 AND iec.cart_date_create >= NOW() - INTERVAL 90 DAY AND cart_user_id = ?';
        return (bool) App::$cur->db->query(['query' => $query, 'params' => [$user->id]])->fetch();
    }];
        