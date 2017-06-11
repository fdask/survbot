<?php
session_start();

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

// testing out the session handling
$app->get('/session', function (Request $req, Response $res) {
	if (!isset($_SESSION['count'])) {
		$_SESSION['count'] = 1;
	} else {
		$_SESSION['count']++;
	}

	$res->getBody()->write("Count is " . $_SESSION['count']);
});

$app->get('/sd', function (Request $req, Response $res) {
	session_destroy();

	$res->getBody()->write("Session is destroyed!");
});

$app->post('/', function (Request $req, Response $res) {
	// get the pageId we're hooked up to!
	$pageId = Settings::get_ini_value('facebook', 'page_id');

	// parse out the posted message
	$parsedBody = $req->getParsedBody();

	// parse out the message and send a reply!
	if (isset($parsedBody['entry']) && !empty($parsedBody['entry'])) {
		foreach ($parsedBody['entry'] as $entry) {
			if (isset($entry['messaging']) && !empty($entry['messaging'])) {
				foreach ($entry['messaging'] as $message) {
					if (isset($message['message'])) {
						$recipientId = $message['recipient']['id'];

						if ($recipientId == $pageId) {
							// see if we already have a session for this user!
							
							$senderId = $message['sender']['id'];
							$message = $message['message']['text'];

							error_log("User $senderId said '$message'");

							if (FB::sendMessage($senderId, "BEEP!")) {
								error_log("Reply successfully sent");
							} else {
								error_log("Error sending reply!");
							}
						} else {
							error_log("Message is addressed to $recipientId!  We're looking for ones addressed to $pageId");
						} 
					} else {
						error_log("'message' isnt set in the message variable!");
					}
				}
			} else {
				error_log("'messaging' isnt set in the entry variable!");
			}
		}
	} else {
		error_log("'entry' isnt set in the parsedBody variable!");
	}
});

$app->run();
