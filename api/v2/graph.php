<?php

date_default_timezone_set('America/Toronto');
include "../../data/config.php";

function get_requests($slug){
	
	$time = time();
	$requests = [];
	
	for($i=0; $i<73; $i++){
		
		$data = apcu_fetch(intdiv($time - (3600 * $i), 3600) . ".$slug");
		
		$requests[] = [$i, ($data === false ? 0 : $data)];
	}
	
	// rtrim() out all entries that are 0
	$out = [];
	$found = false;
	for($i=count($requests) - 1; $i>=0; $i--){
		
		if($requests[$i][1] !== 0){
			
			$found = $i;
			break;
		}
	}
	
	if($found === false){
		
		return [];
	}
	
	$requests = array_slice($requests, 0, $found + 1);
	
	return $requests;
}

$bot_requests = get_requests("bot_requests");
$real_requests = get_requests("real_requests");

$real_reqs_24h = 0;
for($i=0; $i<count($real_requests); $i++){
	
	if($real_requests[$i][1] !== false){
		
		$real_reqs_24h += $real_requests[$i][1];
	}
}

$real_reqs_72h = 0;
for($i=0; $i<count($real_requests); $i++){
	
	if($real_requests[$i][1] !== false){
		
		$real_reqs_72h += $real_requests[$i][1];
	}
}

header("Content-Type: image/png");

include "../../lib/graph_gen.php";
$graph = new graph();

echo $graph->render([
	"title" => "Number of requests within the last 72 hours (non-cummulative)",
	"subtitle" => date("jS M y @ g:ia", time()) . " || 24h=" . number_format($real_reqs_24h) . ", 72h=" . number_format($real_reqs_72h) . " || " . config::SERVER_NAME,
	"x_label" => "<= Hours passed =>",
	"y_label" => "<= Request count/hour =>",
	"grad_x" => 25,
	"grad_y" => 20,
	"data" => [
		"Bot requests" => $bot_requests,
		"Real requests" => $real_requests,
	]
]);
