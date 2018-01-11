<?php
require 'enso.conf.php';
require 'passwd.conf.php';

require 'vendor/autoload.php';
$app = new \Slim\Slim ();

// carregamento de libs
foreach ( scandir ( './libs/' ) as $dirname ) {
	$path = './libs/' . $dirname;
	
	if (is_dir ( $path ) && file_exists ( $path . '/include.php' )) {
		require $path . '/include.php';
	}
}

// carregamento de controladores
foreach ( scandir ( './controllers.autoload/' ) as $filename ) {
	$path = './controllers.autoload/' . $filename;
	
	if (is_file ( $path ) && ! strcmp ( pathinfo ( $path, PATHINFO_EXTENSION ), "php" )) {
		require $path;
	}
}

// carregamento de models
foreach ( scandir ( './controllers.autoload/models' ) as $filename ) {
	$path = './controllers.autoload/models/' . $filename;
	
	if (is_file ( $path ) && ! strcmp ( pathinfo ( $path, PATHINFO_EXTENSION ), "php" )) {
		require $path;
	}
}

$app->contentType ( 'application/json; charset=utf-8' );
$app->run ();

function ensoSendResponseRaw($responseCode, $responseBody) {
	global $app;
	$res = $app->response ();
	
	$res->status ( $responseCode );
	$res->setBody ( $responseBody );
}

function ensoSendResponse($responseCode, $responseBody) {
	ensoSendResponseRaw ( $responseCode, json_encode ( $responseBody ) );
	return true;
}

function ensoGetRequest() {
	global $app;
	return $app->request ();
}
