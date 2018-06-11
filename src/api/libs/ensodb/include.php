<?php
require 'config.php';
require 'iEntity.php';
require 'EntityException.php';
require 'Entity.php';

class EnsoDB{
	
	private static $ENSO_DB_VERSION = "2.1.1";
	
	private $dbConn = null;
	private $queryExecute = null;
	
	public function __construct(){
		global $databaseConfig;
		$this->dbConn = new PDO(
			$databaseConfig['database_type'] . ':host=' . $databaseConfig['server'] . ';port=' . $databaseConfig['port'] . ';dbname=' . $databaseConfig['database_name'],
			$databaseConfig['username'],
			$databaseConfig['password']); 
		
		$this->dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function getDB(){
		return $this->dbConn;		
	}

	public function getQuery(){
		return $this->queryExecute;		
	}
	
	public function prepare($sql){
		$this->queryExecute = $this->dbConn->prepare($sql);
	}
	
	public function execute($values = array()){
		$this->queryExecute->execute($values);
	}
	
	public function fetchAll($mode = PDO::FETCH_ASSOC){
		$results = $this->queryExecute->fetchAll($mode);
		$this->queryExecute->closeCursor();
		return $results;
	}
	
	public function fetch($mode = PDO::FETCH_ASSOC){
		$result =  $this->queryExecute->fetch($mode);
		$this->queryExecute->closeCursor();
		return $result;
	}
	
	public function closeCursor(){
	    $this->queryExecute->closeCursor();
	}
}
