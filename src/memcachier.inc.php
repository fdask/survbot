<?php
require '../vendor/autoload.php';

use fdask\surveybot\Settings;

// create a new persistent client
$m = new Memcached("memcached_pool");
$m->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

// some nicer default options
$m->setOption(Memcached::OPT_NO_BLOCK, true);
$m->setOption(Memcached::OPT_AUTO_EJECT_HOSTS, true);
$m->setOption(Memcached::OPT_CONNECT_TIMEOUT, 2000);
$m->setOption(Memcached::OPT_POLL_TIMEOUT, 2000);
$m->setOption(Memcached::OPT_RETRY_TIMEOUT, 2);

// setup authentication
$user = (getenv("MEMCACHIER_USERNAME") ? getenv("MEMCACHIER_USERNAME") : Settings::getIniValue('memcached', 'username'));
$pass = (getenv("MEMCACHIER_PASSWORD") ? getenv("MEMCACHIER_PASSWORD") : Settings::getIniValue('memcached', 'password'));
$servers = (getenv("MEMCACHIER_SERVERS") ? getenv("MEMCACHIER_SERVERS") : Settings::getIniValue('memcached', 'servers'));

$m->setSaslAuthData($user, $pass);

// We use a consistent connection to memcached, so only add in the
// servers first time through otherwise we end up duplicating our
// connections to the server.
if (!$m->getServerList()) {
	// parse server config
	$bits = explode(",", $servers);

	foreach ($bits as $s) {
		$parts = explode(":", $s);

		$m->addServer($parts[0], $parts[1]);
	}
}
