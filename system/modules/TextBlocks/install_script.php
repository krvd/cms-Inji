<?php

return function ($step = NULL, $params = []) {
    App::$cur->db->createTable('textblocks_block', [
        'block_id' => 'pk',
        'block_code' => 'varchar(255) NOT NULL',
        'block_name' => 'varchar(255) NOT NULL',
        'block_text' => 'text NOT NULL',
        'block_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
};
