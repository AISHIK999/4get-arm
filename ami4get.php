<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "data/config.php";

// get request count in last 24 hours
$bot_requests = 0;
$real_requests = 0;
$time = time();

for($i=0; $i<24; $i++){
	
	$bot_requests += apcu_fetch(intdiv($time - (3600 * $i), 3600) . ".bot_requests");
}

for($i=0; $i<24; $i++){
	
	$real_requests += apcu_fetch(intdiv($time - (3600 * $i), 3600) . ".real_requests");
}

echo json_encode(
	[
		"status" => "ok",
		"service" => "4get",
		"server" => [
			"name" => config::SERVER_NAME,
			"description" => config::SERVER_LONG_DESCRIPTION,
			"bot_protection" => config::BOT_PROTECTION,
			"real_requests" => $real_requests,
			"bot_requests" => $bot_requests,
			"api_enabled" => config::API_ENABLED,
			"alt_addresses" => config::ALT_ADDRESSES,
			"version" => config::VERSION
		],
		"instances" => config::INSTANCES
	]
);
