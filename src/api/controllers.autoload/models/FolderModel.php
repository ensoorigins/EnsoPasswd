<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FolderModel
{

    /**
     * Nome da tabela com os utilizadores
     */
    static $FOLDERS_TABLE = 'Folders';

    /*
     * Obter lista de pastas root
     *
     * @params $string - string de filtro
     *
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return Lista de Folders
     *
     */

    public static function getRootFolders($string)
    {
        $sql = "SELECT * FROM " . self::$FOLDERS_TABLE . " " .
            "where parent IS NULL AND LCASE(name) LIKE LCASE(:search)";

        $values = array();
        $values[':search'] = $string; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();

            return $rows;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter lista de pastas root da perpesctiva de um user
     *
     * @params $string - string de filtro
     * @params $observer - utilizador de perspectiva
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return Lista de Folders
     *
     */

    public static function getRootFoldersAsSeenBy($string, $observer)
    {
        $sql = "SELECT * FROM " . self::$FOLDERS_TABLE . " " .
            "LEFT JOIN Permissions on Folders.idFolders = Permissions.folder " .
            "WHERE Folders.parent IS NULL AND Permissions.userId = :observer AND LCASE(name) LIKE LCASE(:search)";

        $values = array();
        $values[':observer'] = $observer; // save the placeholder
        $values[':search'] = $string;

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();

            return $rows;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Criar nova pasta
     *
     * @params $nome - novo nome
     * @params $createdBy - por quem foi criada
     * @params $parent - pai da pasta
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return int - idInserida caso tenha sido criada com sucesso
     *
     */

    public static function addFolder($name, $createdBy, $parent = NULL)
    {
        $sql = "INSERT INTO " . self::$FOLDERS_TABLE . " " .
            "(parent, name, createdById) " .
            "VALUES " .
            "(:parent, :name, :createdById)";

        $values = array();
        $values[':createdById'] = $createdBy; // save the placeholder
        $values[':name'] = $name; // save the placeholder
        $values[':parent'] = $parent; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return $db->getDB()->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter dados de uma pasta
     *
     * @params $id - id da pasta a consultar
     * 
     * @return FALSE - caso haja um erro de execução de query
     * @return $row - a folder pedida
     *
     */

    public static function getFolderById($id)
    {
        $sql = "SELECT * FROM " . self::$FOLDERS_TABLE . " " .
            "where idFolders = :idFolders";

        $values = array();
        $values[':idFolders'] = $id; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();

            if (count($rows) < 1)
                return false;

            return $rows[0];
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Editar pasta
     *
     * @params $name - novo nome
     * @params $idfolder - id da folder a alterar
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return TRUE -  caso tenha sido editada com sucesso
     *
     */

    public static function editFolder($name, $idFolder)
    {
        $sql = "UPDATE " . self::$FOLDERS_TABLE . " " .
            "SET name = :name " .
            "WHERE idFolders = :idFolders";

        $values = array();
        $values[':idFolders'] = $idFolder; // save the placeholder
        $values[':name'] = $name; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter as subpastas de uma pasta
     * 
     * @params $idFolder - folder pai
     *
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return TRUE -  caso tenha sido editada com sucesso
     *
     */

    public static function getAllChildsOf($idFolder, $string)
    {
        $sql = "SELECT * FROM " . self::$FOLDERS_TABLE . " " .
            "WHERE parent = :idFolders AND LCASE(name) LIKE LCASE(:search)";

        $values = array();
        $values[':idFolders'] = $idFolder; // save the placeholder
        $values[':search'] = $string; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();

            return $rows;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * remover pasta
     * 
     * @params $idFolder - id da pasta a remover
     *
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return TRUE -  caso tenha sido editada com sucesso
     *
     */

    public static function removeFolder($idFolder)
    {
        $sql = "DELETE FROM " . self::$FOLDERS_TABLE . " " .
            "WHERE idFolders = :idFolders";

        $values = array();
        $values[':idFolders'] = $idFolder; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter a pasta pai mais perto da root
     *
     * @params $id - id da pasta a consultar
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return $row['parent'] -  o id do pai mais perto da root
     *
     */

    public static function getTopMostFolderTo($id)
    {
        try {
            $db = new EnsoDB();

            $sql = "SELECT idFolders, parent FROM " . self::$FOLDERS_TABLE . " " .
                "WHERE idFolders = :idFolders";

            $values = array();

            $values[':idFolders'] = $id;
            $row = array();

            do {

                $db->prepare($sql);
                $db->execute($values);

                $row = $db->fetch();

                $db->closeCursor();

                if ($row['parent'] != NULL) {
                    $values[':idFolders'] = $row['parent'];
                } else {
                    $row['parent'] = $values[':idFolders'];
                    break;
                }
            } while (true);

            return $row['parent'];
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter todas as pastas filhas abaixo do nivel de hierarquia (multiplos niveis)
     * Inicialmente feito para a remoção de uma pasta que é pai de outra, obtêm-se todas as subspastas TOP-DOWN, para remover BOTTOM-UP
     *
     * @params $parent - id da pasta a consultar
     * 
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return TRUE -  array com os ids de todas as subpastas
     *
     */

    public static function getChildFoldersOnAllLevels($parent)
    {
        try {
            $db = new EnsoDB();

            $sql = "SELECT * FROM " . self::$FOLDERS_TABLE . " " .
                "WHERE parent = :idFolders";

            $values = array();
            $children = array();

            if ($parent != "")
                $values[':idFolders'] = $parent; // save the placeholder
            else
                $sql = str_replace('= :idFolders', 'IS NULL', $sql);

            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetchAll();

            foreach ($row as $value) {

                array_push($children, $value);

                $children = array_merge($children, self::getChildFoldersOnAllLevels($value['idFolders']));
            }

            return $children;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
     * Obter dados de uma pasta
     *
     * @params $name - nome da pasta a procurar
     * @params $parent - pai da pasta a procurar
     * 
     * @return TRUE / FALSE - se folder existe ou não
     *
     */

    public static function folderExists($name, $parent)
    {
        $sql = "SELECT * FROM " . self::$FOLDERS_TABLE . " " .
            "where name = :name AND parent = :parent";

        $values = array();
        $values[':name'] = $name; // save the placeholder
        if($parent == NULL)
            $sql = str_replace('= :parent', 'IS NULL', $sql);
        else
            $values[':parent'] = $parent;

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();

            if (count($rows) < 1)
                return false;

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function folderExistsById($id)
    {
        $sql = "SELECT COUNT(*) FROM " . self::$FOLDERS_TABLE . " " .
            "WHERE idFolders = :idFolders";

        $values = array();
        $values[':idFolders'] = $id; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetch(PDO::FETCH_COLUMN);

            if($row[0] < 1)
                return false;

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}