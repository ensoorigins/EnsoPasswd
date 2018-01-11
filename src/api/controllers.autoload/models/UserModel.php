<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class UserModel
{

    /**
     * Nome da tabela com os utilizadores
     */
    static $USERS_TABLE = 'Users';

    /**
     * Nome da view de conveniencia que verifica se é sysadmin ou não
     */
    private static $USERINFO_VIEW = 'UserInfo';

    /*
     * Obter listas listagem de utilizadores cujo username ou email tenham match com argumento
     *
     * @param $string - string de procura
     *
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return Lista de utilzadores - array(array('username'=>username,'email'=>email))
     *
     */

    public static function getUsersMatching($string)
    {
        $sql = "SELECT username, email, sysadmin FROM " . self::$USERINFO_VIEW . " " .
            "where LCASE(username) LIKE LCASE(:search) OR LCASE(email) LIKE LCASE(:search)";

        $values = array();
        $values[':search'] = $string; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();
				
			//retorno do valor
            return $rows;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter username, email e ldap de um determinado username
     *
     * @param $username - username a procurar
     *
     * @return FALSE - caso haja um erro de execução de query
     * @return informação de utilizador - array('username'=>username,'email'=>email, 'ldap'=>ldap)
     *
     */

    public static function getUser($username)
    {
        $sql = "SELECT Users.username, Users.email, Users.ldap FROM " . self::$USERS_TABLE . " " .
            "where username = :username";

        $values = array();

        $values[':username'] = $username; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetchAll();

            if (sizeof($row) != 1)
                return false;

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter username, email, ldap, password e se é sysadmin de um determinado username
     *
     * @param $username - username a procurar
     *
     * @return FALSE - caso haja um erro de execução de query
     * @return informação de utilizador - array('username'=>username,'email'=>email, 'ldap'=>ldap, 'password'=>password, 'sysadmin'=>temRoleSysadmin)
     *
     */

    public static function getUserInfo($username)
    {
        $sql = "SELECT * FROM " . self::$USERINFO_VIEW . " " .
            "where username = :username";

        $values = array();

        $values[':username'] = $username; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetch();
            
			//retorno do valor
            return $row;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Edita utilizador
     *
     * @param $username - username a editar
     * @param $email - novo email
     * @param $ldap - se é ldap
     * @param $password - password antes de hash
     *
     * @return FALSE - caso haja um erro de execução de query
     * @return TRUE - caso a operação tenha sido bem sucedida
     *
     */

    public static function editUser($username, $email, $ldap, $password)
    {

        $row = self::getUser($username);

        if ($row == false || count($row) < 1) {
            return false; //User wasn't found in records
        }

        $sql = "UPDATE " . self::$USERS_TABLE . " " .
            "SET " .
            "password = :password, " .
            "email = :email, " .
            "ldap = :ldap " .
            "WHERE username = :username";

        $values = array();

        $values[':username'] = $username; // save the placeholder
        if ($password != "")
            $values[':password'] = EnsoShared::hash($password); // save the placeholder
        else
            $sql = str_replace(":password", "password", $sql);
        $values[':email'] = $email; // save the placeholder

        if ($ldap != NULL)
            $values[':ldap'] = $ldap; // save the placeholder
        else
            $sql = str_replace(":ldap", "ldap", $sql);

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);
                
                //retorno do valor
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Cria um utilizador
     *
     * @param $username - username a criar
     * @param $email - email
     * @param $ldap - ldap
     * @param $password - password antes de hash
     *
     * @return FALSE - caso haja um erro de execução de query
     * @return TRUE - caso a operação tenha sido bem sucedida
     *
     */

    public static function addUser($username, $email, $ldap, $password)
    {

        $sql = "INSERT INTO " . self::$USERS_TABLE . " " .
            "(username, password, email, ldap) " .
            "VALUES " .
            "(:username, :password, :email, :ldap)";

        $values = array();


        $values[':username'] = $username; // save the placeholder
        $values[':password'] = EnsoShared::hash($password);
        $values[':email'] = $email; // save the placeholder
        $values[':ldap'] = $ldap; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);
            
			//retorno do valor
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Elimina um utilziador
     *
     * @param $username - username a eliminar
     *
     * @return FALSE - caso haja um erro de execução de query
     * @return TRUE - caso a operação tenha sido bem sucedida
     *
     */

    public static function removeUser($username)
    {
        $sql = "DELETE FROM " . self::$USERS_TABLE . " " .
            "WHERE username = :username";

        $values = array();


        $values[':username'] = $username; // save the placeholder


        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);
            
			//retorno do valor
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
        /*
     * Obter username, email e ldap de um determinado username
     *
     * @param $username - username a procurar
     *
     * @return FALSE - caso haja um erro de execução de query
     * @return informação de utilizador - array('username'=>username,'email'=>email, 'ldap'=>ldap)
     *
     */

    public static function userExists($username)
    {
        $sql = "SELECT Users.username, Users.email, Users.ldap FROM " . self::$USERS_TABLE . " " .
            "where username = :username";

        $values = array();

        $values[':username'] = $username; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetchAll();

            if (count($row) < 1)
                return false;
            
			//retorno do valor
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
