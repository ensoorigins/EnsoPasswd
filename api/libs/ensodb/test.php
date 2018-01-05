<?php
require 'include.php';

echo "INIT\n";

try {
	$db = new EnsoDB ();
	$db->prepare ( "SELECT name FROM ic_access WHERE name = :name" );
	$values = array ();
	$values [':name'] = 'anamelo@inova-em.pt';
	$db->execute ( $values );
	
	$row = $db->fetchAll ();
	print_r ( $row );
} catch ( PDOException $e ) {
	echo "FALHOU\n";
	print_r ( $e );
}

echo "CORREU\n";