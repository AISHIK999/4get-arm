<?php

$scrapers = [
	"web" => [
		"DuckDuckGo",
		"Brave",
		"Yandex",
		"Google",
		"Google API",
		"Google CSE",
		"Yahoo! JAPAN",
		"Startpage",
		"Yep",
		"Mwmbl",
		"Mojeek",
		"Naver",
		"Baidu",
		"Cốc Cốc",
		"Solofield",
		"Marginalia",
		"Purili",
		"wiby"
	],
	"images" => [
		"DuckDuckGo",
		"Yandex",
		"Brave",
		"Google",
		"Google API",
		"Google CSE",
		"Yahoo! JAPAN",
		"Startpage",
		"Naver",
		"Baidu",
		"Solofield",
		"Pinterest",
		"Flickr",
		"Pexels",
		"Pixabay",
		"Unsplash",
		"500px",
		"VSCO",
		"Imgur",
		"FindThatMeme"
	],
	"videos" => [
		"YouTube",
		"Vimeo",
		"Sepia Search",
		"DuckDuckGo",
		"Brave",
		"Yandex",
		"Google",
		"Yahoo! JAPAN",
		"Startpage",
		"Naver",
		"Baidu",
		"Cốc Cốc",
		"Purili",
		"Solofield"
	],
	"news" => [
		"DuckDuckGo",
		"Brave",
		"Google",
		"Yahoo! JAPAN",
		"Startpage",
		"Baidu"
	],
	"music" => [
		"SoundCloud",
		"Swisscows"
	],
	"autocomplete" => [
		"Brave",
		"DuckDuckGo",
		"Yandex",
		"Google",
		"Qwant",
		"Marginalia",
		"YouTube",
		"SoundCloud",
		"Startpage",
		"Kagi"
	]
];

$lines = [
	0 => "",
	1 => ""
];

// get longest scraper string
// also get list of scrapers with most entries
$biggest_category_len = 0;
foreach($scrapers as $category_name => $category){
	
	$count = count($category);
	
	if($count > $biggest_category_len){
		
		$biggest_category_len = $count;
	}
}

foreach($scrapers as $category_name => $category){
	
	// get longest string of the category
	$longest_len = mb_strlen($category_name);
	foreach($category as $scraper){
		
		$strlen = mb_strlen($scraper);
		
		if($strlen > $longest_len){
			
			$longest_len = $strlen;
		}
	}
	
	// add header
	$lines[0] .= mb_str_pad("| {$category_name}", $longest_len + 3);
	$lines[1] .= "|" . str_repeat("-", $longest_len + 2);
	
	for($i=2; $i<count($category) + 2; $i++){
		
		if(!isset($lines[$i])){
			
			$lines[$i] = "";
		}
		
		$lines[$i] .= mb_str_pad("| {$category[$i - 2]}", $longest_len + 3);
	}
	
	if($i - 2 < $biggest_category_len){
		
		for($k=$i; $k<$biggest_category_len + 2; $k++){
			
			if(!isset($lines[$k])){
				
				$lines[$k] = "";
			}
			
			$lines[$k] .= "|" . str_repeat(" ", $longest_len + 2);
		}
	}
}

foreach($lines as $line){
	
	echo $line . "|\n";
}
