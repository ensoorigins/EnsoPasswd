<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class PermissionModel
{

    /**
     * Nome da tabela com as permissões
     */
    static $PERMISSIONS_TABLE = 'Permissions';

    /*
     * Adicionar permissões
     *
     * @params $idFolder - folder para referencia das permissões
     * @params $hasAdmin - se tem admin na pasta ou não
     * @params $userId - user a adicionar nas permissões
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return TRUE - caso seja adicionada com sucesso
     *
     */

    public static function addNewPermission($idFolder, $hasAdmin, $userId)
    {
        $sql = "INSERT INTO " . self::$PERMISSIONS_TABLE . " " .
            "(folder, hasAdmin, userId) VALUES " .
            "(:idFolder, :hasAdmin, :userId)";

        $idFolder = FolderModel::getTopMostFolderTo($idFolder);

        $values = array();
        $values[':idFolder'] = $idFolder; // save the placeholder
        $values[':hasAdmin'] = $hasAdmin; // save the placeholder
        $values[':userId'] = $userId; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);
            
			//retorno do valor
            return true;
        } catch (PDOException $e) {
            EnsoDebug::var_error_log($e);
            return false;
        }
    }

    /*
     * Obter permissões
     *
     * @params $idFolder - folder a obter
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return $row - com a row da tabela referente à permissão
     *
     */

    public static function getFolderPermissions($idFolder)
    {
        $sql = "SELECT * FROM " . self::$PERMISSIONS_TABLE . " " .
            "where folder = :folder";

        $idFolder = FolderModel::getTopMostFolderTo($idFolder);

        $values = array();
        $values[':folder'] = $idFolder; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetchAll();
            
			//retorno do valor
            return $row;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Limpar permissões de pasta de um utilizador
     *
     * @params $idFolder - folder a obter
     * @params $user - user de referencia
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return TRUE - caso a aquery tenha corrido com sucesso
     *
     */

    public static function cleanPermissionsFromUserToFolder($idFolder, $user)
    {
        $sql = "DELETE FROM " . self::$PERMISSIONS_TABLE . " " .
            "where folder = :folder AND userId = :userId";

        $idFolder = FolderModel::getTopMostFolderTo($idFolder);

        $values = array();
        $values[':folder'] = $idFolder; // save the placeholder
        $values[':userId'] = $user; // save the placeholder

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
     * Limpar permissões de uma pasta
     *
     * @params $idFolder - folder a obter
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return TRUE - caso a aquery tenha corrido com sucesso
     *
     */

    public static function cleanPermissionsFromFolder($idFolder)
    {
        $sql = "DELETE FROM " . self::$PERMISSIONS_TABLE . " " .
            "WHERE folder = :folder";

        $idFolder = FolderModel::getTopMostFolderTo($idFolder);

        $values = array();
        $values[':folder'] = $idFolder; // save the placeholder

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
     * Consultar se utilizador tem permissões para ver pasta
     *
     * @params $idFolder - folder a obter
     *  @params $who - utilizador de referencia
     * 
     * @return FALSE - caso haja um erro de execuçãode  query ou não tenha permissões
     * @return TRUE - caso tenha permissões
     *
     */

    public static function hasPermissionToSeeFolder($who, $idFolder)
    {
        $sql = "SELECT * FROM " . self::$PERMISSIONS_TABLE . " " .
            "where folder = :folder AND userId = :userId";

        $idFolder = FolderModel::getTopMostFolderTo($idFolder);



        $values = array();
        $values[':folder'] = $idFolder; // save the placeholder
        $values[':userId'] = $who; // save the placeholder

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
    /*
     * Consultar se utilizador tem permissões para administrar pasta
     *
     * @params $idFolder - folder a obter
     *  @params $who - utilizador de referencia
     * 
     * @return FALSE - caso haja um erro de execuçãode  query ou não tenha permissões
     * @return TRUE - caso tenha permissões
     *
     */
    public static function hasPermissionToAdminFolder($who, $idFolder)
    {
        $sql = "SELECT * FROM " . self::$PERMISSIONS_TABLE . " " .
            "where folder = :folder AND userId = :userId AND hasAdmin = 1";

        $idFolder = FolderModel::getTopMostFolderTo($idFolder);

        $values = array();
        $values[':folder'] = $idFolder; // save the placeholder
        $values[':userId'] = $who; // save the placeholder

        try{
			$db = new EnsoDB();
			$db->prepare($sql);
            $db->execute($values);
			 
            $row = $db->fetchAll();

            if(count($row) < 1)
            return false;
            
			//retorno do valor
			return true;
		}catch (PDOException $e){
			return false;
		}
    }

    public static function cleanPermissionsFromUser($user)
    {
        $sql = "DELETE FROM " . self::$PERMISSIONS_TABLE . " " .
            "where userId = :userId";

        $values = array();
        $values[':userId'] = $user; // save the placeholder

        try{
			$db = new EnsoDB();
			$db->prepare($sql);
            $db->execute($values);
            
			//retorno do valor
			return true;
		}catch (PDOException $e){
			return false;
		}
    }
}
