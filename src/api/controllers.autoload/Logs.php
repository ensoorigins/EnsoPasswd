<?php
/*
 * Errors
 * 
 * 1 - Data de começo não pode ser maior do que a data de fim
 * 
 * 
 */

class Logs
{
    public static function getFilterInfo($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'viewLogs'))
                throw new RBACDeniedException();

        /* 3. validação de inputs */

        /* 4. executar operações */

            $facilities = EnsoLogsModel::getAvailableFacilities();
            $severities = EnsoLogsModel::getUsedSeverityLevels();
            $users = EnsoLogsModel::getUsersPresentInLogs();

        /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, ["facilities" => $facilities, "severities" => $severities, "users" => $users]);
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get external message, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getLogs($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $facility = Input::validate($request->getParam('facility'), Input::$STRING);
            $startTime = Input::validate($request->getParam('startTime'), Input::$STRING);
            $endTime = Input::validate($request->getParam('endTime'), Input::$STRING);
            $severity = Input::validate($request->getParam('severity'), Input::$STRING);
            $userSearch = Input::validate($request->getParam('userSearch'), Input::$STRING);
            $startIndex = Input::validate($request->getParam('startIndex'), Input::$STRING);
            $advance = Input::validate($request->getParam('advance'), Input::$STRING);
            $searchString = Input::validate($request->getParam('search'), Input::$STRING);


        /* 1. autenticação - validação do token */

            if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
                return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
            }

        /* 2. autorização - validação de permissões */

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'viewLogs')) {
                return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }

        /* 3. validação de inputs */

            if (($startTime === "" xor $startTime === "") || ($startTime > $endTime)) {
                $startTime = null;
                $endTime = null;
            } else {
                $endTime = strtotime('+1 day', intval($endTime));
            }

            $searchString = '%' . $searchString . '%';

        /* 4. executar operações */

            $logs = EnsoLogsModel::getLogs($facility, $startTime, $endTime, $severity, $userSearch, $startIndex, $advance, $searchString);

        /* 5. response */

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $logs);
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get external message, operation failed.", EnsoLogsModel::$ERROR, "Message");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }
}

$app->get("/logFilters/", 'Logs::getFilterInfo');
$app->get("/logs/", "Logs::getLogs");
