<?php
require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use fdask\surveybot\Settings;
use fdask\surveybot\FB;

include 'memcachier.inc.php';

/**
Your current age 
2. Are you currently receiving ssdi or ssi benefits? Must be no 
3. Are you currently our of work or expect to be for a year? Must be yes
4. have you worked at least 5 of the past 10 years? YES / NO (either)
5.Have you been treated by a doctor in the last year?
6. Please describe your case. (response will indicate we will make a call to discuss results and options
7. Request phone # - time to call back (If possible would be great to get city, state, address, zip )
**/

$config = [
	'settings' => [
		'displayErrorDetails' => true
	]
];

$app = new \Slim\App($config);

$app->get('/phpinfo', function (Request $r, Response $res) {
	phpinfo();
});

$app->get('/', function (Request $r, Response $res) {
	$qs = $r->getQueryParams();

	$res->getBody()->write($qs['hub_challenge']);
});

$app->get('/dump', function (Request $r, Response $res) use ($m) {
	$keys = $m->getAllKeys();

	if (!empty($keys)) {
		$data = array();

		foreach ($keys as $key) {
			$data[$key] = $m->get($key);			
		}
	} else {
		error_log("Keys is empty!");
	}

	$res->getBody()->write(print_r($data, true));
});

$app->get('/test', function (Request $r, Response $res) use ($m) {
	$x = $m->get("abc");

	if (!$x) {
		$resp = "No x is set!  Initializing it now";
		$x = array(
			'abc' => "hello"
		);

		if (!$m->set("abc", $x)) {
			$resp .= "\nFailed to write memcache data!";
		}
	} else {
		$resp = print_r($x, true);
	}

	$res->getBody()->write($resp);
});

$app->get('/privacypolicy', function (Request $r, Response $res) {
	$res->getBody()->write(file_get_contents("pp.html"));
});

$app->get('/delete', function (Request $r, Response $res) use ($m) {
	$m->flush();
});

$app->post('/', function (Request $req, Response $res) use ($m) {
	// get the pageId we're hooked up to!
	$pageId = Settings::getIniValue('facebook', 'page_id');

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
							$senderId = $message['sender']['id'];
							$message = $message['message']['text'];

							$key = "survey-$senderId";

							// load up the users previous responses
							$data = $m->get($key);

							/*
							if (!$data || !isset($data['state'])) {
								error_log("No state set.  Starting from ONE.");

								// lets retrieve the users details to save!
								$ret = FB::getUser($senderId);
	
								if ($ret) {
									foreach ($ret as $k => $v) {
										error_log("Setting '$k' to '$v'");

										$data[$k] = $v;
									}
								} else {
									error_log("Facebook API call failed!");
								}

								$data['state'] = 1;								

								error_log(print_r($data, true));
							} 

							switch ($data['state']) {
								case 1:
									$msg = "What is your current age?";

									error_log("Processing state 1\n");

									$data['state']++;

									break;
								case 2:
									// Are you currently receiving ssdi or ssi benefits? Must be no 
									error_log("Processing state 2\n");

									if (preg_match("@(\d+)@", $message, $matches)) {
										$age = (int)$matches[1];

										if ($age >= 18 && $age <= 99) {
											$data['age'] = $age;

											$data['state']++;

											$msg = "Are you currently receiving SSDI or SSI benefits?";
										} else {
											$msg = "The age you provided doesn't look right.  Can you please indicate your age using numbers?  (18-99)";
										}
									} else {
										$msg = "Could not understand your response.  Can you please indicate your age using numbers?  (18-99)";
									}

									break;
								case 3:
									// Are you currently our of work or expect to be for a year? Must be yes
									$data['benefits'] = $message;
									$data['state']++;

									$msg = "Are you currently out of work or expect to be for a year?";

									break;
								case 4:
									// have you worked at least 5 of the past 10 years? YES / NO (either)
									$data['outofwork'] = $message;
									$data['state']++;

									$msg = "Have you worked at least 5 of the past 10 years?";

									break;
								case 5:
									// Have you been treated by a doctor in the last year?
									$data['fiveoften'] = $message;
									$data['state']++;

									$msg = "Have you been treated by a doctor in the last year?";

									break;
								case 6:
									// Please describe your case. (response will indicate we will make a call to discuss results and options
									$data['doctor'] = $message;
									$data['state']++;

									$msg = "Please describe your case.";

									break;
								case 7:
									// Request phone # - time to call back (If possible would be great to get city, state, address, zip )
									$data['case'] = $message;
									$data['state']++;

									$msg = "Please provide your phone number, and an appropriate time to call you!";

									break;
								case 8:
									$data['phone'] = $message;
								
									$msg = "Thank you!";

									break;
								default:
									// we don't know where this user falls!
									error_log("Got a value for data state of '{$data['state']}'");
							}

							error_log(print_r($data, true));

							if (!$m->set($key, $data)) {
								error_log("There was an issue saving the memcache data!");
							}
							*/
							$msg = "response";

							error_log("User $senderId said '$message'");

							if (FB::sendMessage($senderId, $msg)) {
								error_log("Reply successfully sent");
							} else {
								error_log("Error sending reply!");
							}
						} else {
							// error_log("Message is addressed to $recipientId!  We're looking for ones addressed to $pageId");
						} 
					} else {
						// error_log("'message' isnt set in the message variable!");

						// error_log(print_r($message, true));
					}
				}
			} else {
				// error_log("'messaging' isnt set in the entry variable!");
			}
		}
	} else {
		// error_log("'entry' isnt set in the parsedBody variable!");
	}
});

$app->run();
