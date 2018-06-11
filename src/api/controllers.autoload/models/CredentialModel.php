<?php

class CredentialModel extends Entity
{
    protected static $table = "Credentials";

    protected static $columns = [
        "idCredentials",
        "title",
        "username",
        "password",
        "description",
        "url",
        "belongsToFolder",
        "createdById"
    ];

    public static function getEncryptionKey()
    {
        $key = "";
        global $encryptionKeyLocation;
        $myfile = fopen($encryptionKeyLocation . "encryption.key", "r") or die("Unable to open encryption file!");
        $key = fread($myfile, 32);
        fclose($myfile);

        return $key;
    }

    public static function insert($attributes)
    {
        if (array_key_exists("password", $attributes)) {
            $attributes['password'] = EnsoShared::encrypt(EnsoShared::networkDecode($attributes['password']), static::getEncryptionKey());
        }

        return parent::insert($attributes);
    }

    public static function getWhere($filters, $attributes = null, $range = null)
    {
        $result = parent::getWhere($filters, $attributes, $range);

        if ($attributes === null || array_key_exists("password", $attributes))
            foreach ($result as &$cred)
                $cred['password'] = EnsoShared::networkEncode(EnsoShared::decrypt($cred['password'], static::getEncryptionKey()));

        return $result;
    }

    public static function editWhere($filters, $newAttributes)
    {
        if (array_key_exists("password", $newAttributes)) {
            $newAttributes['password'] = EnsoShared::encrypt(EnsoShared::networkDecode($newAttributes['password']), static::getEncryptionKey());
        }

        parent::editWhere($filters, $newAttributes);
    }

    public static function getMatchesBelongingTo($belongsTo, $termos = ['%'])
    {
        $sql = "SELECT idCredentials, title, createdById, username FROM " . static::$table . " " .
            "WHERE belongsToFolder = :belongsToFolder AND (";

        $values = array();

        foreach ($termos as $key => $termo) {
            $sql .= "(LCASE(title) LIKE LCASE(:search$key) OR LCASE(description) LIKE LCASE(:search$key) OR LCASE(url) LIKE LCASE(:search$key)) AND ";
            $values['search' . $key] = $termo;
        }

        $sql = rtrim($sql, 'AND ');

        $sql .= ")";

        $values[':belongsToFolder'] = $belongsTo; // save the placeholder

        $db = new EnsoDB();
        $db->prepare($sql);
        $db->execute($values);

        return $db->fetchAll();
    }
}