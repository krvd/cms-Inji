<?php

class UserInvite extends Model
{

    static function table()
    {
        return 'user_invites';
    }

    static function index()
    {
        return 'ui_id';
    }

    static function relations()
    {
        return [
            'user' => [
                'model' => 'User',
                'col' => 'ui_user_id'
            ]
        ];
    }

}
