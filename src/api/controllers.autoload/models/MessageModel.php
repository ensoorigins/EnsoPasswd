<?php
class MessageModel
{
    /**
     * Nome da tabela com os utilizadores
     */
    static $MESSAGE_TABLE = 'Messages';
    static $EXTERNAL_MESSAGE_TABLE = 'ExternalMessages';

    /*
     * Obter lista de pastas root
     *
     * @params $string - string de filtro
     *
     * @return FALSE - caso haja um erro de execuçãode  query
     * @return Lista de Folders
     *
     */

    public static function deleteDeadMessages()
    {
        try {
            $db = new EnsoDB();

            $sql = "DELETE FROM " . self::$MESSAGE_TABLE . " " .
                "WHERE timeToDie < :now";

            $values = array();
            $values[':now'] = EnsoShared::now(); // save the placeholder

            $db->prepare($sql);
            $db->execute($values);
            $db->closeCursor();

            $sql = "DELETE FROM " . self::$EXTERNAL_MESSAGE_TABLE . " " .
                "WHERE timeToDie < :now";

            $db->prepare($sql);
            $db->execute($values);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function addMessage($message, $timeToDie, $credential, $sender, $receiver)
    {
        $sql = "INSERT INTO " . self::$MESSAGE_TABLE . " " .
            "(message, timeToDie, referencedCredential, senderId, receiverId, inserted_timestamp) VALUES " .
            "(:message, :timeToDie, :referencedCredential, :senderId, :receiverId, :now)";

        $values = array();
        $values[':message'] = $message; // save the placeholder
        $values[':timeToDie'] = strtotime($timeToDie);
        $values[':referencedCredential'] = $credential;
        $values[':senderId'] = $sender;
        $values[':receiverId'] = $receiver;
        $values[':now'] = EnsoShared::now();

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

    public static function getExternalMessagesSentBy($sender)
    {
        $sql = "SELECT Credentials.title, ExternalMessages.idExternalMessage, ExternalMessages.referencedCredential, Credentials.belongsToFolder FROM " . self::$EXTERNAL_MESSAGE_TABLE . " " .
            "LEFT JOIN Credentials ON Credentials.idCredentials = ExternalMessages.referencedCredential " .
            "WHERE senderId = :senderId";

        $values = array();
        $values[':senderId'] = $sender; // save the placeholder

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

    public static function removeExternalMessage($externalKey)
    {
        $sql = "DELETE FROM " . self::$EXTERNAL_MESSAGE_TABLE . " " .
            "WHERE externalKey = :externalKey";

        $values = array();
        $values[':externalKey'] = $externalKey; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetch();
            
			//retorno do valor
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getMessagesSentBy($sender)
    {
        $sql = "SELECT * FROM (SELECT Credentials.title, Messages.receiverId, Messages.idMessages, Messages.referencedCredential, Credentials.belongsToFolder, Messages.inserted_timestamp FROM " . self::$MESSAGE_TABLE . " " .
            "LEFT JOIN Credentials ON Credentials.idCredentials = Messages.referencedCredential " .
            "WHERE senderId = :senderId " .
            "UNION ALL " .
            "SELECT Credentials.title, 'External', ExternalMessages.idExternalMessage, ExternalMessages.referencedCredential, Credentials.belongsToFolder, ExternalMessages.inserted_timestamp FROM " . self::$EXTERNAL_MESSAGE_TABLE . " " .
            "LEFT JOIN Credentials ON Credentials.idCredentials = ExternalMessages.referencedCredential " .
            "WHERE senderId = :senderId) mensagens ORDER BY inserted_timestamp DESC";

        $values = array();
        $values[':senderId'] = $sender; // save the placeholder

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

    public static function getMessagesReceivedBy($receiver)
    {
        $sql = "SELECT Credentials.title, Messages.senderId, Messages.idMessages, Messages.referencedCredential, Credentials.belongsToFolder FROM " . self::$MESSAGE_TABLE . " " .
            "LEFT JOIN Credentials ON Credentials.idCredentials = Messages.referencedCredential " .
            "WHERE receiverId = :receiverId " .
            "ORDER BY inserted_timestamp DESC";

        $values = array();
        $values[':receiverId'] = $receiver; // save the placeholder

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

    public static function getMessageById($id)
    {
        $sql = "SELECT * FROM " . self::$MESSAGE_TABLE . " " .
            "LEFT JOIN Credentials ON Credentials.idCredentials = Messages.referencedCredential " .
            "WHERE idMessages = :idMessages";

        $values = array();
        $values[':idMessages'] = $id; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $row = $db->fetch();
            
			//retorno do valor
            $row['password'] = EnsoShared::networkEncode(EnsoShared::decrypt($row['password'], CredentialModel::getEncryptionKey()));
            return $row;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function removeMessage($id)
    {
        $sql = "DELETE FROM " . self::$MESSAGE_TABLE . " " .
            "WHERE idMessages = :idMessages";

        $values = array();
        $values[':idMessages'] = $id; // save the placeholder

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

    public static function removeMessagesWithInteraction($who)
    {


        try {
            $db = new EnsoDB();

            $sql = "DELETE FROM " . self::$MESSAGE_TABLE . " " .
                "WHERE receiverId = :who OR senderId = :who";

            $values = array();
            $values[':who'] = $who; // save the placeholder

            $db->prepare($sql);
            $db->execute($values);
            $db->closeCursor();

            $sql = "DELETE FROM " . self::$EXTERNAL_MESSAGE_TABLE . " " .
                "WHERE senderId = :who";

            $db->prepare($sql);
            $db->execute($values);
            $db->closeCursor();
            
			//retorno do valor
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getExternalMessage($externalKey)
    {
        $sql = "SELECT * FROM " . self::$EXTERNAL_MESSAGE_TABLE . " " .
            "LEFT JOIN Credentials ON Credentials.idCredentials = ExternalMessages.referencedCredential " .
            "WHERE externalKey = :externalKey";

        $values = array();
        $values[':externalKey'] = $externalKey; // save the placeholder

        try {
            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);

            $rows = $db->fetchAll();

            if (count($rows) < 1)
                return false;

            $rows[0]['password'] = EnsoShared::networkEncode(EnsoShared::decrypt($rows[0]['password']), CredentialModel::getEncryptionKey());
            return $rows[0];

        } catch (PDOException $e) {
            return false;
        }
    }

    public static function addExternalMessage($message, $timeToDie, $credential, $sender)
    {
        try {
            $sql = "INSERT INTO " . self::$EXTERNAL_MESSAGE_TABLE . " " .
                "(message, timeToDie, referencedCredential, senderId, externalKey, inserted_timestamp) VALUES " .
                "(:message, :timeToDie, :referencedCredential, :senderId, 0, :now)";

            $values = array();
            $values[':message'] = $message; // save the placeholder
            $values[':timeToDie'] = strtotime($timeToDie);
            $values[':referencedCredential'] = $credential;
            $values[':senderId'] = $sender;
            $values[':now'] = EnsoShared::now();

            $db = new EnsoDB();
            $db->prepare($sql);
            $db->execute($values);
            $db->closeCursor();

            $sql = "UPDATE  " . self::$EXTERNAL_MESSAGE_TABLE . " " .
                "SET externalKey = md5(idExternalMessage) " .
                "WHERE idExternalMessage = :idExternalMessage; ";

            $values = array();
            $values['idExternalMessage'] = $db->getDB()->lastInsertId();

            $db->prepare($sql);
            $db->execute($values);
            $db->closeCursor();

            $sql = "SELECT externalKey FROM " . self::$EXTERNAL_MESSAGE_TABLE . " " .
                "WHERE idExternalMessage = :idExternalMessage";

            $db->prepare($sql);
            $db->execute($values);

            $externalKey = $db->fetch()['externalKey'];

            return $externalKey;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getInboxCount($receiver)
    {
        $sql = "SELECT COUNT(idMessages) AS numero FROM " . self::$MESSAGE_TABLE . " WHERE receiverId = :receiver ";

        $values = array();
        $values['receiver'] = $receiver;

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
}
