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

class Messages {

    public static function addNewMessage() {
        $req = ensoGetRequest();

        $key = $req->post('sessionkey');
        $authusername = $req->post('authusername');
        $receiver = $req->post('receiver');
        $credential = $req->post('referencedCredential');
        $message = $req->post('message');
        $timeToDie = $req->post('timeToDie');
        $title = $req->post('title');
        $username = $req->post('username');
        $password = $req->post('password');
        $description = $req->post('description');
        $url = $req->post('url');


        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'shareCredentials')) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to share a credential but has no such permission", EnsoLogsModel::$NOTICE, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        if (!UserModel::userExists($receiver))
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);

        switch ($timeToDie) {
            case "+6 hours":
            case "+12 hours":
            case "+24 hours":
            case "+7 days":
                break;

            default:
                return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);
                break;
        }

        /* 4. executar operações */

        if ($credential === NULL) { //criar cred temporaria
            $credential = CredentialModel::addCredential($title, $username, $password, $description, $url, NULL, $authusername);

            if ($credential === false) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed.", EnsoLogsModel::$ERROR, "Messages");
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao criar credencial");
            }
        }

        if (!CredentialModel::credentialExistsById($credential))
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);


        if (MessageModel::addMessage($message, $timeToDie, $credential, $authusername, $receiver) === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to share credential $credential , operation failed.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao partilhar credencial");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Shared credential $credential", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }

    public static function getInbox() {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        MessageModel::deleteDeadMessages();

        $inbox = MessageModel::getMessagesReceivedBy($authusername);

        if ($inbox === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult inbox , operation failed.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter inbox");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Consulted inbox", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $inbox);
    }

    public static function getOutbox() {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        MessageModel::deleteDeadMessages();

        $outbox = MessageModel::getMessagesSentBy($authusername);

        if ($outbox === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult outbox , operation failed.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter outbox");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Consulted outbox", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $outbox);
    }

    public static function getMessage() {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');
        $messageId = $req->get('messageId');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        $message = MessageModel::getMessageById($messageId);

        if ($message === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult message $messageId , operation failed because no record of such message was found.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter mensagem");
        }

        if ($message['senderId'] !== $authusername && $message['receiverId'] !== $authusername) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult message $messageId , operation failed because user does not have access to this message.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter mensagem");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Consulted message $messageId", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

        ensoSendResponse(EnsoShared::$ENSO_REST_OK, $message);
    }

    public static function saveCredential() {
        $req = ensoGetRequest();

        $key = $req->post('sessionkey');
        $authusername = $req->post('authusername');
        $messageId = $req->post('messageId');
        $belongsTo = $req->post('belongsTo');

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        $message = MessageModel::getMessageById($messageId);

        if ($message === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult message $messageId , operation failed because no record of such message was found.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter mensagem");
        }

        if ($message['senderId'] !== $authusername && $message['receiverId'] !== $authusername) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult message $messageId , operation failed because user does not have access to this message.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter mensagem");
        }

        if ($message['belongsToFolder'] == NULL) {
            /*
             * Credencial temporaria foi criada ao partilhar
             */

            if (FolderModel::folderExistsById($belongsTo) === false)
                return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 5);

            if (EnsoRBACModel::checkUserHasAction($authusername, 'manageCredentials') === false || PermissionModel::hasPermissionToSeeFolder($authusername, $belongsTo) === false) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Messages");
                return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }

            if (CredentialModel::moveToFolder($message['idCredentials'], $belongsTo) === false) {
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao mover credencial temporaria");
            }
        } else {
            /*
             * This is tricky ok... os paramteros de credenciais estão cá todos por isso vou tratar esta request como uma de credencial
             * Se correr mal faz de conta que tentei inserir uma credencial e está mal 
             */

            Credentials::addNewCredential();

            global $app;
            if ($app->response()->finalize()[0] != EnsoShared::$ENSO_REST_OK) {
                return;
            }
        }

        /* Credencial bem inserida vou eliminar mensagem  */

        /* 4. executar operações */

        MessageModel::removeMessage($messageId);

        EnsoLogsModel::addEnsoLog($authusername, "Saved credential from message $messageId", EnsoLogsModel::$INFORMATIONAL, "Messages");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }

    public static function deleteMessage() {
        $req = ensoGetRequest();
        
        $key = $req->delete('sessionkey');
        $authusername = $req->delete('authusername');
        $messageId = $req->delete('messageId');

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        $message = MessageModel::getMessageById($messageId);

        if ($message === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult message $messageId , operation failed because no record of such message was found.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter mensagem");
        }

        if ($message['senderId'] !== $authusername && $message['receiverId'] !== $authusername) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to consult message $messageId , operation failed because user does not have access to this message.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao obter mensagem");
        }

        MessageModel::removeMessage($messageId);
        
        if($message['belongsToFolder'] === NULL)
        {
            CredentialModel::removeCredential($message['referencedCredential']);
        }

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }
    
    public static function getExternalMessage() {
        $req = ensoGetRequest();

        $externalKey = $req->get('externalKey');

        /* 1. autenticação - validação do token */

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        $message = MessageModel::getExternalMessage($externalKey);

        if ($message === false) {
            EnsoLogsModel::addEnsoLog("external", "Tried to consult message with key $externalKey , operation failed because no record of such message was found.", EnsoLogsModel::$ERROR, "External Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_FOUND, "Falha ao obter mensagem");
        }

        EnsoLogsModel::addEnsoLog("external", "Consulted message with key $externalKey", EnsoLogsModel::$INFORMATIONAL, "External Messages");
        
        MessageModel::removeExternalMessage($externalKey);

        /* 5. response */

        if($message['belongsToFolder'] === NULL)
        {
            CredentialModel::removeCredential($message['referencedCredential']);
        }
        
        ensoSendResponse(EnsoShared::$ENSO_REST_OK, $message);
    }
    
    public static function addNewExternalMessage() {
        $req = ensoGetRequest();

        $key = $req->post('sessionkey');
        $authusername = $req->post('authusername');
        $receiver = $req->post('receiver');
        $credential = $req->post('referencedCredential');
        $message = $req->post('message');
        $timeToDie = $req->post('timeToDie');
        $title = $req->post('title');
        $username = $req->post('username');
        $password = $req->post('password');
        $description = $req->post('description');
        $url = $req->post('url');
        $destination = $req->post('destination');
        $serverpath = $req->post('serverpath');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'shareCredentials')) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to share a credential but has no such permission", EnsoLogsModel::$NOTICE, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        switch ($timeToDie) {
            case "+6 hours":
            case "+12 hours":
            case "+24 hours":
            case "+7 days":
                break;

            default:
                return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);
                break;
        }

        if (!filter_var($receiver, FILTER_VALIDATE_EMAIL) && $receiver !== "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 4);

        /* 4. executar operações */

        if ($credential === NULL) { //criar cred temporaria
            $credential = CredentialModel::addCredential($title, $username, $password, $description, $url, NULL, $authusername);

            if ($credential === false) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed.", EnsoLogsModel::$ERROR, "Messages");
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao criar credencial");
            }
        }


        if (CredentialModel::credentialExistsById($credential) === false)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);


        $externalKey = MessageModel::addExternalMessage($message, $timeToDie, $credential, $authusername);
        
        if ($externalKey === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to share credential $credential , operation failed.", EnsoLogsModel::$ERROR, "Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao partilhar credencial");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Shared external credential $credential", EnsoLogsModel::$INFORMATIONAL, "Messages");

        global $ensoMailConfig;
        
        if($receiver != "")
            Ensomail::sendMail($ensoMailConfig["from"], $receiver, "Notificação de Credencial", $serverpath . $externalKey);

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $externalKey);
    }

    public static function getInboxCount() {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');



        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        $count = MessageModel::getInboxCount($authusername);

        if ($count === false) {
            EnsoLogsModel::addEnsoLog("external", "Tried to consult inbox count , operation failed.", EnsoLogsModel::$ERROR, "External Messages");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao contagem");
        }
        

        /* 5. response */
        
        ensoSendResponse(EnsoShared::$ENSO_REST_OK, $count['numero']);
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
