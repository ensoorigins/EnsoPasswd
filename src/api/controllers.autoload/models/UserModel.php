<?php

class UserModel extends Entity
{
    protected static $table = "Users";
    protected static $view = 'UserInfo';

    protected static $columns = [
        "username",
        "password",
        "email",
        "sessionKey",
        "trustLimit",
        "ldap",
        "sysadmin"
    ];
}