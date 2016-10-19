<?php

namespace OLOG\Auth\Admin;

use OLOG\Auth\AuthConfig;
use OLOG\CheckClassInterfaces;
use OLOG\Layouts\InterfaceMenu;

class AuthAdminActionsBaseProxy implements InterfaceMenu
{
    static public function menuArr(){
        $admin_actions_base_classname = AuthConfig::getAdminActionsBaseClassname();
        if (CheckClassInterfaces::classImplementsInterface($admin_actions_base_classname, InterfaceMenu::class)){
            return $admin_actions_base_classname::menuArr();
        }

        return [];
    }
}