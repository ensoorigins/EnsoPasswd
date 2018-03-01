<?php

/*
 * Errors
 * 
 * 1 - Destination user does not exist
 * 2 - Credential non-existent
 * 3 - Invalid timeToDie
 * 4 - Email 
 * 
 */

class Messages
{

    private static function deleteDeadMessages()
    {
        MessageModel::delete(['timeToDie' => ["<=", EnsoShared::now()]]);
        ExternalMessageModel::delete(['timeToDie' => ["<=", EnsoShared::now()]]);
    }

    public static function addNewMessage($request, $response, $args)
    {

        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $receiver = Input::validate($request->getParam('receiver'), Input::$STRICT_STRING, 1, UserModel::class, 'username');

            $credential = $request->getParam('referencedCredential');
            if (!empty($credential))
                $credential = Input::validate($credential, Input::$INT, 2, CredentialModel::class, 'idCredentials');

            $message = $request->getParam('message');
            if (!empty($message))
                $message = Input::validate($message, Input::$STRING);

            $timeToDie = $request->getParam('timeToDie');
            switch ($timeToDie) {
                case "+6 hours":
                case "+12 hours":
                case "+24 hours":
                case "+7 days":
                    break;

                default:
                    throw new BadInputValidationException(3);
            }

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'shareCredentials'))
                throw new RBACDeniedException();

        /* 4. executar operações */

            if ($credential === null) {
                $response = Credentials::addNewCredential($request, $response, $args);

                if ($response->getStatusCode() !== EnsoShared::$ENSO_REST_OK)
                    return $response;
                else {
                    $response->getBody()->rewind();
                    $credential = json_decode($response->getBody()->getContents());
                }
            }

            MessageModel::insert([
                "message" => $message,
                "timeToDie" => strtotime($timeToDie),
                "referencedCredential" => $credential,
                "senderId" => $authusername,
                "receiverId" => $receiver
            ]);

            global $ensoMailConfig;
            Ensomail::sendMail($ensoMailConfig["from"], UserModel::getWhere(["username" => $receiver])[0]['email'], "Notificação de Credencial", "You have received a credential on your EnsoPasswd platform.");

            EnsoLogsModel::addEnsoLog($authusername, "Shared credential $credential", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add new message, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add new message, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add new message, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getInbox($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

            self::deleteDeadMessages();

            $inbox = MessageModel::getWhere(
                [
                    "receiverId" => $authusername
                ]
            );

            EnsoLogsModel::addEnsoLog($authusername, "Consulted inbox", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $inbox);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get inbox, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get inbox, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get inbox, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getOutbox($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

            self::deleteDeadMessages();

            $outbox = MessageModel::getWhere(
                [
                    "senderId" => $authusername
                ]
            );

            EnsoLogsModel::addEnsoLog($authusername, "Consulted outbox", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $outbox);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get outbox, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get outbox, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get outbox, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getMessage($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
            $messageId = Input::validate($request->getParam('messageId'), Input::$INT, 0, MessageModel::class, 'idMessages');

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

            $message = MessageModel::getWhere(['idMessages' => $messageId])[0];

            if ($message['senderId'] !== $authusername && $message['receiverId'] !== $authusername)
                throw new PermissionDeniedException();

            EnsoLogsModel::addEnsoLog($authusername, "Consulted message $messageId", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

            ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $message);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get message $messageId, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get message $messageId, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get message $messageId, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function saveCredential($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $messageId = Input::validate($request->getParam('messageId'), Input::$INT, 0, MessageModel::class, 'idMessages');
            $belongsTo = Input::validate($request->getParam('belongsTo'), Input::$INT, 5, FolderModel::class, 'idFolders');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            $message = MessageModel::getWhere(['idMessages' => $messageId])[0];

            if ($message['senderId'] !== $authusername && $message['receiverId'] !== $authusername)
                throw new PermissionDeniedException();

            if ($message['belongsToFolder'] == null) {
            /*
                 * Credencial temporaria foi criada ao partilhar
                 */

                if (EnsoRBACModel::checkUserHasAction($authusername, 'manageCredentials') === false)
                    throw new RBACDeniedException();

                PermissionModel::hasPermissionToSeeFolder($authusername, $belongsTo);

                CredentialModel::editWhere(['idCredentials' => $message['idCredentials']], ['belongsToFolder' => $belongsTo]);
            } else {
            /*
                 * This is tricky ok... os paramteros de credenciais estão cá todos por isso vou tratar esta request como uma de credencial
                 * Se correr mal faz de conta que tentei inserir uma credencial e está mal 
                 */

                $response = Credentials::addNewCredential($request, $response, $args);

                if ($response->getStatusCode() !== EnsoShared::$ENSO_REST_OK)
                    return $response;
                    else {
                        $response->getBody()->rewind();
                        $credential = json_decode($response->getBody()->getContents());
                    }
            }

        /* Credencial bem inserida vou eliminar mensagem  */

        /* 4. executar operações */

            MessageModel::delete(['idMessages' => $messageId]);

            EnsoLogsModel::addEnsoLog($authusername, "Saved credential from message $messageId", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "");
        } catch (BadInputValidationException $e) {
            EnsoDebug::var_error_log($e);
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to save credential from message $messageId, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to save credential from message $messageId, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to save credential from message $messageId, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function deleteMessage($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $messageId = Input::validate($request->getParam('messageId'), Input::$INT, 0, MessageModel::class, 'idMessages');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            $message = MessageModel::getWhere(['idMessages' => $messageId])[0];

            if ($message['senderId'] !== $authusername && $message['receiverId'] !== $authusername)
                throw new PermissionDeniedException();

            MessageModel::delete(['idMessages' => $messageId]);

            if ($message['belongsToFolder'] === null)
                CredentialModel::delete(['idCredentials' => $message['referencedCredential']]);

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to delete external message, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to delete external message, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to delete external message, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getExternalMessage($request, $response, $args)
    {
        try {

            $externalKey = Input::validate($request->getParam('externalKey'), Input::$STRING, 0, ExternalMessageModel::class, 'externalKey');

            $message = ExternalMessageModel::getWhere(['externalKey' => $externalKey])[0];

            EnsoLogsModel::addEnsoLog("external", "Consulted message with key $externalKey", EnsoLogsModel::$INFORMATIONAL, "External Messages");

            ExternalMessageModel::delete(['externalKey' => $externalKey]);

            if ($message['belongsToFolder'] === null)
                CredentialModel::delete(['idCredentials' => $message['referencedCredential']]);

            ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $message);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_FOUND, $e->getCode());
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog("external", "Tried to get external message, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function addNewExternalMessage($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $receiver = $request->getParam('receiver');
            if (!empty($receiver))
                $receiver = Input::validate($receiver, Input::$EMAIL, 4);

            $credential = $request->getParam('referencedCredential');
            if ($credential === null)
                $credential = Input::validate($credential, Input::$INT, 2, CredentialModel::class, 'idCredentials');

            $message = $request->getParam('message');
            if (!empty($message))
                $message = Input::validate($message, Input::$STRING);

            $timeToDie = $request->getParam('timeToDie');
            switch ($timeToDie) {
                case "+6 hours":
                case "+12 hours":
                case "+24 hours":
                case "+7 days":
                    break;
                default:
                    throw new BadInputValidationException("bad timetodie", 3);
            }

            $destination = $request->getParam('destination');
            $serverpath = $request->getParam('serverpath');

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'shareCredentials'))
                throw new RBACDeniedException();

        /* 4. executar operações */

            if ($credential === null) {
                $response = Credentials::addNewCredential($request, $response, $args);

                if ($response->getStatusCode() !== EnsoShared::$ENSO_REST_OK)
                    return $response;
                    else {
                        $response->getBody()->rewind();
                        $credential = json_decode($response->getBody()->getContents());
                    }
            }


            $externalKey = ExternalMessageModel::insert([
                "message" => $message,
                "timeToDie" => $timeToDie,
                "referencedCredential" => $credential,
                "senderId" => $authusername
            ]);

            EnsoLogsModel::addEnsoLog($authusername, "Shared external credential $credential", EnsoLogsModel::$INFORMATIONAL, "Messages");

            global $ensoMailConfig;

            if ($receiver != "")
                Ensomail::sendMail($ensoMailConfig["from"], $receiver, "Notificação de Credencial", $serverpath . $externalKey);

        /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $externalKey);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add new external message, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add new external message, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add new external message, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getInboxCount($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

            $count = count(MessageModel::getWhere(['receiverId' => $authusername]));

            EnsoDebug::d($count);

        /* 5. response */

            ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $count);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get inbox count, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get inbox count, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get inbox count, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }
}

$app->post('/share/', 'Messages::addNewMessage');
$app->get('/inbox/', 'Messages::getInbox');
$app->get('/inboxCount/', 'Messages::getInboxCount');
$app->get('/outbox/', 'Messages::getOutbox');
$app->get('/message/', 'Messages::getMessage');
$app->post('/message/', 'Messages::saveCredential');
$app->delete('/message/', 'Messages::deleteMessage');
$app->get('/externalMessage/', 'Messages::getExternalMessage');
$app->post('/shareExternal/', 'Messages::addNewExternalMessage');
