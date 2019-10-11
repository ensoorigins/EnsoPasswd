<?php

class ExternalMessageModel extends Entity
{
    protected static $table = "ExternalMessages";

    protected static $view = "JoinedExternalMessages";

    protected static $columns = [
        "idExternalMessage",
        "message",
        "timeToDie",
        "externalKey",
        "referencedCredential",
        "senderId",
        "inserted_timestamp",
    ];

    public static function insert($attributes, bool $transactional = false, $returnField = NULL)
    {
        $attributes['externalKey'] = '';
        
        $newId = parent::insert($attributes);

        $externalKey = EnsoShared::hash($newId);

        static::editWhere(['idExternalMessage' => $newId], ['externalKey' => $externalKey]);

        return $externalKey;
    }

    public static function getWhere($filters, $attributes = null, $range = null)
    {
        $result = parent::getWhere($filters, $attributes, $range);

        if ($attributes === null || array_key_exists("password", $attributes))
            foreach ($result as &$cred)
                $cred['password'] = EnsoShared::networkEncode(EnsoShared::decrypt($cred['password'], CredentialModel::getEncryptionKey()));

        return $result;
    }
}