<?php

class MessageModel extends Entity
{
    protected static $table = "Messages";
    protected static $view = "JoinedMessages";

    protected static $columns = [
        "idMessages",
        "message",
        "timeToDie",
        "referencedCredential",
        "senderId",
        "receiverId",
        "inserted_timestamp",
        "idCredentials",
        "title",
        "username",
        "password",
        "description",
        "url",
        "belongsToFolder",
        "createdById"
    ];

    public static function getWhere($filters, $attributes = null, $range = null)
    {
        $result = parent::getWhere($filters, $attributes, $range);

        if ($attributes === null || array_key_exists("password", $attributes))
            foreach ($result as &$cred)
                $cred['password'] = EnsoShared::networkEncode(EnsoShared::decrypt($cred['password'], CredentialModel::getEncryptionKey()));

        return $result;
    }
}