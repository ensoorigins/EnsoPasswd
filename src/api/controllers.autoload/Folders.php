<?php
class Folders
{
    /* Errors
     * 1 - Pasta com este nome já existe
     * 2 - Nome é obrigatório
     * 3 - Tentativa de atribuição de permissões a utilizador não existente
     */

    public static function getRootFolders($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            if (EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders') === false)
                throw new RBACDeniedException();

            $string = '%' . trim(Input::validate($request->getParam("search"), Input::$STRING)) . '%';

            $listaDeFolders = FolderModel::getWhere(
                [
                    'parent' => ["IS", null]
                ]
            );

            foreach ($listaDeFolders as &$folder) {
                $folder['credentialChildren'] = count(CredentialModel::getWhere(['belongsToFolder' => $folder['idFolders']], ["idCredentials", "title", "createdById"]));
                $folder['folderChildren'] = count(FolderModel::getWhere(["parent" => $folder['idFolders']]));
            }

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $listaDeFolders);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get root folders, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get root folders, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get root folders, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function addNewFolder($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $parent = $request->getParam('folderId');
            if ($parent != null)
                $parent = Input::validate($parent, Input::$INT, 0, FolderModel::class, 'idFolders');

            $name = Input::validate($request->getParam('name'), Input::$STRICT_STRING, 2);

            if (FolderModel::exists(['name' => $name, 'parent' => $parent]))
                throw new BadInputValidationException(1);

            $permissions = $request->getParam('permissions');

            if (count($permissions) > 0)
                foreach ($permissions as $userId => $hasAdmin)
                Input::validate($userId, Input::$STRICT_STRING, 3, UserModel::class, 'username');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents'))
                throw new RBACDeniedException();

            if ($parent != null)
                PermissionModel::hasPermissionToAdminFolder($authusername, $parent);
            else if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders'))
                throw new RBACDeniedException();

            $idFolder = FolderModel::insert([
                "name" => $name,
                "createdById" => $authusername,
                "parent" => $parent
            ]);

            if (count($permissions) == 0) {
                EnsoLogsModel::addEnsoLog($authusername, "Folder $idFolder was created without permissions, is this a bug?", EnsoLogsModel::$ERROR, 'Folder');
            } else {
                PermissionModel::delete(["folder" => $idFolder]);

                foreach ($permissions as $userId => $hasAdmin) {
                    PermissionModel::insert(['folder' => $idFolder, "hasAdmin" => $hasAdmin, "userId" => $userId]);
                }
            }

            EnsoLogsModel::addEnsoLog($authusername, "Folder with id $idFolder was created successfully.", EnsoLogsModel::$INFORMATIONAL, "Folder");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $title, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $title, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $title, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getFolderById($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $id = Input::validate($request->getParam('folderId'), Input::$INT, 0, FolderModel::class, 'idFolders');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            $infoFolder = FolderModel::getWhere(['idFolders' => $id])[0];

            if ($infoFolder['parent'] == null) {
                if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders'))
                    throw new RBACDeniedException();

                PermissionModel::hasPermissionToSeeFolder($authusername, $id);
            } else
                PermissionModel::hasPermissionToSeeFolder($authusername, $id);

            $permissions = PermissionModel::getWhere(['folder' => $id]);

            foreach ($permissions as &$perm) {
                if (EnsoRBACModel::checkUserHasAction($perm['userId'], 'manageRootFolders'))
                    $perm['sysadmin'] = 1;
                else
                    $perm['sysadmin'] = 0;
            }

            EnsoLogsModel::addEnsoLog($authusername, "Folder $id was accessed.", EnsoLogsModel::$INFORMATIONAL, "Folder");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, ['folderInfo' => $infoFolder, 'permissions' => $permissions]);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get folder $id, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $id, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $id, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function removeFolder($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $id = Input::validate($request->getParam('folderId'), Input::$INT, 0, FolderModel::class, 'idFolders');

        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            $infoFolder = FolderModel::getWhere(['idFolders' => $id])[0];

            if ($infoFolder['parent'] == null) {
                if (!EnsoRBACModel::checkUserHasAction($authusername, 'manageRootFolders'))
                    throw new RBACDeniedException();
                else
                    PermissionModel::hasPermissionAdminFolder($authusername, $id);
            } else
                PermissionModel::hasPermissionToAdminFolder($authusername, $id);

            $children = FolderModel::getChildFoldersOnAllLevels($id);

            for ($i = count($children) - 1; $i >= 0; $i--) {
                CredentialModel::delete(['belongsToFolder' => $children[$i]['idFolders']]);

                FolderModel::delete(['idFolders' => $children[$i]['idFolders']]);
            }

            if ($infoFolder['parent'] === null)
                PermissionModel::delete(['folder' => $id]);

            CredentialModel::delete(['belongsToFolder' => $id]);

            FolderModel::delete(['idFolders' => $id]);

            EnsoLogsModel::addEnsoLog($authusername, "Folder $id removed.", EnsoLogsModel::$INFORMATIONAL, "Folder");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, '');
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get folder $id, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $id, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $id, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getFolderPath($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $id = $request->getParam('folderId');
            if (!empty($id))
                Input::validate($id, Input::$INT, 0, FolderModel::class, 'idFolders');
            else
                $id = null;

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents'))
                throw new RBACDeniedException();

            if ($id != null)
                PermissionModel::hasPermissionToSeeFolder($authusername, $id);

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, self::walkAndReturnPath($id));
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to get folder $id, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $id, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to add folder $id, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    private static function walkAndReturnPath($id)
    {
        $path = array();

        if ($id != null) {
            while (true) {

                $folder = FolderModel::getWhere(["idFolders" => $id], ["parent", "name"])[0];

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

    public static function getFolderContentsOnSameLevel($request, $response, $args)
    {
        $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
        $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

        $id = $request->getParam('folderId');
        if (!empty($id))
            $id = Input::validate($id, Input::$INT, 0, FolderModel::class, 'idFolders');
        else
            $id = null; 
        
                /* 1. autenticação - validação do token */

        AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents'))
            throw new RBACDeniedException();

        if ($id !== null)
            PermissionModel::hasPermissionToSeeFolder($authusername, $id);

        $childFolders = array();
        $credentials = array();

        if ($id != null) {
            $childFolders = FolderModel::getWhere(['parent' => $id]);
            $credentials = CredentialModel::getWhere(['belongsToFolder' => $id]);
        } else {
            $childFolders = FolderModel::getRootFoldersAsSeenBy($authusername);
        }

        foreach ($childFolders as &$folder) {
            $folder['credentialChildren'] = count(CredentialModel::getWhere(['belongsToFolder' => $folder['idFolders']]));
            $folder['folderChildren'] = count(FolderModel::getWhere(['parent' => $folder['idFolders']]));
        }

        return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, ["folders" => $childFolders, "credentials" => $credentials, "search" => ""]);
    }

    public static function getFolderContentsRecursively($request, $response, $args)
    {
        $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
        $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

        $id = $request->getParam('folderId');
        if (!empty($id))
            $id = Input::validate($id, Input::$INT, 0, FolderModel::class, 'idFolders');
        else
            $id = null;

        $string = Input::validate($request->getParam('search'), Input::$STRICT_STRING);

        AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents'))
            throw new RBACDeniedException();

        if ($id !== null)
            PermissionModel::hasPermissionToSeeFolder($authusername, $id);

        $childFolders = array();
        $matchedFolders = array();
        $credentials = array();

        $childFolders = FolderModel::getChildFoldersOnAllLevels($id);

        if ($id != null) {
            array_push($childFolders, FolderModel::getWhere(['idFolders' => $id])[0]);
        }

        EnsoDebug::var_error_log($childFolders);

        $termos = explode(' ', trim($string));

        for ($i = 0; $i < count($termos); $i++)
            $termos[$i] = '%' . $termos[$i] . '%';

        foreach ($childFolders as $folder) {
            try {
                PermissionModel::hasPermissionToSeeFolder($authusername, $folder['idFolders']);

                EnsoDebug::d("Can see folder");
                EnsoDebug::var_error_log($folder);

                foreach ($termos as $termo) {
                    if (strpos(strtolower($folder['name']), strtolower(trim($termo, "%"))) !== false) {
                        $folder['credentialChildren'] = count(CredentialModel::getWhere(['belongsToFolder' => $folder['idFolders']]));
                        $folder['folderChildren'] = count(FolderModel::getWhere(['parent' => $folder['idFolders']]));
                        array_push($matchedFolders, $folder);
                        break;
                    }
                }

                foreach (CredentialModel::getMatchesBelongingTo($folder['idFolders'], $termos) as $cred) {
                    $cred['path'] = self::walkAndReturnPath($folder['idFolders']);
                    array_push($credentials, $cred);
                }
            } catch (PermissionDeniedException $e) {
                    //no permission, not included in array
            }
        }

        return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, ["folders" => $matchedFolders, "credentials" => $credentials, "search" => $string]);
    }

    public static function getFolderContents($request, $response, $args)
    {
        try {
            if (trim(trim($request->getParam('search')), '%') == "")
                return self::getFolderContentsOnSameLevel($request, $response, $args);
            else
                return self::getFolderContentsRecursively($request, $response, $args);

        } catch (BadInputValidationException $e) {
            EnsoDebug::var_error_log($e);
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function editFolder($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);

            $id = $request->getParam('folderId');
            if ($id != null)
                $id = Input::validate($id, Input::$INT, 0, FolderModel::class, 'idFolders');

            $name = Input::validate($request->getParam('name'), Input::$STRICT_STRING, 2);

            if (FolderModel::exists(['name' => $name, 'parent' => $parent, "idFolders" => ["<>", $id]]))
                throw new BadInputValidationException(1);

            $permissions = $request->getParam('permissions');

            if (count($permissions) > 0)
                foreach ($permissions as $userId => $hasAdmin)
                Input::validate($userId, Input::$STRICT_STRING, 3, UserModel::class, 'username');

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

            if (!EnsoRBACModel::checkUserHasAction($authusername, 'seeFolderContents'))
                throw new RBACDeniedException();

            PermissionModel::hasPermissionToAdminFolder($authusername, $id);

            FolderModel::editWhere(
                [
                    "idFolders" => $id
                ],
                [
                    "name" => $name,
                ]
            );

            if (count($permissions) == 0) {
                EnsoLogsModel::addEnsoLog($authusername, "Folder $id was created without permissions, is this a bug?", EnsoLogsModel::$ERROR, 'Folder');
            } else {
                PermissionModel::delete(["folder" => $id]);

                foreach ($permissions as $userId => $hasAdmin) {
                    PermissionModel::insert(['folder' => $id, "hasAdmin" => $hasAdmin, "userId" => $userId]);
                }
            }

            EnsoLogsModel::addEnsoLog($authusername, "Edited folder $id.", EnsoLogsModel::$NOTICE, "Folder");

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, "");
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    public static function getTreeView($request, $response, $args)
    {
        try {
            $key = Input::validate($request->getParam('sessionkey'), Input::$STRING);
            $authusername = Input::validate($request->getParam('authusername'), Input::$STRING);
        
        /* 1. autenticação - validação do token */

            AuthenticationModel::checkIfSessionKeyIsValid($key, $authusername);

        /* 2. autorização - validação de permissões */

            $treeView = [];

            $rootFolders = FolderModel::getRootFoldersAsSeenBy($authusername);

            foreach ($rootFolders as $value) {
                $currentNode = ['id' => $value['idFolders'], 'name' => $value['name'], 'credentials' => [], 'childFolders' => []];

                self::generateTreeView($currentNode);

                array_push($treeView, $currentNode);
            }

            return ensoSendResponse($response, EnsoShared::$ENSO_REST_OK, $treeView);
        } catch (BadInputValidationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (PermissionDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed due to lack of permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (RBACDeniedException $e) {
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed due to lack of RBAC permissions.", EnsoLogsModel::$NOTICE, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_FORBIDDEN, "");
        } catch (AuthenticationException $e) {
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_NOT_AUTHORIZED, "");
        } catch (Exception $e) {
            EnsoDebug::var_error_log($e);
            EnsoLogsModel::addEnsoLog($authusername, "Tried to search folder, operation failed.", EnsoLogsModel::$ERROR, "Credencial");
            return ensoSendResponse($response, EnsoShared::$ENSO_REST_INTERNAL_SERVER_ERROR, "");
        }
    }

    private static function generateTreeView(&$parentNode)
    {
        $childs = FolderModel::getWhere(['parent' => $parentNode['id']], ["name", "idFolders"]);

        foreach ($childs as $value) {

            $currentNode = ['id' => $value['idFolders'], 'name' => $value['name'], 'credentials' => [], 'childFolders' => []];

            self::generateTreeView($currentNode);

            array_push($parentNode['childFolders'], $currentNode);
        }

        $parentNode['credentials'] = CredentialModel::getWhere(['belongsToFolder' => $parentNode['id']]);
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