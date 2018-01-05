<?php

/*
 * Args mandatórios
 *  
 *  'authusername'  - username para autenticar a request
 *  'sessionkey' - sessionkey para autenticar a request
 * 
 * Errors
 * 
 *  1 - Email inválido
 *  2 - LDAP inválido
 *  3 - Sysadmin inválido
 *  4 - O campo de username é obrigatório
 *  5 - Este username já existe
 *  6 - O campo de password é obrigatório
 */

class Users {

    public static function getMatching() {

        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'listUsers')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        $string = '%' . $req->get("search") . '%';

        /* 4. executar operações */

        $listaDeUsers = UserModel::getUsersMatching($string);

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $listaDeUsers);
    }

    public static function getInfoByUsername() {

        $req = ensoGetRequest();

        $username = $req->get("username");
        $authusername = $req->get("authusername");
        $key = $req->get('sessionkey');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'listUsers')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        /* 4. executar operações */

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, UserModel::getUserInfo($username));
    }

    public static function getByUsername() {

        $req = ensoGetRequest();

        $username = $req->get("username");

        $authusername = $req->get("authusername");
        $key = $req->get('sessionkey');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'listUsers')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        /* 4. executar operações */

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, UserModel::getUser($username));
    }

    public static function editUser() {

        $req = ensoGetRequest();

        $username = $req->put("username");
        $authusername = $req->put("authusername");
        $key = $req->put('sessionkey');
        $email = $req->put("email");
        $ldap = $req->put("ldap");
        $sysadmin = $req->put("sysadmin");
        $password = $req->put("password");

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageUsers') && $username != $authusername) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);

        if (filter_var($ldap, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === NULL)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);

        if (!filter_var($sysadmin, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === NULL)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);

        /* 4. executar operações */

        $roles = EnsoRBACModel::getUserRoles($username);

        if (!UserModel::editUser($username, $email, $ldap, $password)) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit user '$username', operation failed.", EnsoLogsModel::$ERROR, 'User');
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "erro nos utilziadores");
        } else {

            if ($sysadmin && !in_array("SysAdmin", $roles)) { //é para marcar como sysadmin e ainda não está
                $return = EnsoRBACModel::addRoleToUser($username, "SysAdmin", time());

                if (!$return) {
                    EnsoLogsModel::addEnsoLog($authusername, "Tried to edit user '$username', operation failed because the role could not be changed.", EnsoLogsModel::$ERROR, 'User');
                    return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel adicionar role");
                }
            } else

            if (!$sysadmin && in_array("SysAdmin", $roles)) {
                $return = EnsoRBACModel::removeRoleFromUser($username, "SysAdmin");

                if (!$return) {
                    EnsoLogsModel::addEnsoLog($authusername, "Tried to edit user '$username', operation failed because the role could not be changed.", EnsoLogsModel::$ERROR, 'User');
                    return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel remover role");
                }
            }
        }

        EnsoLogsModel::addEnsoLog($authusername, "Edited user '$username'.", EnsoLogsModel::$INFORMATIONAL, 'User');

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "ok");
    }

    public static function addUser() {

        $req = ensoGetRequest();

        $username = $req->post("username");
        $authusername = $req->post("authusername");
        $key = $req->post('sessionkey');
        $email = $req->post("email");
        $ldap = $req->post("ldap");
        $sysadmin = $req->post("sysadmin");
        $password = $req->post("password");

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageUsers')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);

        if (!filter_var($ldap, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === NULL)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);

        if (!filter_var($sysadmin, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === NULL)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);

        if ($username === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 4);

        if (UserModel::userExists($username))
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 5);

        if ($password === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 6);

        /* 4. executar operações */

        if (!UserModel::addUser($username, $email, $ldap, $password)) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel criar");
        } else {

            if ($sysadmin) {
                $return = EnsoRBACModel::addRoleToUser($username, "SysAdmin", time());

                if ($return === false) {
                    EnsoLogsModel::addEnsoLog($authusername, "Tried to add user '$username', operation failed because the role could not be added.", EnsoLogsModel::$ERROR, 'User');
                    return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel adicionar a role");
                }
            }

            $return = EnsoRBACModel::addRoleToUser($username, "NormalUser", time());

            if (!$return) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to add user '$username', operation failed because the role could not be added.", EnsoLogsModel::$ERROR, 'User');
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel adicionar a role");
            }
        }

        EnsoLogsModel::addEnsoLog($authusername, "Added user '$username'.", EnsoLogsModel::$INFORMATIONAL, 'User');

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "ok");
    }

    public static function removeUser() {

        $req = ensoGetRequest();

        $username = $req->delete("username");
        $authusername = $req->delete("authusername");
        $key = $req->delete('sessionkey');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageUsers')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        /* 4. executar operações */


        //Remover mensagens e credenciais

        $msgsIn = MessageModel::getMessagesReceivedBy($username);
        $msgsOut = MessageModel::getMessagesSentBy($username);
        $extMsgsOut = MessageModel::getExternalMessagesSentBy($username);

        if (MessageModel::removeMessagesWithInteraction($username) === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove user '$username', operation failed because it was impossible to delete messages.", EnsoLogsModel::$ERROR, 'User');
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel remover mensagens");
        }

        foreach ($msgsIn as $value) {
            if ($value['belongsToFolder'] == NULL)
                CredentialModel::removeCredential($value['referencedCredential']);
        }

        foreach ($msgsOut as $value) {
            if ($value['belongsToFolder'] == NULL)
                CredentialModel::removeCredential($value['referencedCredential']);
        }

        foreach ($extMsgsOut as $value) {
            if ($value['belongsToFolder'] == NULL)
                CredentialModel::removeCredential($value['referencedCredential']);
        }



        //Remover user

        PermissionModel::cleanPermissionsFromUser($username);

        if (!UserModel::removeUser($username)) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove user '$username', operation failed.", EnsoLogsModel::$ERROR, 'User');
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel remover");
        } else {

            if (!EnsoRBACModel::removeAllUserRoles($username)) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to remove user '$username', operation failed because the roles could not be removed.", EnsoLogsModel::$ERROR, 'User');
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel remover roles");
            }
        }

        EnsoLogsModel::addEnsoLog($authusername, "Removed user '$username'.", EnsoLogsModel::$INFORMATIONAL, 'User');

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "ok");
    }

}

$app->get('/users/search/', 'Users::getMatching');
$app->get('/users/', 'Users::getInfoByUsername');
$app->put('/users/', 'Users::editUser');
$app->post('/users/', 'Users::addUser');
$app->delete('/users/', 'Users::removeUser');
