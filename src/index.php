<?php
require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config = [
	'settings' => [
		'displayErrorDetails' => true
	]
];

$app = new \Slim\App($config);

$app->get('/hello/{name}', function (Request $request, Response $response) {
	$name = $request->getAttribute('name');
	$response->getBody()->write("Hello, $name");

	return $response;
});

$app->run();
