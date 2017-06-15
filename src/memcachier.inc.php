<?php
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
$m->setSaslAuthData(getenv("MEMCACHIER_USERNAME"), getenv("MEMCACHIER_PASSWORD"));

// We use a consistent connection to memcached, so only add in the
// servers first time through otherwise we end up duplicating our
// connections to the server.
if (!$m->getServerList()) {
	// parse server config
	$servers = explode(",", getenv("MEMCACHIER_SERVERS"));

	foreach ($servers as $s) {
		$parts = explode(":", $s);
		$m->addServer($parts[0], $parts[1]);
	}
}
