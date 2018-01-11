<?php
/*
 * Errors
 * 
 * 1 - Data de começo não pode ser maior do que a data de fim
 * 
 * 
 */

class Logs {
    public static function getFilterInfo()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');


        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'viewLogs')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        /* 4. executar operações */

        $facilities = EnsoLogsModel::getAvailableFacilities();
        $severities = EnsoLogsModel::getUsedSeverityLevels();
        $users = EnsoLogsModel::getUsersPresentInLogs();

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, ["facilities" => $facilities, "severities" => $severities, "users" => $users]);
    }
    
        public static function getLogs()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');
        $facility = $req->get('facility');
        $startTime = $req->get('startTime');
        $endTime = $req->get('endTime');
        $severity = $req->get('severity');
        $userSearch = $req->get('userSearch');
        $startIndex = $req->get('startIndex');
        $advance = $req->get('advance');
        $searchString = $req->get('search');


        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'viewLogs')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */
        
        if($facility === NULL)
            $facility = "";
        
        if($severity === NULL)
            $severity = "";
        
        if(($startTime === "" xor $startTime === "") || ($startTime > $endTime))
        {
            $startTime = NULL;
            $endTime = NULL;
        }
        else
        {
            $endTime = strtotime('+1 day', intval($endTime));
        }
        
        $searchString = '%' . $searchString . '%';

        /* 4. executar operações */

        $logs = EnsoLogsModel::getLogs($facility, $startTime, $endTime, $severity, $userSearch, $startIndex, $advance, $searchString);
        
        if($logs === false)
        {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to access logs but operation failed", EnsoLogsModel::$ERROR, "Logs");
             return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $logs);
    }
}

$app->get("/logFilters/", 'Logs::getFilterInfo');
$app->get("/logs/", "Logs::getLogs");
