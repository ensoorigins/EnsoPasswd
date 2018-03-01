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

        $user = UserModel::getWhere(
            [
                'username' => $username
            ]
        )[0];

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
        $newkey = EnsoShared::generateSecret();

        UserModel::editWhere(
            [
                "username" => $username
            ],
            [
                "sessionKey" => $newkey,
                "trustLimit" => strtotime('+30 minutes')
            ]
        );

        return $newkey;
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
        if (UserModel::exists([
            'username' => $username,
            "sessionKey" => $key,
            "trustLimit" => [">", time()]
        ])) {

            if ($renewTrustLimit) {
                UserModel::editWhere(
                    [
                        'username' => $username
                    ],
                    [
                        'trustLimit' => strtotime('+30 minutes')
                    ]
                );
            }

            return true;
        } else {
            throw new AuthenticationException($username);
        }
    }

    public static function performInternalCredentialCheck($password, $userdata)
    {
        if ($userdata !== false && $userdata["password"] == EnsoShared::hash($password)) {
            return true;
        } else {
            throw new AuthenticationException($username);
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

        if ($userEntry === false) //No user found
        return 0;

        $userDn = ldap_get_dn($ds, $userEntry);
        
        /* try to auth as user */

        if (@$bind = ldap_bind($ds, $userDn, $password)) {
            
            //connected successfully, auth ok

            @ldap_close($ds);
            return true;
        } else {

            /* failed to connect bad auth */
            throw new AuthenticationException($username);
        }
    }

}
