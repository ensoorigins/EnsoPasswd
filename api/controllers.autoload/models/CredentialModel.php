<?php
class CredentialModel
{

    static $CREDENTIALS_TABLE = 'Credentials';

    public static function getEncryptionKey()
    {

        $key = "";
        global $encryptionKeyLocation;
        $myfile = fopen($encryptionKeyLocation . "encryption.key", "r") or die("Unable to open encryption file!");
        $key = fread($myfile, 32);
        fclose($myfile);

        return $key;
    }

    public static function addCredential($title, $username, $password, $description, $url, $belongsTo, $createdBy)
    {
        $sql = "INSERT INTO " . self::$CREDENTIALS_TABLE . " " .
            "(title, username, password, description, url, belongsToFolder, createdById) " .
            "VALUES " .
            "(:title, :username, :password, :description, :url, :belongsToFolder, :createdById)";

        $values = array();
        $values[':title'] = $title;
        $values[':username'] = $username;
        $values[':password'] = EnsoShared::encrypt(EnsoShared::networkDecode($password), self::getEncryptionKey());
        $values[':description'] = $description;
        $values[':url'] = $url;
        $values[':belongsToFolder'] = $belongsTo;
        $values[':createdById'] = $createdBy;

        try {

            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return $db->getDB()->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function editCredential($id, $title, $username, $password, $description, $url)
    {
        $sql = "UPDATE " . self::$CREDENTIALS_TABLE . " " .
            "SET " .
            "title = :title, username = :username, password = :password, description = :description, url = :url " .
            "WHERE idCredentials = :idCredentials";

        $values = array();
        $values[':title'] = $title;
        $values[':username'] = $username;
        $values[':password'] = EnsoShared::encrypt(EnsoShared::networkDecode($password), self::getEncryptionKey());
        $values[':description'] = $description;
        $values[':url'] = $url;
        $values[':idCredentials'] = $id;

        try {

            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getCredentialsBelongingToFolder($belongsTo, $string = ['%'])
    {
        $sql = "SELECT idCredentials, title, createdById FROM " . self::$CREDENTIALS_TABLE . " " .
            "WHERE BelongsToFolder = :belongsToFolder AND (";

            $values = array();
        foreach ($string as $key => $termo) {
            $sql .= "(LCASE(title) LIKE LCASE(:search$key) OR LCASE(description) LIKE LCASE(:search$key) OR LCASE(url) LIKE LCASE(:search$key)) AND ";
            $values['search' . $key] = $termo;
        }

        $sql = rtrim($sql, 'AND ');

        $sql .= ")";

        $values[':belongsToFolder'] = $belongsTo; // save the placeholder

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

    public static function getCredentialsById($credentialId)
    {
        $sql = "SELECT * FROM " . self::$CREDENTIALS_TABLE . " " .
            "WHERE idCredentials = :idCredentials";

        $values = array();
        $values[':idCredentials'] = $credentialId; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetch();

            $row['password'] = EnsoShared::networkEncode(EnsoShared::decrypt($row['password'], self::getEncryptionKey()));

            return $row;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function removeCredential($credentialId)
    {
        $sql = "DELETE FROM " . self::$CREDENTIALS_TABLE . " " .
            "WHERE idCredentials = :idCredentials";

        $values = array();
        $values[':idCredentials'] = $credentialId; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function removeCredentialsOfFolder($belongsToFolder)
    {
        $sql = "DELETE FROM " . self::$CREDENTIALS_TABLE . " " .
            "WHERE belongsToFolder = :belongsToFolder";

        $values = array();
        $values[':belongsToFolder'] = $belongsToFolder; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function credentialExists($credentialName, $belongsTo)
    {
        $sql = "SELECT COUNT(*) FROM " . self::$CREDENTIALS_TABLE . " " .
            "WHERE title = :title AND belongsToFolder = :belongsToFolder";

        $values = array();
        $values[':title'] = $credentialName; // save the placeholder
        $values[':belongsToFolder'] = $belongsTo;

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetch(PDO::FETCH_COLUMN);

            if ($row[0] < 1)
                return false;

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function credentialExistsById($id)
    {
        $sql = "SELECT COUNT(*) FROM " . self::$CREDENTIALS_TABLE . " " .
            "WHERE idCredentials = :idCredentials";

        $values = array();
        $values[':idCredentials'] = $id; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetch(PDO::FETCH_COLUMN);

            if ($row[0] < 1)
                return false;

            return $row;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function moveToFolder($credId, $newParent)
    {
        $sql = "UPDATE " . self::$CREDENTIALS_TABLE . " " .
            "SET belongsToFolder = :belongsToFolder " .
            "WHERE idCredentials = :idCredentials";

        $values = array();
        $values[':idCredentials'] = $credId; // save the placeholder
        $values[':belongsToFolder'] = $newParent;

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
