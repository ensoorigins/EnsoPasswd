<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Authentication
{
    public static function attemptLogin()
    {
         $req = ensoGetRequest();

        /* 1. autenticação - validação do token */

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        if(file_exists("../setup/"))
        {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);
        }

        $username = $req->get("username");
        $password = $req->get("password");

        /* 4. executar operações */
        
        if(AuthenticationModel::performCredentialCheck($username, $password))
        {
            //Generate Session Key
            $auth_key = AuthenticationModel::generateNewSessionKeyForUser($username);
            
            //Get Actions
            $actions = EnsoRBACModel::getAvailableUserActions($username);    
            
            EnsoLogsModel::addEnsoLog($username, "Logged in.", EnsoLogsModel::$INFORMATIONAL, 'Authentication');
            
            return ensoSendResponse( EnsoShared::$ENSO_REST_OK, [ "sessionkey" => $auth_key, "actions" => $actions, "username" => $username]);
        }
        else
        {           
            ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }
                
        

        /* 5. response */
    }

    public static function checkValidity()
    {
        $req = ensoGetRequest();
        
        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');

        ensoSendResponse(EnsoShared::$ENSO_REST_OK, AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) ? "1" : "0");
    }
}

$app->get('/auth/', 'Authentication::attemptLogin');
$app->get('/validity/', 'Authentication::checkValidity');
//$app->get('/validSession/', 'Authentication::checkIfSessionKeyIsValid');
