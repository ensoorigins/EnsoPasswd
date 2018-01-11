<?php
class Folders
{
    /* Errors
     * 1 - Pasta com este nome já existe
     * 2 - Nome é obrigatório
     * 3 - Tentativa de atribuição de permissões a utilizador não existente
     */

    public static function getRootFolders()
    {

        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');


        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        $string = '%' . $req->get("search") . '%';

        /* 4. executar operações */

        $listaDeFolders = FolderModel::getRootFolders($string);

        foreach ($listaDeFolders as &$folder) {

            $folder['credentialChildren'] = count(CredentialModel::getCredentialsBelongingToFolder($folder['idFolders']));
            $folder['folderChildren'] = count(FolderModel::getAllChildsOf($folder['idFolders'], '%'));
        }

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $listaDeFolders);
    }

    public static function addNewFolder()
    {
        $req = ensoGetRequest();

        $key = $req->post('sessionkey');
        $authusername = $req->post('authusername');
        $parent = $req->post('folderId');
        $name = $req->post('name');
        $permissions = $req->post('permissions');


        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        if (!PermissionModel::hasPermissionToAdminFolder($authusername, $parent) && $parent != null) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        if ($name === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);

        $name = trim($name);

        if (FolderModel::folderExists($name, $parent))
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);

        if (count($permissions) > 0) {
            foreach ($permissions as $userId => $hasAdmin) {
                if (UserModel::userExists($userId) === false) {
                    return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);
                }
            }
        }


        /* 4. executar operações */

        $idFolder = FolderModel::addFolder($name, $authusername, $parent);

        if ($idFolder === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add a new folder, operation failed.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Impossivel aceder a pasta");
        }
        if (count($permissions) == 0) {
            EnsoLogsModel::addEnsoLog($authusername, "Folder $idFolder was created without permissions, is this a bug?", EnsoLogsModel::$ERROR, 'Folder');
        } else {

            if (PermissionModel::cleanPermissionsFromFolder($idFolder) === false) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to remove permissions from folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Permissões não limpas");
            }

            foreach ($permissions as $userId => $hasAdmin) {
                if (PermissionModel::addNewPermission($idFolder, $hasAdmin, $userId) === false) {
                    EnsoLogsModel::addEnsoLog($authusername, "Tried to add a new folder $idFolder, operation was interrutpted due to an error creating permissions ", EnsoLogsModel::$ERROR, "Folder");
                    return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Permissão não criada");
                }
            }
        }

        EnsoLogsModel::addEnsoLog($authusername, "Folder with id $idFolder was created successfully.", EnsoLogsModel::$INFORMATIONAL, "Folder");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }

    public static function getFolderById()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');
        $id = $req->get('folderId');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */

        /* 4. executar operações */

        $infoFolder = FolderModel::getFolderById($id);

        if ($infoFolder === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to access folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter a pasta");
        }

        if ($infoFolder['parent'] == null) { // se for pasta na root ou tem manageRootFolders ou é folderadmin dela
            if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders') && !PermissionModel::hasPermissionToSeeFolder($authusername, $id)) {
                return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }
        } else { // se não for root então tem de ter fodleradmin
            if (!PermissionModel::hasPermissionToSeeFolder($authusername, $id)) {
                return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }
        }

        $permissions = PermissionModel::getFolderPermissions($id);

        if ($permissions === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to access permissions of folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter permissões");
        }

        foreach ($permissions as &$perm) {
            if (EnsoRBACModel::checkUserHasAction($perm['userId'], 'manageRootFolders'))
                $perm['sysadmin'] = 1;
            else
                $perm['sysadmin'] = 0;
        }

        EnsoLogsModel::addEnsoLog($authusername, "Folder $id was accessed.", EnsoLogsModel::$INFORMATIONAL, "Folder");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, ['folderInfo' => $infoFolder, 'permissions' => $permissions]);
    }

    public static function removeFolder()
    {
        $req = ensoGetRequest();

        $key = $req->delete('sessionkey');
        $authusername = $req->delete('authusername');

        $id = $req->delete('folderId');

        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        /* 3. validação de inputs */



        $infoFolder = FolderModel::getFolderById($id);

        if ($infoFolder === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove folder $id, operation failed because no records of this folder were found.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter a pasta");
        }

        if ($infoFolder['parent'] == null) { // se for pasta na root ou tem manageRootFolders ou é folderadmin dela
            if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders') && !PermissionModel::hasPermissionToAdminFolder($authusername, $id)) {
                return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }
        } else { // se não for root então tem de ter fodleradmin
            if (!PermissionModel::hasPermissionToAdminFolder($authusername, $id)) {
                return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }
        }

        /* 4. executar operações */

        $children = FolderModel::getChildFoldersOnAllLevels($id);

        for ($i = count($children) - 1; $i >= 0; $i--) {

            if (!CredentialModel::removeCredentialsOfFolder($children[$i]['idFolders'])) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to remove credential during removal of folder, operation failed.", EnsoLogsModel::$ERROR, "Folder");
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falhou remoção de credenciais");
            }

            if (!FolderModel::removeFolder($children[$i]['idFolders'])) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to remove child folder " . $children[$i] . " of $id.", EnsoLogsModel::$ERROR, "Folder");
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falhou remoção de folder");
            }
        }

        if ($infoFolder['parent'] === null) { //É filha de root, eliminar permissões também
            if (!PermissionModel::cleanPermissionsFromFolder($id)) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to remove permissions from folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falharam a remoção de permissões");
            }
        }

        if (!CredentialModel::removeCredentialsOfFolder($id)) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove credentials of folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falhou remoção de credenciais");
        }

        if (!FolderModel::removeFolder($id)) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Falhou remoção de folder");
        }

        EnsoLogsModel::addEnsoLog($authusername, "Folder $id removed.", EnsoLogsModel::$INFORMATIONAL, "Folder");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, '');
    }

    public static function getFolderPath()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');

        $id = $req->get('folderId');


        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        if (!PermissionModel::hasPermissionToSeeFolder($authusername, $id) && $id != null) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        /* 3. validação de inputs */

        /* 4. executar operações */

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, self::walkAndReturnPath($id));
    }

    private static function walkAndReturnPath($id)
    {
        $path = array();

        if ($id != null) {
            while (true) {

                $folder = FolderModel::getFolderById($id);

                array_unshift($path, $folder);

                if ($folder['parent'] == null) {
                    break;
                } else {
                    $id = $folder['parent'];
                }
            }
        }

        return $path;
    }

    public static function getFolderContentsOnSameLevel()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');

        $id = $req->get('folderId'); //opcional se não existir assume root
        
        
                /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }
        
                /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        if (!PermissionModel::hasPermissionToSeeFolder($authusername, $id) && $id != null) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }
        
                /* 3. validação de inputs */

        $string = '%';
        
                /* 4. executar operações */

        $childFolders = array();
        $credentials = array();

        if ($id != null) {
            $childFolders = FolderModel::getAllChildsOf($id, $string);
            $credentials = CredentialModel::getCredentialsBelongingToFolder($id);
        } else {
            $childFolders = FolderModel::getRootFoldersAsSeenBy($string, $authusername);
        }

        foreach ($childFolders as &$folder) {
            $folder['credentialChildren'] = count(CredentialModel::getCredentialsBelongingToFolder($folder['idFolders']));
            $folder['folderChildren'] = count(FolderModel::getAllChildsOf($folder['idFolders'], '%'));
        }

                /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, ["folders" => $childFolders, "credentials" => $credentials, "search" => $string]);
    }

    public static function getFolderContentsRecursively()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');
        $string = $req->get('search');
        $id = $req->get('folderId'); //opcional se não existir assume root
        
        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }
        
                /* 2. autorização - validação de permissões */

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents')) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }

        if (!PermissionModel::hasPermissionToSeeFolder($authusername, $id) && $id != null) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
        }
        
        /* 3. validação de inputs */

        
        /* 4. executar operações */

        $childFolders = array();
        $matchedFolders = array();
        $credentials = array();

        $childFolders = FolderModel::getChildFoldersOnAllLevels($id);

        if ($id != null) {
            array_push($childFolders, FolderModel::getFolderById($id));
        }

        $termos = explode(' ', trim($string));

        for($i = 0; $i < count($termos); $i++)
            $termos[$i] = '%' . $termos[$i] . '%';
        

        foreach ($childFolders as $folder) {
            if (PermissionModel::hasPermissionToSeeFolder($authusername, $folder['idFolders']) === true) {
                foreach ($termos as $termo) {
                    if (strpos($folder['name'], trim($termo, "%")) !== false) {
                        $folder['credentialChildren'] = count(CredentialModel::getCredentialsBelongingToFolder($folder['idFolders']));
                        $folder['folderChildren'] = count(FolderModel::getAllChildsOf($folder['idFolders'], '%'));
                        array_push($matchedFolders, $folder);
                        break;
                    }
                }

                foreach (CredentialModel::getCredentialsBelongingToFolder($folder['idFolders'], $termos) as $cred) {
                    $cred['path'] = self::walkAndReturnPath($folder['idFolders']);
                    array_push($credentials, $cred);
                }
            }
        }
        

        
        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, ["folders" => $matchedFolders, "credentials" => $credentials, "search" => $string]);
    }

    public static function getFolderContents()
    {
        $req = ensoGetRequest();

        if (trim(trim($req->get('search')), '%') == "") {
            return self::getFolderContentsOnSameLevel();
        } else {
            return self::getFolderContentsRecursively();
        }
    }

    public static function editFolder()
    {
        $req = ensoGetRequest();

        $key = $req->put('sessionkey');
        $authusername = $req->put('authusername');
        $name = $req->put('name');
        $id = $req->put('folderId');
        $permissions = $req->put('permissions');


        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        $infoFolder = FolderModel::getFolderById($id);

        if ($infoFolder === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit folder $id, operation failed because no records of this folder were found.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter a pasta");
        }

        if ($infoFolder['parent'] == null) { // se for pasta na root ou tem manageRootFolders ou é folderadmin dela
            if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders') && !PermissionModel::hasPermissionToAdminFolder($authusername, $id)) {
                return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }
        } else { // se não for root então tem de ter fodleradmin
            if (!PermissionModel::hasPermissionToAdminFolder($authusername, $id)) {
                return ensoSendResponse(EnsoShared::$ENSO_REST_FORBIDDEN, "");
            }
        }

        /* 3. validação de inputs */

        if ($name === "")
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 2);

        if (FolderModel::folderExists($name, $infoFolder['parent']) && $infoFolder['name'] != $name)
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 1);

        if (count($permissions) > 0) {
            foreach ($permissions as $userId => $hasAdmin) {
                if (UserModel::userExists($userId) === false) {
                    return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, 3);
                }
            }
        }

        /* 4. executar operações */

        if (!FolderModel::editFolder($name, $id)) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to edit folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Folder não editada");
        }

        if (PermissionModel::cleanPermissionsFromFolder($id) === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to remove permissions from folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Permissões não limpas");
        }

        foreach ($permissions as $userId => $hasAdmin) {

            if (PermissionModel::addNewPermission($id, $hasAdmin, $userId) === false) {
                EnsoLogsModel::addEnsoLog($authusername, "Tried to add permissions to folder $id, operation failed.", EnsoLogsModel::$ERROR, "Folder");
                return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Permissão não criada");
            }
        }

        EnsoLogsModel::addEnsoLog($authusername, "Edited folder $id.", EnsoLogsModel::$NOTICE, "Folder");

        /* 5. response */

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, "");
    }

    public static function getTreeView()
    {
        $req = ensoGetRequest();

        $key = $req->get('sessionkey');
        $authusername = $req->get('authusername');
        
        /* 1. autenticação - validação do token */

        if (AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername) === false) {
            return ensoSendResponse(EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        }

        /* 2. autorização - validação de permissões */

        $treeView = [];

        $rootFolders = FolderModel::getRootFoldersAsSeenBy("%", $authusername);

        if ($rootFolders === false) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to access root folders, operation failed because no records of these folders were found.", EnsoLogsModel::$ERROR, "Folder");
            return ensoSendResponse(EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "Erro a obter a pastas");
        }

        foreach ($rootFolders as $value) {
            $currentNode = ['id' => $value['idFolders'], 'name' => $value['name'], 'credentials' => [], 'childFolders' => []];

            self::generateTreeView($currentNode);


            array_push($treeView, $currentNode);
        }

        return ensoSendResponse(EnsoShared::$ENSO_REST_OK, $treeView);


    }

    private static function generateTreeView(&$parentNode)
    {
        $childs = FolderModel::getAllChildsOf($parentNode['id'], "%");

        foreach ($childs as $value) {

            $currentNode = ['id' => $value['idFolders'], 'name' => $value['name'], 'credentials' => [], 'childFolders' => []];

            self::generateTreeView($currentNode);

            array_push($parentNode['childFolders'], $currentNode);
        }

        $parentNode['credentials'] = CredentialModel::getCredentialsBelongingToFolder($parentNode['id']);
    }
}

//Area Sysadmin
$app->get('/sysadmin/folders/', 'Folders::getRootFolders');

//Utils
$app->get('/folder/getPath/', 'Folders::getFolderPath');

//Normal user
$app->get('/folders/', 'Folders::getFolderContents');
$app->get('/folder/', 'Folders::getFolderById');
$app->post('/folder/', 'Folders::addNewFolder');
$app->put('/folder/', 'Folders::editFolder');
$app->delete('/folder/', 'Folders::removeFolder');
$app->get('/folderTreeView/', 'Folders::getTreeView');