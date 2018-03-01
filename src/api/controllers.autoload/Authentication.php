<?php

class Authentication
{
    public static function attemptLogin($request, $response, $args)
    {
        try {


            if (file_exists("../setup/")) {
                return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);
            }

            $username = Input::validate($request->getParam("username"), Input::$STRING, 0, UserModel::class, 'username');
            $password = Input::validate($request->getParam("password"), Input::$STRING);

        /* 4. executar operações */

            AuthenticationModel::performCredentialCheck($username, $password);

            //Generate Session Key
            $auth_key = AuthenticationModel::generateNewSessionKeyForUser($username);
            
            //Get Actions
            $actions = EnsoRBACModel::getAvailableUserActions($username);

            EnsoLogsModel::addEnsoLog($username, "Logged in.", EnsoLogsModel::$INFORMATIONAL, 'Authentication');

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, ["sessionkey" => $auth_key, "actions" => $actions, "username" => $username]);

        } catch (PermissionDeniedException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function checkValidity($request, $response, $args)
    {
        try {

            $key = Input::Validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "1");

        } catch (PermissionDeniedException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "0");
        } catch (Exception $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }


    }
}

$app->get('/auth/', 'Authentication::attemptLogin');
$app->get('/validity/', 'Authentication::checkValidity');