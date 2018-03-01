<?php
require 'enso.conf.php';
require 'passwd.conf.php';

require 'vendor/autoload.php';
$app = new \Slim\App;

// carregamento de libs
foreach (scandir('./libs/') as $dirname) {
	$path = './libs/' . $dirname;

	if (is_dir($path) && file_exists($path . '/include.php')) {
		require $path . '/include.php';
	}
}

foreach (scandir('./controllers.autoload/includes/') as $filename) {
	$path = './controllers.autoload/includes/' . $filename;

	if (is_file($path) && !strcmp(pathinfo($path, PATHINFO_EXTENSION), "php")) {
		require $path;
	}
}

// carregamento de controladores
foreach (scandir('./controllers.autoload/') as $filename) {
	$path = './controllers.autoload/' . $filename;

	if (is_file($path) && !strcmp(pathinfo($path, PATHINFO_EXTENSION), "php")) {
		require $path;
	}
}

// carregamento de models
foreach (scandir('./controllers.autoload/models') as $filename) {
	$path = './controllers.autoload/models/' . $filename;

	if (is_file($path) && !strcmp(pathinfo($path, PATHINFO_EXTENSION), "php")) {
		require $path;
	}
}

$app->run();

function ensoSendResponse($responseObj, $responseCode, $responseBody)
{
	$responseObj = $responseObj->withHeader('Content-type', 'application/json');
	$responseObj = $responseObj->withStatus($responseCode);
	$responseObj->getBody()->rewind();
	for ($i = 0; $i < $responseObj->getBody()->getSize(); $i++)
		$responseObj->getBody()->write(' ');
	$responseObj->getBody()->rewind();
	$responseObj->getBody()->write(json_encode($responseBody));

	return $responseObj;
}

