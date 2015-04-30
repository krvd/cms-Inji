<div class = 'content-box'>
    <h1 class = 'content-head'>Базы данных</h1>
    <div class = 'content-body'>
        <div class = 'content-body-container'>
<?php
if( !empty( $configs['databases'] ) )
    foreach( $configs['databases'] as $db )
        echo $db;
else
    echo 'нет настроенных баз';

    //var_dump( $this->db->result_array( $this->db->select('test') ) );
?>
        </div>
    </div>
</div>