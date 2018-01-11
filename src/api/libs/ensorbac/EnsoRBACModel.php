<?php

class EnsoRBACModel {
	
	private static $ENSO_RBAC_VERSION = "5.0.0";
	
	/**
	 * Obter listas de Roles de um determinado utilizador
	 *
	 * @param $userId - Id do utilizador do qual vamos obter a lista de Roles
	 *
	 * @return FALSE - Caso não existam quaisquer roles associadas ao utilizador especificado
	 * @return Lista de Roles - array(array('rolename'=>rolename,'id_role'=>id_role))
	 *
	 * */
	public static function getUserRoles($userId){
		
		$sql = "SELECT enso_user_roles.enso_role_name_enso_roles " .
				"FROM enso_roles, enso_user_roles " .
				"WHERE enso_user_roles.id_user = :userid " .
				"AND enso_user_roles.deleted_timestamp IS NULL " .
				"AND enso_roles.deleted_timestamp IS NULL " .
				"AND enso_user_roles.enso_role_name_enso_roles = enso_roles.enso_role_name";
		
		$values = array(
			':userid' => $userId
		);
		
		try{
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute($values);
			 
			$row = $db->fetchAll();
			
			//caso exista construir o array com os valores a retornar;
			$ret = array();
			foreach ($row as $role){
				array_push($ret, $role['enso_role_name_enso_roles']);
			}
				
			//retorno do valor
			return $ret;
		}catch (PDOException $e){
			return false;
		}
	}


	/**
	 * Verificar se um determinado user pode executar uma determinada action
	 *
	 * @param $userId - Id do User
	 * @param $actionName - Nome da Action
	 *
	 * @return FALSE se o userid não existe ou se o mesmo existe e não detem nenhuma role que permita executar a action
	 * TRUE caso a action possa ser executada pelo user;
	 *
	 * */
	public static function checkUserHasAction($userId, $actionName){
		$user_roles=self::getUserRoles($userId);
		foreach ($user_roles as $role){
			if (self::checkRoleHasAction($role, $actionName))
				return TRUE;
		}
		return FALSE;
	}
	
	
	/**
	 * Verificar se um determinado role pode executar uma determinada action
	 *
	 * @param $roleName - Nome do role
	 * @param $actionName - Nome da Action
	 *
	 * @return FALSE se o role não detem permissões sobre a action pedida
	 * TRUE caso a action possa ser executada pelo role;
	 *
	 * */
	private static function checkRoleHasAction($roleName, $actionName){

		$sql = "SELECT enso_role_name_enso_roles " .
				"FROM enso_actions_roles " .
				"WHERE enso_role_name_enso_roles = :enso_role_name_enso_roles " .
				"AND  enso_action_name_enso_actions = :enso_action_name_enso_actions " .
				"AND deleted_timestamp IS NULL ";
		
		$values = array(
			':enso_role_name_enso_roles' => $roleName,
			':enso_action_name_enso_actions' => $actionName
		);
		
		try{
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute($values);
		
			$row = $db->fetchAll();
			
			if (sizeof($row) != 1)
				return false;
			
			return true;
		}catch (PDOException $e){
			return false;
		}
	}
	
	
	/**
	 * Função para adicionar uma role a um user
	 * 
	 * @param int $userId
	 * @param int $roleId
	 * @return boolean false se não conseguiu adicionar role ao utilizador, true se conseguiu.
	 */
	public static function addRoleToUser($userId, $roleName){
		
		$sql = "INSERT INTO enso_user_roles (inserted_timestamp, enso_role_name_enso_roles, id_user) " .
				"VALUES (:inserted_timestamp, :enso_role_name_enso_roles, :id_user);";
			
		$values = array();
		$values[':inserted_timestamp'] = EnsoShared::now(); // save the placeholder
		$values[':enso_role_name_enso_roles'] = $roleName; // save the placeholder
		$values[':id_user'] = $userId; // save the placeholder

		try{
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute($values);
		
			return true;
		}catch (PDOException $e){
			return false;
		}
	}
	
	/**
	 * Função para remover uma role a um user
	 *
	 * @param int $userId
	 * @param int $roleId
	 * @return boolean false se não conseguiu remover role ao utilizador, true se conseguiu.
	 */
	public static function removeRoleFromUser($userId, $roleName) {
	
		$sql = "DELETE FROM enso_user_roles " .
				"WHERE id_user = :id_user AND enso_role_name_enso_roles = :enso_role_name_enso_roles";
	
		$values = array();
		$values[':enso_role_name_enso_roles'] = $roleName; // save the placeholder
		$values[':id_user'] = $userId; // save the placeholder
	
		
		try{
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute($values);

			return true;
		}catch (PDOException $e){
			return false;
		}
	}
	
	/**
	 * Função para obter todas as ações possiveis ao utilizador
	 *
	 * @param int $userId
	 * @return array com ações possiveis ou false se houver falha na execução, FALSE se não foi possivel executar a query
	 */
	public static function getAvailableUserActions($userId) {
	
		$sql = "SELECT enso_action_name FROM enso_user_roles " .
				"INNER JOIN enso_actions_roles on enso_actions_roles.enso_role_name_enso_roles = enso_user_roles.enso_role_name_enso_roles " .
				"INNER JOIN enso_actions on enso_actions.enso_action_name = enso_actions_roles.enso_action_name_enso_actions " .
				"WHERE enso_user_roles.id_user = :id_user";
	
		$values = array();
		$values[':id_user'] = $userId; // save the placeholder
	
		try{
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute($values);
		
			return $db->fetchAll(PDO::FETCH_COLUMN);
		}catch (PDOException $e){
			return false;
		}
	}
	
	/**
	 * Função para remover todas as roles ao utilizador
	 *
	 * @param int $userId
	 * @return true se a operação foi executada com sucesso;
	 */
	public static function removeAllUserRoles($userId) {
	
		$sql = "DELETE FROM enso_user_roles " .
				"WHERE id_user = :id_user";
	
		$values = array();
		$values[':id_user'] = $userId; // save the placeholder
	
		try{
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute($values);
		
			return true;
		}catch (PDOException $e){
			return false;
		}
	}
}
?>
