<?php
require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use fdask\surveybot\Settings;
use fdask\surveybot\FB;

$config = [
	'settings' => [
		'displayErrorDetails' => true
	]
];

$app = new \Slim\App($config);

$app->get('/', function (Request $r, Response $res) {
	$qs = $r->getQueryParams();

	$res->getBody()->write($qs['hub_challenge']);
});

$app->get('/privacypolicy', function (Request $r, Response $res) {
	$res->getBody()->write(file_get_contents("pp.html"));
});

$app->post('/', function (Request $req, Response $res) {
	$parsedBody = $req->getParsedBody();

	// parse out the message and send a reply!
	$senderId = $parsedBody['entry'][0]['messaging'][0]['sender']['id'];
	$message = $parsedBody['entry'][0]['messaging'][0]['message']['text'];

	error_log("User $senderId said '$message'");

	/**
	if (FB::sendMessage($senderId, "BEEP!")) {
		error_log("Reply successfully sent");
	} else {
		error_log("Error sending reply!");
	}
	**/
});

$app->run();
