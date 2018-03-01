<?php

class FolderModel extends Entity
{
    protected static $table = "Folders";

    protected static $columns = [
        "idFolders",
        "parent",
        "name",
        "createdById"
    ];

    public static function getTopMostFolderTo($id)
    {
        do {
            $currentFolder = FolderModel::getWhere(["idFolders" => $id], ["idFolders", "parent"])[0];

            if ($currentFolder['parent'] != null)
                $id = $currentFolder['parent'];
            else
                return $currentFolder['idFolders'];
        } while (true);
    }

    public static function getRootFoldersAsSeenBy($who, $nameSearch = "%")
    {
        $sql = "SELECT * FROM " . static::$table . "  
                LEFT JOIN Permissions on Folders.idFolders = Permissions.folder
                WHERE Folders.parent IS NULL AND Permissions.userId = :observer AND LCASE(name) LIKE LCASE(:search)";

        $values = array();
        $values[':observer'] = $who; // save the placeholder
        $values[':search'] = $nameSearch;

        $db = new EnsoDB();
        $db->prepare($sql);
        $db->execute($values);

        return $db->fetchAll();
    }

    public static function getChildFoldersOnAllLevels($parent = null)
    {
        $children = array();

        $folders = FolderModel::getWhere(['parent' => $parent]);

        foreach ($folders as $value) {
            array_push($children, $value);

            $children = array_merge($children, self::getChildFoldersOnAllLevels($value['idFolders']));
        }

        return $children;
    }
}