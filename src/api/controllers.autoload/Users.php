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

class Users
{
    public static function getMatching($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
            $search = Input::validate($request->getParam("search"), Input::$STRING);

            /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            /* 2. autorização - validação de permissões */

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'listUsers'))
                throw new RBACDeniedException();

            /* 3. validação de inputs */

            $string = '%' . $search . '%';

            /* 4. executar operações */

            $listaDeUsers = UserModel::getWhere(
                [
                    'username' => ["LIKE", $string]
                ],
                [
                    "username",
                    "email",
                    "sysadmin"
                ]
            );

            //TODO: May be missing some attributes returned due to not consulting view

            /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $listaDeUsers);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed.", EnsoLogsModel::$ERROR, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getInfoByUsername($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
            $username = Input::validate($request->getParam("username"), Input::$STRING, 0, UserModel::class, 'username');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'listUsers'))
                throw new RBACDeniedException();

            //TODO: May be missing some attributes returned due to not consulting view

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, UserModel::getWhere(['username' => $username], ["username", "email", "password", "ldap", "sysadmin"])[0]);
        } catch (EntityCheckFailureException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_FOUND, $e->getCode());
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get user $username, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get user $username, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get user $username, operation failed.", EnsoLogsModel::$ERROR, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function editUser($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
            $username = Input::validate($request->getParam('username'), Input::$STRING, 0, UserModel::class, 'username');
            $email = Input::validate($request->getParam("email"), Input::$EMAIL, 1);
            $ldap = (int)Input::validate($request->getParam("ldap"), Input::$BOOLEAN, 2);
            $sysadmin = (int)Input::validate($request->getParam("sysadmin"), Input::$BOOLEAN, 3);

            $password = $request->getParam("password");

            /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            /* 2. autorização - validação de permissões */

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageUsers') && $username != $authusername)
                throw new RBACDeniedException();

            /* 3. validação de inputs */

            /* 4. executar operações */


            $newAttrs = ["email" => $email];

            if (EnsoRBACModel::checkUserHasAction($authusername, 'manageUsers')) //a sysadmin is editing
                $newAttrs["ldap"] = $ldap;

            if (!empty($password))
                $newAttrs['password'] = EnsoShared::hash($password);

            $roles = EnsoRBACModel::getUserRoles($username);

            UserModel::editWhere(
                [
                    'username' => $username
                ],
                $newAttrs
            );

            if ($request->getParam("sysadmin") !== null) { //Why can't I trust input to not recognize null as null and not false
                if ($sysadmin && !in_array("SysAdmin", $roles)) { //é para marcar como sysadmin e ainda não está
                    $return = EnsoRBACModel::addRoleToUser($username, "SysAdmin", time());

                    if (!$return) {
                        EnsoLogsModel::addEnsoLog($authusername, "Tried to edit user '$username', operation failed because the role could not be changed.", EnsoLogsModel::$ERROR, 'User');
                        return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel adicionar role");
                    }
                } else
                if (!$sysadmin && in_array("SysAdmin", $roles)) {
                    $return = EnsoRBACModel::removeRoleFromUser($username, "SysAdmin");

                    if (!$return) {
                        EnsoLogsModel::addEnsoLog($authusername, "Tried to edit user '$username', operation failed because the role could not be changed.", EnsoLogsModel::$ERROR, 'User');
                        return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel remover role");
                    }
                }
            }

            EnsoLogsModel::addEnsoLog($authusername, "Edited user '$username'.", EnsoLogsModel::$INFORMATIONAL, 'User');

            /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "ok");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed.", EnsoLogsModel::$ERROR, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function addUser($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
            $username = Input::validate($request->getParam('username'), Input::$STRICT_STRING, 4);
            $email = Input::validate($request->getParam("email"), Input::$EMAIL, 1);
            $ldap = (int)Input::validate($request->getParam("ldap"), Input::$BOOLEAN, 2);
            $sysadmin = (int)Input::validate($request->getParam("sysadmin"), Input::$BOOLEAN, 3);
            $password = Input::validate($request->getParam("password"), Input::$STRICT_STRING, 6);

            /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);
            /* 2. autorização - validação de permissões */

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageUsers'))
                throw new RBACDeniedException();

            if (UserModel::exists(['username' => $username]))
                throw new BadInputValidationException(5);

            /* 4. executar operações */

            UserModel::insert(
                [
                    'username' => $username,
                    'email' => $email,
                    'ldap' => $ldap,
                    'password' => EnsoShared::hash($password)
                ]
            );


            if ($sysadmin) {
                $return = EnsoRBACModel::addRoleToUser($username, "SysAdmin", time());

                if ($return === false) {
                    EnsoLogsModel::addEnsoLog($authusername, "Tried to add user '$username', operation failed because the role could not be added.", EnsoLogsModel::$ERROR, 'User');
                    return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel adicionar a role");
                }
            }

            $return = EnsoRBACModel::addRoleToUser($username, "NormalUser", time());

            if (!$return) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to add user '$username', operation failed because the role could not be added.", EnsoLogsModel::$ERROR, 'User');
                return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel adicionar a role");
            }

            EnsoLogsModel::addEnsoLog($authusername, "Added user '$username'.", EnsoLogsModel::$INFORMATIONAL, 'User');

            /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "ok");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed.", EnsoLogsModel::$ERROR, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function removeUser($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $username = Input::validate($request->getParam("username"), Input::$STRING, 0, UserModel::class, 'username');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageUsers'))
                throw new RBACDeniedException();

            //Remover mensagens e credenciais

            $msgsIn = MessageModel::getWhere(['receiverId' => $username]);
            $msgsOut = MessageModel::getWhere(['senderId' => $username]);
            $extMsgsOut = ExternalMessageModel::getWhere(['senderId' => $username]);

            MessageModel::delete(['receiverId' => $username]);
            MessageModel::delete(['senderId' => $username]);
            ExternalMessageModel::delete(['senderId' => $username]);

            foreach ($msgsIn as $value) {
                if ($value['belongsToFolder'] == null)
                    CredentialModel::delete(['idCredentials' => $value['referencedCredential']]);
            }

            foreach ($msgsOut as $value) {
                if ($value['belongsToFolder'] == null)
                    CredentialModel::delete(['idCredentials' => $value['referencedCredential']]);
            }

            foreach ($extMsgsOut as $value) {
                if ($value['belongsToFolder'] == null)
                    CredentialModel::delete(['idCredentials' => $value['referencedCredential']]);
            }

            //Remover user

            PermissionModel::delete(['userId' => $username]);

            UserModel::delete(['username' => $username]);

            if (!EnsoRBACModel::removeAllUserRoles($username)) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to remove user '$username', operation failed because the roles could not be removed.", EnsoLogsModel::$ERROR, 'User');
                return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel remover roles");
            }

            EnsoLogsModel::addEnsoLog($authusername, "Removed user '$username'.", EnsoLogsModel::$INFORMATIONAL, 'User');

            /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "ok");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "User");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get users matching $search, operation failed.", EnsoLogsModel::$ERROR, "User");
            EnsoDebug::var_error_log($e);
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }
}

$app->get('/users/search/', 'Users::getMatching');
$app->get('/users/', 'Users::getInfoByUsername');
$app->put('/users/', 'Users::editUser');
$app->post('/users/', 'Users::addUser');
$app->delete('/users/', 'Users::removeUser');
