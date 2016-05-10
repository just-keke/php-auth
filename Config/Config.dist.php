<?php

namespace Config;

use OLOG\Auth\Constants;

class Config
{
    const DB_NAME_PHPAUTHDEMO = 'db_phpauthdemo';

    public static function get()
    {
        $conf['cache_lifetime'] = 60;
        $conf['return_false_if_no_route'] = true; // for local php server
        $conf['db'] = array(
            self::DB_NAME_PHPAUTHDEMO => array(
                'host' => 'localhost',
                'db_name' => 'db_phpauthdemo',
                'user' => 'root',
                'pass' => '1'
            ),
            Constants::DB_NAME_PHPAUTH => array(
                'host' => 'localhost',
                'db_name' => 'db_phpauth',
                'user' => 'root',
                'pass' => '1'
            )
        );

        return $conf;
    }
}