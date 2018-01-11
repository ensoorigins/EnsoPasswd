<?php
class AuthenticationModel
{

    /**
     * Função para verificar se o utilizador e a password correspondem
     * 
     * @param string $username
     * @param string $password plain-text password
     * @return TRUE a autenticação for feita com sucesso, FALSE senão
     */
    public static function performCredentialCheck($username, $password)
    {

        $user = UserModel::getUserInfo($username);

        if ($user['ldap'] == 1) {
            return self::performExternalCredentialCheck($username, $password);
        } else {
            return self::performInternalCredentialCheck($password, $user);
        }
    }

    /**
     * Função para gerar uma nova sessionkey para o utilizador
     * 
     * @param string $username
     * @return sessionkey se for gerada com sucesso, FALSE se ocorreu um erro
     */
    public static function generateNewSessionKeyForUser($username)
    {

        $sql = "UPDATE " . UserModel::$USERS_TABLE . " " .
            "SET " .
            "sessionKey = :sessionKey, " .
            "trustLimit = :trustLimit " .
            "WHERE username = :username";

        $values = array();

        $values[':username'] = $username; // save the placeholder
        $values[':sessionKey'] = EnsoShared::generateSecret(); // save the placeholder
        $values[':trustLimit'] = strtotime('+30 minutes'); // 60 * 30; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);			
				
			//retorno do valor
            return $values[':sessionKey'];
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Função para verificar se o utilizador e a sessionkey são válidas
     * 
     * @param string $username
     * @param string $sessionkey chave de sessão a verificar
     * @return TRUE se estiver válida, FALSE senão
     */
    public static function checkIfSessionKeyIsValid($key, $username, $renewTrustLimit = true)
    {
        $sql = "SELECT username FROM " . UserModel::$USERS_TABLE . " " .
            "WHERE sessionKey = :sessionKey AND trustLimit > :now AND username = :username";

        $values = array();

        $values[':username'] = $username;
        $values[':sessionKey'] = $key; // save the placeholder
        $values[':now'] = time(); // save the placeholder


        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();

            if (count($rows) !== 1)
                return false;

            if ($renewTrustLimit) {
                $sql = "UPDATE " . UserModel::$USERS_TABLE . " " .
                    "SET " .
                    "trustLimit = :trustLimit " .
                    "WHERE username = :username AND sessionKey = :sessionKey";

                $values = array();

                $values[':username'] = $username;
                $values[':sessionKey'] = $key; // save the placeholder
                $values['trustLimit'] = strtotime('+30 minutes');

                $db->prepare($sql);
                $db->execute($values);
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function performInternalCredentialCheck($password, $userdata)
    {

        if ($userdata !== false && $userdata["password"] == EnsoShared::hash($password)) {
            return true;
        } else {
            return false;
        }
    }

    public static function performExternalCredentialCheck($username, $password)
    {

        global $ldapConfig;
        /* connect as anon */

        $ds = ldap_connect($ldapConfig['host'], $ldapConfig['port']);

        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, $ldapConfig['timeout']);
        
        /* search user */

        $formattedSearch = sprintf($ldapConfig['query'], $username, $username);

        $userSearch = ldap_search($ds, $ldapConfig['mainDn'], $formattedSearch);

        $userEntry = ldap_first_entry($ds, $userSearch);

        if ($userEntry === FALSE) //No user found
        return 0;

        $userDn = ldap_get_dn($ds, $userEntry);
        
        /* try to auth as user */

        if (@$bind = ldap_bind($ds, $userDn, $password)) {
            
            //connected successfully, auth ok

            @ldap_close($ds);
            return 1;
        } else {

            /* failed to connect bad auth */
            return 0;
        }
    }

}
