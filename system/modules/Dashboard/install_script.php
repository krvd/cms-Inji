<?php

return function ($step = NULL, $params = []) {
    App::$cur->db->createTable('dashboard_comment', [
        'comment_id' => 'pk',
        'comment_text' => 'text NOT NULL',
        'comment_user_id' => 'int(11) UNSIGNED  NOT NULL',
        'comment_model' => 'varchar(255) NOT NULL',
        'comment_item_id' => 'int(11) UNSIGNED NOT NULL',
        'comment_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
};
