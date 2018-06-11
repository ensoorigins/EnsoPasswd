<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class PermissionModel extends Entity
{

    protected static $table = 'Permissions';

    protected static $columns = [
        "folder",
        "hasAdmin",
        "userId"
    ];

    public static function hasPermissionToSeeFolder($who, $idFolder)
    {
        $idFolder = FolderModel::getTopMostFolderTo($idFolder);

        if (!PermissionModel::exists(['userId' => $who, 'folder' => $idFolder]))
            throw new PermissionDeniedException($who, "has no permission to see folder $idFolder");
    }

    public static function hasPermissionToAdminFolder($who, $idFolder)
    {
        $idFolder = FolderModel::getTopMostFolderTo($idFolder);

        if (!PermissionModel::exists(['userId' => $who, 'folder' => $idFolder, "hasAdmin" => 1]))
            throw new PermissionDeniedException($who, "has no permission to admin folder $idFolder");
    }

    public static function getWhere($filters, $attributes = null, $range = null)
    {
        if (array_key_exists("folder", $filters))
            $filters['folder'] = FolderModel::getTopMostFolderTo($filters['folder']);

        return parent::getWhere($filters, $attributes, $range);;
    }
}
