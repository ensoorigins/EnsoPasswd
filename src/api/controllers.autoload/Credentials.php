<?php
class Credentials
{
    /* ErrorCodes */
    /*
     * 1- Url Inválido
     * 2- Titulo INválido
     * 3- Já existe uma credencial com esse nome
     * 4 - PAssword é obrigatória
     * 5 - Pasta pai não existe
     */

    public static function addNewCredential()
    {
        $req = ensoGetRequest();

        $key = $req->post('sessionkey');
        $authusername = $req->post('authusername');
        $belongsTo = $req->post('belongsTo');
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

        if (EnsoRBACModel::checkUserHasAction($authusername, 'manageCredentials') === false || PermissionModel::hasPermissionToSeeFolder($authusername, $belongsTo) === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        

        /* 3. validação de inputs */

        if (filter_var($url, FILTER_VALIDATE_URL) === false && $url !== "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);

        if ($title === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);

        $title = trim($title);

        if (CredentialModel::credentialExists($title, $belongsTo) === true)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);

        if ($password === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 4);

        if ($belongsTo !== NULL && FolderModel::folderExistsById($belongsTo) === false)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 5);

        /* 4. executar operações */



        if (CredentialModel::addCredential($title, $username, $password, $description, $url, $belongsTo, $authusername) === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao criar credencial");
        }




        EnsoLogsModel::addEnsoLog($authusername, "Added credential", EnsoLogsModel::$INFORMATIONAL, "Credencial");

        /* 5. response */



        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }

    public static function editCredential()
    {
        $req = ensoGetRequest();

        $key = $req->put('sessionkey');
        $authusername = $req->put('authusername');
        $title = $req->put('title');
        $username = $req->put('username');
        $password = $req->put('password');
        $description = $req->put('description');
        $url = $req->put('url');
        $id = $req->put('id');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageCredentials')) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit credential $id , operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        $infoCredencial = CredentialModel::getCredentialsById($id);

        if ($infoCredencial === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit credential $id, operation failed because no records of this credential were found.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter a credencial");
        }

        if (!PermissionModel::hasPermissionToSeeFolder($authusername, $infoCredencial['belongsToFolder']) && $parent != NULL) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false && $url !== "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);

        if ($title === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);

        if (CredentialModel::credentialExists($title, $infoCredencial['belongsToFolder']) === true && $infoCredencial['title'] != $title)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);

        if ($password === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 4);

        /* 4. executar operações */

        if (!CredentialModel::editCredential($id, $title, $username, $password, $description, $url)) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit credential $id , operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falha ao editar credencial");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Edited credential $id.", EnsoLogsModel::$INFORMATIONAL, "Credencial");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }

    public static function getCredentialById()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');
        $id = $req->get('credentialId');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        $infoCredencial = CredentialModel::getCredentialsById($id);

        if ($infoCredencial === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to access credential $id, operation failed because no records of this credential were found.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter a credencial");
        }

        if (!PermissionModel::hasPermissionToSeeFolder($authusername, $infoCredencial['belongsToFolder'])) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to acess credential $id, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Accessed credential $id.", EnsoLogsModel::$INFORMATIONAL, "Credencial");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $infoCredencial);
    }

    public static function removeCredential()
    {
        $req = ensoGetRequest();

        $key = $req->delete('sessionkey');
        $authusername = $req->delete('authusername');
        $id = $req->delete('credentialId');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        $infoCredencial = CredentialModel::getCredentialsById($id);

        if ($infoCredencial === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove credential $id, operation failed because no records of this credential were found.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter a credencial");
        }

        if (!PermissionModel::hasPermissionToSeeFolder($authusername, $infoCredencial['belongsToFolder'])) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove credential $id, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        if (!CredentialModel::removeCredential($id)) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove credential $id, operation failed.", EnsoLogsModel::$NOTICE, "Credencial");

            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a remover a credencial");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Credencial $id was removed.", EnsoLogsModel::$INFORMATIONAL, "Credencial");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }

}

$app->post('/credential/', 'Credentials::addNewCredential');
$app->put('/credential/', 'Credentials::editCredential');
$app->get('/credential/', 'Credentials::getCredentialById');
$app->delete('/credential/', 'Credentials::removeCredential');
