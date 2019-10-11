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

    public static function addNewCredential($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $belongsTo = $request->getParam('belongsTo');
            if ($belongsTo !== null)
                $belongsTo = Input::validate($belongsTo, Input::$INT, 5, FolderModel::class, 'idFolders');

            $title = Input::validate($request->getParam('title'), Input::$STRICT_STRING, 2);

            if ($belongsTo != NULL && CredentialModel::exists(
                [
                    'title' => $title,
                    'belongsToFolder' => $belongsTo
                ]
            ))
                throw new BadInputValidationException(3);

            $username = $request->getParam('username');
            if (!empty($username))
                $username = Input::validate($username, Input::$STRING);

            $password = Input::validate($request->getParam('password'), Input::$STRING, 4);

            $description = $request->getParam('description');
            if (!empty($description))
                $description = Input::validate($description, Input::$STRING);

            $url = $request->getParam('url');
            if (!empty($url))
                $url = Input::validate($url, Input::$URL, 1);

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);
            if ($belongsTo != null)
                if (EnsoRBACModel::checkUserHasAction($authusername, 'manageCredentials') === false || PermissionModel::hasPermissionToSeeFolder($authusername, $belongsTo) === false)
                throw new RBACDeniedException();

            $newCred = CredentialModel::insert(
                [
                    "title" => $title,
                    "username" => $username,
                    "password" => $password,
                    "description" => $description,
                    "url" => $url,
                    "belongsToFolder" => $belongsTo,
                    "createdById" => $authusername,
                ]
            );

            EnsoLogsModel::addEnsoLog($authusername, "Added credential", EnsoLogsModel::$INFORMATIONAL, "Credencial");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $newCred);

        } catch (BadInputValidationException $e) {
            EnsoDebug::var_error_log($e);
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (RBACDeniedException $e) {

            EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed due to lack of RBAC permissions .", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to create credential in folder $belongsTo , operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function editCredential($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $id = Input::validate($request->getParam('id'), Input::$INT, 0, CredentialModel::class, 'idCredentials');


            $belongsTo = $request->getParam('belongsTo');
            if($belongsTo !== null)
                $belongsTo = Input::validate($belongsTo, Input::$INT, 5/* , FolderModel::class, 'idFolders' */ );
            else
                $belongsTo = CredentialModel::getWhere(['idCredentials' => $id])[0]['belongsToFolder'];

            $title = Input::validate($request->getParam('title'), Input::$STRICT_STRING, 2);
            if (CredentialModel::exists(
                [
                    'title' => $title,
                    'belongsToFolder' => $belongsTo,
                    'idCredentials' => ["<>", $id]
                ]
            ))
                throw new BadInputValidationException(3);

            $username = $request->getParam('username');
            if (!empty($username))
                $username = Input::validate($username, Input::$STRING);

            $password = Input::validate($request->getParam('password'), Input::$STRICT_STRING, 4);

            $description = $request->getParam('description');
            if (!empty($description))
                $description = Input::validate($description, Input::$STRING);

            $url = $request->getParam('url');
            if (!empty($url))
                $url = Input::validate($url, Input::$URL, 1);

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageCredentials'))
                throw new RBACDeniedException();

            PermissionModel::hasPermissionToSeeFolder($authusername, $belongsTo);

            CredentialModel::editWhere(
                ['idCredentials' => $id],
                [
                    'title' => $title,
                    'username' => $username,
                    'password' => $password,
                    'description' => $description,
                    'url' => $url,
                    'belongsToFolder' => $belongsTo
                ]
            );

            EnsoLogsModel::addEnsoLog($authusername, "Edited credential $id.", EnsoLogsModel::$INFORMATIONAL, "Credencial");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "");

        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit credential $id , operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit credential in folder $belongsTo , operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getCredentialById($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
            $id = Input::validate($request->getParam('credentialId'), Input::$INT, 0, CredentialModel::class, 'idCredentials');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            $infoCredencial = CredentialModel::getWhere(['idCredentials' => $id])[0];

            PermissionModel::hasPermissionToSeeFolder($authusername, $infoCredencial['belongsToFolder']);

            EnsoLogsModel::addEnsoLog($authusername, "Accessed credential $id.", EnsoLogsModel::$INFORMATIONAL, "Credencial");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $infoCredencial);
        } catch (EntityCheckFailureException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_FOUND, $e->getCode());
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get credential $id, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get credential $id , operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get $id credential in folder $belongsTo, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function removeCredential($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
            $id = Input::validate($request->getParam('credentialId'), Input::$INT, 0, CredentialModel::class, 'idCredentials');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            $infoCredential = CredentialModel::getWhere(['idCredentials' => $id])[0];

            PermissionModel::hasPermissionToSeeFolder($authusername, $infoCredential['belongsToFolder']);

            CredentialModel::delete(["idCredentials" => $id]);

            EnsoLogsModel::addEnsoLog($authusername, "Credential $id was removed.", EnsoLogsModel::$INFORMATIONAL, "Credencial");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to delete credential $id, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to delete credential $id , operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to delete credential $id in folder $belongsTo, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }
}

$app->post('/credential/', 'Credentials::addNewCredential');
$app->put('/credential/', 'Credentials::editCredential');
$app->get('/credential/', 'Credentials::getCredentialById');
$app->delete('/credential/', 'Credentials::removeCredential');
