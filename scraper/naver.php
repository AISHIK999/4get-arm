<?php

class naver{
	
	public function __construct(){
		
		include "lib/backend.php";
		$this->backend = new backend("naver");
		
		include "lib/fuckhtml.php";
		$this->fuckhtml = new fuckhtml();
	}
	
	public function getfilters($page){
		
		$base = [
			"time" => [
				"display" => "Time",
				"option" => [
					"any" => "Any time",
					"1h" => "Last hour",
					"1d" => "Last day",
					"1w" => "Last week",
					"1m" => "Last month",
					"3m" => "Last 3 months",
					"6m" => "Last 6 months",
					"1y" => "Last year",
				]
			]
		];
		
		switch($page){
			
			case "web":
				return
					array_merge([
						"sort" => [
							"display" => "Sort by",
							"option" => [
								"relevance" => "Relevance", // r
								"most_recent" => "Most recent" // dd
							]
						]
					], $base);
				break;
			
			case "images":
				return
					array_merge(
						$base,
						[
							"size" => [
								"display" => "Size",
								"option" => [
									"any" => "Any size",
									"highdef" => "High definition" // &res_fr=786432&res_to=100000000
								]
							],
							"color" => [ // &color=
								"display" => "Color",
								"option" => [
									"any" => "Any color",
									"orange" => "Orange",
									"yellow" => "Yellow",
									"lime" => "Lime",
									"green" => "Green",
									"cyan" => "Cyan",
									"blue" => "Blue",
									"purple" => "Purple",
									"pink" => "Pink",
									"apricot" => "Apricot",
									"ocher" => "Ocher",
									"sepia" => "Sepia",
									"black" => "Black",
									"gray" => "Gray",
									"white" => "White"
								]
							],
							"license" => [ // &ccl=
								"display" => "License",
								"option" => [
									"any" => "Any license",
									"1" => "CCL Total",
									"2" => "Commercial use",
									"4" => "Modifications permitted"
								]
							]
						]
					);
				break;
			
			case "videos":
				return
					[
						"time" => [ // done
							"display" => "Time",
							"option" => [
								"any" => "Any time",
								"1day" => "Last day",
								"1week" => "Last week",
								"1month" => "Last month",
								"3month" => "Last 3 months",
								"6month" => "Last 6 months",
								"1year" => "Last year"
							]
						],
						"sort" => [ // done
							"display" => "Sort by",
							"option" => [
								"rel" => "Relevance",
								"date" => "Most recent", // &sort=date
								"playcount" => "Most views", // &sort=playcount
							]
						],
						"type" => [ // done
							"display" => "Type",
							"option" => [
								"any" => "Any videos",
								"shorts" => "Shorts" // dtype=shorts
							]
						],
						"duration" => [
							"display" => "Duration", // &playtime=
							"option" => [
								"any" => "Any duration",
								"0:600" => "10 minutes",
								"601:1800" => "10-30 minutes",
								"1801:3600" => "30-60 minutes",
								"3601:65535" => "More than 1 hour"
							]
						]
					];
				break;
		}
	}
	
	private function get($proxy, $url, $get = [], $is_xhr = false){
		
		$curlproc = curl_init();
		
		if($get !== []){
			$get = http_build_query($get);
			$url .= "?" . $get;
		}
		
		curl_setopt($curlproc, CURLOPT_URL, $url);
		
		// use http2
		curl_setopt($curlproc, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		
		if($is_xhr === false){
			
			curl_setopt($curlproc, CURLOPT_HTTPHEADER,
				["User-Agent: " . config::USER_AGENT,
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
				"Accept-Language: en-US,en;q=0.5",
				"Accept-Encoding: gzip",
				"DNT: 1",
				"Sec-GPC: 1",
				"Connection: keep-alive",
				"Upgrade-Insecure-Requests: 1",
				"Sec-Fetch-Dest: document",
				"Sec-Fetch-Mode: navigate",
				"Sec-Fetch-Site: same-origin",
				"Priority: u=0, i",
				"Sec-Fetch-User: ?1"]
			);
		}else{
			
			curl_setopt($curlproc, CURLOPT_HTTPHEADER,
				["User-Agent: " . config::USER_AGENT,
				"Accept: */*",
				"Accept-Language: en-US,en;q=0.9",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"Referer: https://search.naver.com/",
				"DNT: 1",
				"Sec-GPC: 1",
				"Alt-Used: s.search.naver.com",
				"Connection: keep-alive",
				"Sec-Fetch-Dest: script",
				"Sec-Fetch-Mode: no-cors",
				"Sec-Fetch-Site: same-site",
				"TE: trailers"]
			);
		}
		
		curl_setopt($curlproc, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curlproc, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curlproc, CURLOPT_TIMEOUT, 30);
		
		$this->backend->assign_proxy($curlproc, $proxy);
		
		$data = curl_exec($curlproc);
		
		if(curl_errno($curlproc)){
			
			throw new Exception(curl_error($curlproc));
		}
		
		curl_close($curlproc);
		return $data;
	}
	
	
	
	public function web($get){
		
		$search = $get["s"];
		if(strlen($search) === 0){
			
			throw new Exception("Search term is empty!");
		}
		
		$out = [
			"status" => "ok",
			"spelling" => [
				"type" => "no_correction",
				"using" => null,
				"correction" => null
			],
			"npt" => null,
			"answer" => [],
			"web" => [],
			"image" => [],
			"video" => [],
			"news" => [],
			"related" => []
		];
		
		if($get["npt"]){
			
			[$d, $proxy] = $this->backend->get($get["npt"], "web");
			
			try{
				
				$html =
					$this->get(
						$proxy,
						"https://search.naver.com/search.naver" . $d,
						[]
					);
				
			}catch(Exception $error){
				
				throw new Exception("Failed to fetch search page");
			}
		}else{
			
			// parse filters
			// https://search.naver.com
			// /search.naver
			// ?nso=
			// &page=1
			// &query=nisekoi
			// &sm=tab_pge
			// &start=1
			// &where=web
			
			$filters = [
				"nso" => "",
				"query" => $search,
				"sm" => "tab_pge",
				"where" => "web",
				"start" => 1 // increment by number of results each time (16??)
			];
			
			$options = [];
			
			if($get["sort"] != "relevance"){
				
				$options[] = "so:dd";
			}
			
			if($get["time"] != "any"){
				
				$options[] = "p:" . $get["time"];
			}
			
			if(count($options) !== 0){
				
				$filters["nso"] = implode(",", $options);
			}
			
			//$html = file_get_contents("scraper/naver.html");
			
			$proxy = $this->backend->get_ip();
			
			try{
				$html =
					$this->get(
						$proxy,
						"https://search.naver.com/search.naver",
						$filters
					);
				
			}catch(Exception $error){
				
				throw new Exception("Failed to fetch search page");
			}
		}
		
		$this->fuckhtml->load($html);
		
		$results =
			preg_split(
				'/entry\.bootstrap\(document\.getElementById\("[a-f0-9-r]+"\), ?/',
				$html
			);
		
		if(count($results) !== 2){
			
			// this is thrown when no results are found
			
			$nsfw_probe =
				$this->fuckhtml
				->getElementsByClassName(
					"dsc_adult",
					"div"
				);
			
			if(count($nsfw_probe) !== 0){
				
				$out["answer"][] = [
					"title" => "NSFW results",
					"description" => [
						[
							"type" => "text",
							"value" => "Naver blocks logged-out NSFW searches."
						]
					],
					"url" => null,
					"thumb" => null,
					"table" => [],
					"sublink" => []
				];
			}
			
			return $out;
			//throw new Exception("Failed to grep results entrypoint");
		}
		
		$json =
			json_decode(
				$this->fuckhtml
				->extract_json(
					$results[1]
				),
				true
			);
		
		if(!isset($json["body"]["props"]["children"][0]["props"]["children"])){
			
			throw new Exception("Failed to access nested children");
		}
		
		foreach($json["body"]["props"]["children"][0]["props"]["children"] as $result){
			
			if(
				!isset($result["templateId"]) ||
				$result["templateId"] != "webItem"
			){
				
				// should not happen
				continue;
			}
			
			$result = $result["props"];
			
			// get sublinks
			$sublinks = [];
			
			if(isset($result["subLinks"])){
				
				foreach($result["subLinks"] as $s){
					
					$sublinks[] = [
						"title" => $s["text"],
						"description" => null,
						"url" => $s["href"],
						"date" => null
					];
				}
			}
			
			if(isset($result["linkBtns"])){
				
				foreach($result["linkBtns"] as $s){
					
					$sublinks[] = [
						"title" => $s["text"],
						"description" => null,
						"url" => $s["href"],
						"date" => null
					];
				}
			}
			
			// get image (thumbnail, i guess)
			if(isset($result["images"][0]["imageSrc"])){
				
				$thumb = [
					"ratio" => "16:9",
					"url" => $this->unshit_thumb($result["images"][0]["imageSrc"])
				];
			}else{
				
				$thumb = [
					"ratio" => null,
					"url" => null
				];
			}
			
			// get table elements
			$table = [];
			
			if(isset($result["keyValue"]["contents"])){
				
				foreach($result["keyValue"]["contents"] as $s){
					
					if(!isset($s["valueData"]["text"])){ continue; }
					
					$table[$s["key"]] = $s["valueData"]["text"];
				}
			}
			
			// get date
			$time = null;
			
			if(isset($result["bodyPrefixes"][0]["text"])){
				
				$date =
					strtotime(
						substr(
							$result["bodyPrefixes"][0]["text"],
							-1
						)
					);
				
				if($date !== false){
					
					$time = $date;
				}
			}
			
			$out["web"][] = [
				"title" => $this->decode_html($result["title"]),
				"description" => $this->decode_html($result["bodyText"]),
				"url" => $result["href"],
				"date" => $time,
				"type" => "web",
				"thumb" => $thumb,
				"sublink" => $sublinks,
				"table" => $table
			];
		}
		
		// get next page
		$npt =
			$this->fuckhtml
			->getElementsByClassName(
				"btn_next",
				"a"
			);
		
		if(count($npt) !== 0){
			
			$out["npt"] =
				$this->backend->store(
					$this->fuckhtml
					->getTextContent(
						$npt[0]["attributes"]["href"]
					),
					"web",
					$proxy
				);
		}
		
		return $out;
	}
	
	
	public function image($get){
		
		$search = $get["s"];
		if(strlen($search) === 0){
			
			throw new Exception("Search term is empty!");
		}
		
		$out = [
			"status" => "ok",
			"npt" => null,
			"image" => []
		];
		
		if($get["npt"]){
			
			[$url, $proxy] = $this->backend->get($get["npt"], "images");
			
			try{
				
				$json =
					$this->get(
						$proxy,
						$url,
						[],
						true
					);
				
			}catch(Exception $error){
				
				throw new Exception("Failed to fetch search page");
			}
		}else{
			
			$filters = [
				"ac" => "0",
				"api_type" => "pc_tab_more",
				"aq" => "0",
				"display" => 100,
				"logStart" => 1,
				"mode" => "column",
				"nso" => "so:r,p:all",
				"nx_search_query" => $search,
				"query" => $search,
				"section" => "image",
				"sm" => "tab_opt",
				"ssc" => "tab.image.all",
				"start" => 1,
				"where" => "image"
				// no callback, returns raw json lol
			];
			
			$options = [
				"so:r"
			];
			
			if($get["time"] != "any"){
				
				$options[] = "p:" . $get["time"];
			}
			
			if(count($options) !== 0){
				
				$filters["nso"] = implode(",", $options);
			}
			
			if($get["size"] != "any"){
				
				$filters["res_fr"] = 786432;
				$filters["res_to"] = 100000000;
			}
			
			if($get["color"] != "any"){
				
				$filters["color"] = $get["color"];
			}
			
			if($get["license"] != "any"){
				
				$filters["ccl"] = $get["license"];
			}
			
			//$json = file_get_contents("scraper/naver.html");
			
			$proxy = $this->backend->get_ip();
			
			try{
				$json =
					$this->get(
						$proxy,
						"https://s.search.naver.com/p/c/image/46/search.naver",
						$filters,
						true
					);
				
			}catch(Exception $error){
				
				throw new Exception("Failed to fetch search page");
			}
		}
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Failed to decode JSON");
		}
		
		if(!isset($json["items"])){
			
			// no results returned :(
			return $out;
			//throw new Exception("Naver did not return an items object");
		}
		
		foreach($json["items"] as $image){
			
			// why does it fucking do that
			if($image["orgWidth"] === 0){ continue; }
			
			$out["image"][] = [
				"title" => trim($image["title"], "."),
				"source" => [
					[
						"url" => $image["originalUrl"],
						"width" => (int)$image["orgWidth"],
						"height" => (int)$image["orgHeight"]
					],
					[
						"url" => $image["thumb"],
						"width" => (int)$image["thumbWidth"],
						"height" => (int)$image["thumbHeight"]
					]
				],
				"url" => $image["link"]
			];
		}
		
		// get npt
		if(
			isset($json["url"]) &&
			$json["url"] != "" &&
			$json["url"] != null
		){
			
			$out["npt"] =
				$this->backend->store(
					$json["url"],
					"images",
					$proxy
				);
		}
		
		return $out;
	}
	
	
	public function video($get){
		
		$search = $get["s"];
		if(strlen($search) === 0){
			
			throw new Exception("Search term is empty!");
		}
		
		$out = [
			"status" => "ok",
			"npt" => null,
			"video" => [],
			"author" => [],
			"livestream" => [],
			"playlist" => [],
			"reel" => []
		];
		
		if($get["npt"]){
			
			[$url, $proxy] = $this->backend->get($get["npt"], "images");
			
			try{
				
				$json =
					$this->get(
						$proxy,
						$url,
						[],
						true
					);
				
			}catch(Exception $error){
				
				throw new Exception("Failed to fetch search page");
			}
		}else{
			
			// https://s.search.naver.com/p/video/48/search.naver?ac=0&aq=0&crbase=63&display=48&dtype=&last_block_type=recom&nlu_query=&nq=&nqx_theme={"theme":{"main":{"name":"encyclopedia","source":"TOS"}}}&nx_and_query=&nx_search_hlquery=&nx_search_query=&nx_sub_query=&page=2&period=&playtime=&ptype=&query=asmr&selected_channel=&selected_cp=&sm=mtb_pge&sort=rel&ssc=tab.video.all&start=49&video_more=1
			// https://s.search.naver.com/p/video/48/search.naver
			// ?ac=0
			// &aq=0
			// &crbase=63
			// &display=48
			// &dtype=
			// &last_block_type=recom
			// &nlu_query=
			// &nq=
			// &nqx_theme={"theme":{"main":{"name":"encyclopedia","source":"TOS"}}}
			// &nx_and_query=
			// &nx_search_hlquery=
			// &nx_search_query=
			// &nx_sub_query=
			// &page=2
			// &period=
			// &playtime=
			// &ptype=
			// &query=asmr
			// &selected_channel=
			// &selected_cp=
			// &sm=mtb_pge
			// &sort=rel
			// &ssc=tab.video.all
			// &start=49
			// &video_more=1
			
			$filters = [
				"ac" => "0",
				"aq" => "0",
				"crbase" => "78",
				"display" => 48,
				"dtype" => "",
				"last_block_type" => "recom",
				"nlu_query" => "",
				"nq" => "",
				"nx_and_query" => "",
				"nx_search_hlquery" => "",
				"nx_search_query" => "",
				"nx_sub_query" => "",
				"page" => 1,
				"period" => "",
				"playtime" => "",
				"ptype" => "",
				"query" => $search,
				"selected_channel" => "",
				"selected_cp" => "",
				"sm" => "mtb_pge",
				"sort" => "rel",
				"ssc" => "tab.video.all",
				"start" => 1,
				"video_more" => 1
			];
			
			if($get["type"] != "any"){
				
				$filters["dtype"] = $get["type"];
			}
			
			if($get["time"] != "any"){
				
				$filters["period"] = $get["time"];
			}
			
			if($get["sort"] != "rel"){
				
				$filters["sort"] = $get["sort"];
			}
			
			if($get["duration"] != "any"){
				
				$filters["playtime"] = $get["duration"];
			}
			
			//$json = file_get_contents("scraper/naver.html");
			
			$proxy = $this->backend->get_ip();
			
			try{
				$json =
					$this->get(
						$proxy,
						"https://s.search.naver.com/p/video/48/search.naver",
						$filters,
						true
					);
				
			}catch(Exception $error){
				
				throw new Exception("Failed to fetch search page");
			}
		}
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Failed to decode JSON");
		}
		
		if(!isset($json["collection"])){
			
			return $out;
			//throw new Exception("Naver did not return a collection HTML element");
		}
		
		foreach($json["collection"] as $snippet){
			
			if(!isset($snippet["html"])){ continue; }
			
			$this->fuckhtml->load($snippet["html"]);
			
			$div =
				$this->fuckhtml
				->getElementsByTagName(
					"div"
				);
			
			$items =
				$this->fuckhtml
				->getElementsByAttributeValue(
					"data-template-id",
					"videoItem",
					$div
				);
			
			// parse normal videos
			foreach($items as $item){
				
				if($item["level"] === 6){ continue; }
				
				$this->fuckhtml->load($item);
				
				// get url
				$as =
					$this->fuckhtml
					->getElementsByAttributeName(
						"data-heatmap-target",
						"a"
					);
				
				if(count($as) === 0){
					
					// should not happen
					continue;
				}
				
				// get thumbnail
				$thumb =
					$this->fuckhtml
					->getElementsByAttributeValue(
						"loading",
						"lazy",
						"img"
					);
				
				if(count($thumb) !== 0){
					
					$thumb = [
						"url" =>
							$this->unshit_thumb(
								$this->fuckhtml
								->getTextContent(
									$thumb[0]["attributes"]["src"]
								)
							),
						"ratio" => "16:9"
					];
				}else{
					
					$thumb = [
						"url" => null,
						"ratio" => null
					];
				}
				
				// get timestamp
				$timestamp_probe =
					$this->fuckhtml
					->getElementsByClassName(
						"sds-comps-text-type-footnote",
						"span"
					);
				
				if(count($timestamp_probe) !== 0){
					
					$timestamp =
						$this->hms2int(
							$this->fuckhtml
							->getTextContent(
								$timestamp_probe[0]
							)
						);
				}else{
					
					$timestamp = null;
				}
				
				$out["video"][] = [
					"title" =>
						$this->fuckhtml
						->getTextContent(
							$as[0]
						),
					"description" => null,
					"author" => [
							"name" =>
								isset($item["attributes"]["profileimagealt"]) ?
								$this->fuckhtml
								->getTextContent(
									$item["attributes"]["profileimagealt"]
								) : null,
							"url" =>
								isset($item["attributes"]["profileimagehref"]) ?
								$this->fuckhtml
								->getTextContent(
									$item["attributes"]["profileimagehref"]
								) : null,
							"avatar" =>
								isset($item["attributes"]["profileimagesrc"]) ?
								$this->fuckhtml
								->getTextContent(
									$item["attributes"]["profileimagesrc"]
								) : null
						],
					"date" => null,
					"duration" => $timestamp,
					"views" => null,
					"thumb" => $thumb,
					"url" =>
						$this->fuckhtml
						->getTextContent(
							$as[0]["attributes"]["href"]
						)
				];
			}
			
			// reset
			$this->fuckhtml->load($snippet["html"]);
			
			// parse reels
			$carousels =
				array_merge(
					$this->fuckhtml // for the reels only tab
					->getElementsByClassName(
						"fds-video-tab-shortform-desk-filter",
						$div
					),
					$this->fuckhtml // for the normal tab with reels inbetween
					->getElementsByClassName(
						"fds-video-tab-shortform-desk",
						$div
					)
				);
			
			foreach($carousels as $carousel){
				
				$this->fuckhtml->load($carousel);
				
				$as =
					$this->fuckhtml
					->getElementsByTagName(
						"a"
					);
				
				foreach($as as $reel){
					
					$this->fuckhtml->load($reel);
					
					$spans =
						$this->fuckhtml
						->getElementsByTagName(
							"span"
						);
						
					$title =
						$this->fuckhtml
						->getTextContent(
							$spans[0]
						);
					
					// get thumbnail
					$thumb =
						$this->fuckhtml
						->getElementsByAttributeValue(
							"loading",
							"lazy",
							"img"
						);
					
					if(count($thumb) !== 0){
						
						$thumb = [
							"url" =>
								$this->unshit_thumb(
									$this->fuckhtml
									->getTextContent(
										$thumb[0]["attributes"]["src"]
									)
								),
							"ratio" => "16:9"
						];
					}else{
						
						$thumb = [
							"url" => null,
							"ratio" => null
						];
					}
					
					$name =
						$this->fuckhtml
						->getElementsByClassName(
							"sds-comps-profile-info-title-text",
							$spans
						);
					
					if(count($name) === 0){
						
						$name = null;
					}else{
						
						$name =
							$this->fuckhtml
							->getTextContent(
								$name[0]
							);
					}
					
					$out["reel"][] = [
						"title" => $title,
						"description" => null,
						"author" => [
								"name" => $name,
								"url" => null,
								"avatar" => null
							],
						"date" => null,
						"duration" => null,
						"views" => null,
						"thumb" => $thumb,
						"url" =>
							$this->fuckhtml
							->getTextContent(
								$reel["attributes"]["href"]
							)
					];
				}
			}
		}
		
		// get npt
		if(
			isset($json["url"]) &&
			$json["url"] != "" &&
			$json["url"] != null
		){
			
			$out["npt"] =
				$this->backend->store(
					$json["url"],
					"images",
					$proxy
				);
		}
		
		return $out;
	}
	
	
	private function unshit_thumb($url){
		
		$parts = parse_url($url);
		
		if($parts["host"] == "search.pstatic.net"){
			
			parse_str($parts["query"], $str);
			
			if(isset($str["src"])){
				
				return $str["src"];
			}
		}
		
		return $url;
	}
	
	
	private function decode_html($html){
		
		return
			trim(
				html_entity_decode(
					strip_tags(
						$html
					)
				),
				"."
			);
	}
	
	
	private function hms2int($time){
		
		$parts = explode(":", $time, 3);
		$time = 0;
		
		if(count($parts) === 3){
			
			// hours
			$time = $time + ((int)$parts[0] * 3600);
			array_shift($parts);
		}
		
		if(count($parts) === 2){
			
			// minutes
			$time = $time + ((int)$parts[0] * 60);
			array_shift($parts);
		}
		
		// seconds
		$time = $time + (int)$parts[0];
		
		return $time;
	}
}
