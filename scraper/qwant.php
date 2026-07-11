<?php

class qwant{
	
	public function __construct(){
		
		include "lib/backend.php";
		$this->backend = new backend("qwant");
	}
	
	public function getfilters($page){
		
		$base = [
			"nsfw" => [
				"display" => "NSFW",
				"option" => [
					"yes" => "Yes",
					"maybe" => "Maybe",
					"no" => "No"
				]
			],
			"country" => [
				"display" => "Country",
				"option" => [
					"en_US" => "United States",
					"fr_FR" => "France",
					"en_GB" => "Great Britain",
					"de_DE" => "Germany",
					"it_IT" => "Italy",
					"es_AR" => "Argentina",
					"en_AU" => "Australia",
					"es_ES" => "Spain (es)",
					"ca_ES" => "Spain (ca)",
					"cs_CZ" => "Czech Republic",
					"ro_RO" => "Romania",
					"el_GR" => "Greece",
					"zh_CN" => "China",
					"zh_HK" => "Hong Kong",
					"en_NZ" => "New Zealand",
					"fr_FR" => "France",
					"th_TH" => "Thailand",
					"ko_KR" => "South Korea",
					"sv_SE" => "Sweden",
					"nb_NO" => "Norway",
					"da_DK" => "Denmark",
					"hu_HU" => "Hungary",
					"et_EE" => "Estonia",
					"es_MX" => "Mexico",
					"es_CL" => "Chile",
					"en_CA" => "Canada (en)",
					"fr_CA" => "Canada (fr)",
					"en_MY" => "Malaysia",
					"bg_BG" => "Bulgaria",
					"fi_FI" => "Finland",
					"pl_PL" => "Poland",
					"nl_NL" => "Netherlands",
					"pt_PT" => "Portugal",
					"de_CH" => "Switzerland (de)",
					"fr_CH" => "Switzerland (fr)",
					"it_CH" => "Switzerland (it)",
					"de_AT" => "Austria",
					"fr_BE" => "Belgium (fr)",
					"nl_BE" => "Belgium (nl)",
					"en_IE" => "Ireland",
					"he_IL" => "Israel"
				]
			]
		];
		
		switch($page){
			
			case "web":
				$base = array_merge(
					$base,
					[
						"time" => [
							"display" => "Time posted",
							"option" => [
								"any" => "Any time",
								"day" => "Past 24 hours",
								"week" => "Past week",
								"month" => "Past month"
							]
						],
						"extendedsearch" => [
							// no display, wont show in interface
							"option" => [
								"yes" => "Yes",
								"no" => "No"
							]
						]
					]
				);
				break;
			
			case "images":
				$base = array_merge(
					$base,
					[
						"time" => [
							"display" => "Time posted",
							"option" => [
								"any" => "Any time",
								"day" => "Past 24 hours",
								"week" => "Past week",
								"month" => "Past month"
							]
						],
						"size" => [
							"display" => "Size",
							"option" => [
								"any" => "Any size",
								"large" => "Large",
								"medium" => "Medium",
								"small" => "Small"
							]
						],
						"color" => [
							"display" => "Color",
							"option" => [
								"any" => "Any color",
								"coloronly" => "Color only",
								"monochrome" => "Monochrome",
								"black" => "Black",
								"brown" => "Brown",
								"gray" => "Gray",
								"white" => "White",
								"yellow" => "Yellow",
								"orange" => "Orange",
								"red" => "Red",
								"pink" => "Pink",
								"purple" => "Purple",
								"blue" => "Blue",
								"teal" => "Teal",
								"green" => "Green"
							]
						],
						"imagetype" => [
							"display" => "Type",
							"option" => [
								"any" => "Any type",
								"animatedgif" => "Animated GIF",
								"photo" => "Photograph",
								"transparent" => "Transparent"
							]
						],
						"license" => [
							"display" => "License",
							"option" => [
								"any" => "Any license",
								"share" => "Non-commercial reproduction and sharing",
								"sharecommercially" => "Reproduction and sharing",
								"modify" => "Non-commercial reproduction, sharing and modification",
								"modifycommercially" => "Reproduction, sharing and modification",
								"public" => "Public domain"
							]
						]
					]
				);
				break;
			
			case "videos":
				$base = array_merge(
					$base,
					[
						"order" => [
							"display" => "Order by",
							"option" => [
								"relevance" => "Relevance",
								"views" => "Views",
								"date" => "Most recent",
							]
						],
						"source" => [
							"display" => "Source",
							"option" => [
								"any" => "Any source",
								"youtube" => "YouTube",
								"dailymotion" => "Dailymotion",
							]
						]
					]
				);
				break;
			
			case "news":
				$base = array_merge(
					$base,
					[
						"time" => [
							"display" => "Time posted",
							"option" => [
								"any" => "Any time",
								"hour" => "Less than 1 hour ago",
								"day" => "Past 24 hours",
								"week" => "Past week",
								"month" => "Past month"
							]
						],
						"order" => [
							"display" => "Order by",
							"option" => [
								"relevance" => "Relevance",
								"date" => "Most recent"
							]
						]
					]
				);
				break;
		}
		
		return $base;
	}
	
	private function get($proxy, $url, $get = [], $is_post = false, $cookie = null){
		
		$curlproc = curl_init();
		
		if($is_post){
			
			$headers = [
				"User-Agent: " . config::USER_AGENT,
				"Accept: */*",
				"Accept-Language: en-US,en;q=0.9",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"Referer: https://www.qwant.com/",
				"Content-type: application/x-www-form-urlencoded",
				"Origin: https://www.qwant.com",
				"DNT: 1",
				"Sec-GPC: 1",
				"Connection: keep-alive",
				"Sec-Fetch-Dest: empty",
				"Sec-Fetch-Mode: cors",
				"Sec-Fetch-Site: same-site",
				"Pragma: no-cache",
				"Cache-Control: no-cache"
			];
			
			curl_setopt($curlproc, CURLOPT_POST, true);
			curl_setopt($curlproc, CURLOPT_POSTFIELDS, http_build_query($get));
		}else{
			
			$headers = [
				"User-Agent: " . config::USER_AGENT,
				"Accept: application/json, text/plain, */*",
				"Accept-Language: en-US,en;q=0.9",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"Referer: https://www.qwant.com/",
				"Origin: https://www.qwant.com",
				"DNT: 1",
				($cookie !== null ? "Cookie: {$cookie}" : ""),
				"Sec-GPC: 1",
				"Connection: keep-alive",
				"Sec-Fetch-Dest: empty",
				"Sec-Fetch-Mode: cors",
				"Sec-Fetch-Site: same-site",
				"Priority: u=4",
				"Pragma: no-cache",
				"Cache-Control: no-cache",
				"TE: trailers"
			];
						
			if($get !== []){
				$get = http_build_query($get);
				$url .= "?" . $get;
			}
		}
		
		curl_setopt($curlproc, CURLOPT_URL, $url);
		
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		curl_setopt($curlproc, CURLOPT_HTTPHEADER, $headers);
		
		// Bypass HTTP/2 check
		curl_setopt($curlproc, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		
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
		
		if($get["npt"]){
			
			// get next page data
			[$params, $proxy] = $this->backend->get($get["npt"], "web");
			
			$params = json_decode($params, true);
			$cookie = $params["cookie"];
			unset($params["cookie"]);
			
		}else{
			
			// get _GET data instead
			$search = $get["s"];
			if(strlen($search) === 0){
				
				throw new Exception("Search term is empty!");
			}
			
			if(strlen($search) > 2048){
				
				throw new Exception("Search term is too long!");
			}
			
			$proxy = $this->backend->get_ip();
			
			$params = [
				"q" => $search,
				"freshness" => $get["time"],
				"count" => 10,
				"locale" => $get["country"],
				"offset" => 0,
				"device" => "desktop",
				"tgp" => 3,
				"safesearch" => 0,
				"displayed" => "true",
				"llm" => "false"
			];
			
			switch($get["nsfw"]){
				
				case "yes": $params["safesearch"] = 0; break;
				case "maybe": $params["safesearch"] = 1; break;
				case "no": $params["safesearch"] = 2; break;
			}
			
			$cookie = null;
		}
		/*
		$handle = fopen("scraper/qwant_web.json", "r");
		$json = fread($handle, filesize("scraper/qwant_web.json"));
		fclose($handle);*/
		
		$cookie = $this->fuck_datadome($proxy, $search);
		
		try{
			$json =
				$this->get(
					$proxy,
					"https://api.qwant.com/v3/search/web",
					$params,
					false,
					$cookie
				);
			
		}catch(Exception $error){
			
			throw new Exception("Could not fetch JSON");
		}
		
		$json = json_decode($json, true);
		
		if($json === NULL){
			
			throw new Exception("Failed to decode JSON");
		}
		
		if(isset($json["data"]["message"][0])){
			
			throw new Exception("Server returned an error:\n" . $json["data"]["message"][0]);
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
		
		$this->detect_errors($json);
		
		if(
			$json["status"] != "success" &&
			$json["data"]["error_code"] === 5
		){
			
			// no results
			return $out;
		}
		
		if(!isset($json["data"]["result"]["items"]["mainline"])){
			
			throw new Exception("Server did not return a result object");
		}
		
		// data is OK, parse
		
		// get instant answer
		if(
			$get["extendedsearch"] == "yes" &&
			isset($json["data"]["result"]["items"]["sidebar"][0]["endpoint"])
		){
			
			try{
				$answer =
					$this->get(
						$proxy,
						"https://api.qwant.com/v3" .
						$json["data"]["result"]["items"]["sidebar"][0]["endpoint"],
						[]
					);
				
				$answer = json_decode($answer, true);
				
				if(
					$answer === null ||
					$answer["status"] != "success" ||
					$answer["data"]["result"] === null
				){
					
					throw new Exception();
				}
				
				// parse answer
				$out["answer"][] = [
					"title" => $answer["data"]["result"]["title"],
					"description" => [
						[
							"type" => "text",
							"value" => $this->trimdots($answer["data"]["result"]["description"])
						]
					],
					"url" => $answer["data"]["result"]["url"],
					"thumb" =>
						$answer["data"]["result"]["thumbnail"]["landscape"] == null ?
						null :
						$this->unshitimage($answer["data"]["result"]["thumbnail"]["landscape"]),
					"table" => [],
					"sublink" => []
				];
				
			}catch(Exception $error){
				
				// do nothing in case of failure
			}
			
		}
		
		// get word correction
		if(isset($json["data"]["query"]["queryContext"]["alteredQuery"])){
			
			$out["spelling"] = [
				"type" => "including",
				"using" => $json["data"]["query"]["queryContext"]["alteredQuery"],
				"correction" => $json["data"]["query"]["queryContext"]["alterationOverrideQuery"]
			];
		}
		
		// check for next page
		if($json["data"]["result"]["lastPage"] === false){
			
			$params["offset"] = $params["offset"] + 10;
			$params["cookie"] = $cookie;
			
			$out["npt"] =
				$this->backend->store(
					json_encode($params),
					"web",
					$proxy
				);
		}
		
		// parse results
		foreach($json["data"]["result"]["items"]["mainline"] as $item){
			
			switch($item["type"]){ // ignores ads
				
				case "web":
					
					$first_iteration = true;
					foreach($item["items"] as $result){
						
						if(isset($result["thumbnailUrl"])){
							
							$thumb = [
								"url" => $this->unshitimage($result["thumbnailUrl"]),
								"ratio" => "16:9"
							];
						}else{
							
							$thumb = [
								"url" => null,
								"ratio" => null
							];
						}
						
						$sublinks = [];
						if(isset($result["links"])){
							
							foreach($result["links"] as $link){
								
								$sublinks[] = [
									"title" => $this->trimdots($link["title"]),
									"date" => null,
									"description" => isset($link["desc"]) ? $this->trimdots($link["desc"]) : null,
									"url" => $link["url"]
								];
							}
						}
						
						// detect gibberish results
						if(
							$first_iteration &&
							!isset($result["urlPingSuffix"])
						){
							
							throw new Exception("Qwant returned gibberish results");
						}
						
						$out["web"][] = [
							"title" => $this->trimdots($result["title"]),
							"description" => $this->trimdots($result["desc"]),
							"url" => $result["url"],
							"date" => null,
							"type" => "web",
							"thumb" => $thumb,
							"sublink" => $sublinks,
							"table" => []
						];
						
						$first_iteration = false;
					}
					break;
				
				case "images":
					foreach($item["items"] as $image){
						
						$out["image"][] = [
							"title" => $image["title"],
							"source" => [
								[
									"url" => $image["media"],
									"width" => (int)$image["width"],
									"height" => (int)$image["height"]
								],
								[
									"url" => $this->unshitimage($image["thumbnail"]),
									"width" => $image["thumb_width"],
									"height" => $image["thumb_height"]
								]
							],
							"url" => $image["url"]
						];
					}
					break;
				
				case "videos":
					foreach($item["items"] as $video){
						
						$out["video"][] = [
							"title" => $video["title"],
							"description" => null,
							"date" => (int)$video["date"],
							"duration" => $video["duration"] === null ? null : $video["duration"] / 1000,
							"views" => null,
							"thumb" =>
								$video["thumbnail"] === null ?
								[
									"url" => null,
									"ratio" => null,
								] :
								[
									"url" => $this->unshitimage($video["thumbnail"]),
									"ratio" => "16:9",
								],
							"url" => $video["url"]
						];
					}
					break;
				
				case "related_searches":
					foreach($item["items"] as $related){
						
						$out["related"][] = $related["text"];
					}
					break;
			}
		}
		
		return $out;
	}
	
	
	public function image($get){
		
		if($get["npt"]){
			
			[$params, $proxy] =
				$this->backend->get(
					$get["npt"],
					"images"
				);
			
			$params = json_decode($params, true);
			$cookie = $params["cookie"];
			unset($params["cookie"]);
			
		}else{
			
			$search = $get["s"];
			
			if(strlen($search) === 0){
				
				throw new Exception("Search term is empty!");
			}
			
			$proxy = $this->backend->get_ip();
			
			$params = [
				"t" => "images",
				"q" => $search,
				"count" => 125,
				"locale" => $get["country"],
				"offset" => 0, // increment by 125
				"device" => "desktop",
				"tgp" => 3
			];
			
			if($get["time"] != "any"){
				
				$params["freshness"] = $get["time"];
			}
			
			foreach(["size", "color", "imagetype", "license"] as $p){
				
				if($get[$p] != "any"){
					
					$params[$p] = $get[$p];
				}
			}
			
			switch($get["nsfw"]){
				
				case "yes": $params["safesearch"] = 0; break;
				case "maybe": $params["safesearch"] = 1; break;
				case "no": $params["safesearch"] = 2; break;
			}
			
			$cookie = null;
		}
		
		$cookie = $this->fuck_datadome($proxy, $search);
		
		try{
			$json = $this->get(
				$proxy,
				"https://api.qwant.com/v3/search/images",
				$params,
				false,
				$cookie
			);
		}catch(Exception $err){
			
			throw new Exception("Failed to get JSON");
		}
		
		/*
		$handle = fopen("scraper/yandex.json", "r");
		$json = fread($handle, filesize("scraper/yandex.json"));
		fclose($handle);*/
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Failed to decode JSON");
		}
		
		$this->detect_errors($json);
		
		if(isset($json["data"]["result"]["items"]["mainline"])){
			
			throw new Exception("Qwant returned gibberish results");
		}
		
		$out = [
			"status" => "ok",
			"npt" => null,
			"image" => []
		];
		
		if($json["data"]["result"]["lastPage"] === false){
			
			$params["offset"] = $params["offset"] + 125;
			
			$out["npt"] = $this->backend->store(
				json_encode($params),
				"images",
				$proxy
			);
		}
		
		foreach($json["data"]["result"]["items"] as $image){
			
			$out["image"][] = [
				"title" => $this->trimdots($image["title"]),
				"source" => [
					[
						"url" => $image["media"],
						"width" => $image["width"],
						"height" => $image["height"]
					],
					[
						"url" => $this->unshitimage($image["thumbnail"]),
						"width" => $image["thumb_width"],
						"height" => $image["thumb_height"]
					]
				],
				"url" => $image["url"]
			];
		}
		
		return $out;
	}
	
	public function video($get){
		
		$search = $get["s"];
		if(strlen($search) === 0){
			
			throw new Exception("Search term is empty!");
		}
		
		$params = [
			"t" => "videos",
			"q" => $search,
			"count" => 50,
			"locale" => $get["country"],
			"offset" => 0, // dont implement pagination
			"device" => "desktop",
			"tgp" => 3
		];
		
		switch($get["nsfw"]){
			
			case "yes": $params["safesearch"] = 0; break;
			case "maybe": $params["safesearch"] = 1; break;
			case "no": $params["safesearch"] = 2; break;
		}
		
		try{
			$json =
				$this->get(
					$this->backend->get_ip(),
					"https://api.qwant.com/v3/search/videos",
					$params
				);
		}catch(Exception $error){
			
			throw new Exception("Could not fetch JSON");
		}
		
		/*
		$handle = fopen("scraper/yandex-video.json", "r");
		$json = fread($handle, filesize("scraper/yandex-video.json"));
		fclose($handle);
		*/
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Could not parse JSON");
		}
		
		$this->detect_errors($json);
		
		if(isset($json["data"]["result"]["items"]["mainline"])){
			
			throw new Exception("Qwant returned gibberish results");
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
		
		foreach($json["data"]["result"]["items"] as $video){
			
			if(empty($video["thumbnail"])){
				
				$thumb = [
					"url" => null,
					"ratio" => null
				];
			}else{
				
				$thumb = [
					"url" => $this->unshitimage($video["thumbnail"]),
					"ratio" => "16:9"
				];
			}
			
			$duration = (int)$video["duration"];
			
			$out["video"][] = [
				"title" => $video["title"],
				"description" => $this->limitstrlen($video["desc"]),
				"author" => [
					"name" => $video["channel"],
					"url" => null,
					"avatar" => null
				],
				"date" => (int)$video["date"],
				"duration" => $duration === 0 ? null : $duration,
				"views" => null,
				"thumb" => $thumb,
				"url" => preg_replace("/\?syndication=.+/", "", $video["url"])
			];
		}
		
		return $out;
	}
	
	public function news($get){
		
		$search = $get["s"];
		if(strlen($search) === 0){
			
			throw new Exception("Search term is empty!");
		}
		
		$params = [
			"t" => "news",
			"q" => $search,
			"count" => 50,
			"locale" => $get["country"],
			"offset" => 0, // dont implement pagination
			"device" => "desktop",
			"tgp" => 3
		];
		
		switch($get["nsfw"]){
			
			case "yes": $params["safesearch"] = 0; break;
			case "maybe": $params["safesearch"] = 1; break;
			case "no": $params["safesearch"] = 2; break;
		}
		
		try{
			$json =
				$this->get(
					$this->backend->get_ip(),
					"https://api.qwant.com/v3/search/news",
					$params
				);
		}catch(Exception $error){
			
			throw new Exception("Could not fetch JSON");
		}
		
		/*
		$handle = fopen("scraper/yandex-video.json", "r");
		$json = fread($handle, filesize("scraper/yandex-video.json"));
		fclose($handle);
		*/
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Could not parse JSON");
		}
		
		$this->detect_errors($json);
		
		if(isset($json["data"]["result"]["items"]["mainline"])){
			
			throw new Exception("Qwant returned gibberish results");
		}
		
		$out = [
			"status" => "ok",
			"npt" => null,
			"news" => []
		];
		
		foreach($json["data"]["result"]["items"] as $news){
			
			if(empty($news["media"][0]["pict_big"]["url"])){
				
				$thumb = [
					"url" => null,
					"ratio" => null
				];
			}else{
				
				$thumb = [
					"url" => $this->unshitimage($news["media"][0]["pict_big"]["url"]),
					"ratio" => "16:9"
				];
			}
			
			$out["news"][] = [
				"title" => $news["title"],
				"author" => $news["press_name"],
				"description" => $this->trimdots($news["desc"]),
				"date" => (int)$news["date"],
				"thumb" => $thumb,
				"url" => $news["url"]
			];
		}
		
		return $out;
	}
	
	private function detect_errors($json){
		
		if(
			isset($json["status"]) &&
			$json["status"] == "error"
		){
			
			if(isset($json["data"]["error_data"]["captchaUrl"])){
				
				throw new Exception("Qwant returned a captcha");
			}elseif(isset($json["data"]["error_data"]["error_code"])){
				
				throw new Exception(
					"Qwant returned an API error: " .
					$json["data"]["error_data"]["error_code"]
				);
			}
			
			throw new Exception("Qwant returned an API error");
		}
		
		if(
			isset($json["url"]) &&
			preg_match(
				'/captcha/i',
				$json["url"]
			)
		){
			
			throw new Exception("Qwant returned a captcha redirect");
		}
	}
	
	private function limitstrlen($text){
		
		return explode("\n", wordwrap($text, 300, "\n"))[0];
	}
	
	private function trimdots($text){
		
		return trim($text, ". ");
	}
	
	private function unshitimage($url){
		
		// https://s1.qwant.com/thumbr/0x0/8/d/f6de4deb2c2b12f55d8bdcaae576f9f62fd58a05ec0feeac117b354d1bf5c2/th.jpg?u=https%3A%2F%2Fwww.bing.com%2Fth%3Fid%3DOIP.vvDWsagzxjoKKP_rOqhwrQAAAA%26w%3D160%26h%3D160%26c%3D7%26pid%3D5.1&q=0&b=1&p=0&a=0
		// https://s2.qwant.com/thumbr/474x289/7/f/412d13b3fe3a03eb2b89633c8e88b609b7d0b93cdd9a5e52db3c663e41e65e/th.jpg?u=https%3A%2F%2Ftse.mm.bing.net%2Fth%3Fid%3DOIP.9Tm_Eo6m7V7ltN19mxduDgHaEh%26pid%3DApi&q=0&b=1&p=0&a=0
		
		$image = parse_url($url);
		
		if(
			!isset($image["host"]) ||
			!isset($image["query"])
		){
			
			// cant do anything
			return $url;
		}
		
		$id = null;
		
		if(
			preg_match(
				'/s[0-9]+\.qwant\.com$/',
				$image["host"]
			)
		){
			
			parse_str($image["query"], $str);
			
			// we're being served a proxy URL
			if(isset($str["u"])){
				
				$bing_url = $str["u"];
			}else{
				
				// give up
				return $url;
			}
		}
		
		// parse bing URL
		$id = null;
		$image = parse_url($bing_url);
		
		if(isset($image["query"])){
			
			parse_str($image["query"], $str);
			
			if(isset($str["id"])){
				
				$id = $str["id"];
			}
		}
		
		if($id === null){
			
			$id = explode("/th/id/", $image["path"], 2);
			
			if(count($id) !== 2){
				
				// malformed
				return $url;
			}
			
			$id = $id[1];
		}
		
		if(is_array($id)){
			
			// fuck off, let proxy.php deal with it
			return $url;
		}
		
		return "https://" . $image["host"] . "/th?id=" . rawurlencode($id);
	}
	
	private function fuck_datadome($proxy, $search){
		
		if($cookie === null){
			// get datadome bullshit
			// yeah go ahead and patch this you code monkey
			
			// ... you know, instead of fixing this shit, can you make the second page of results return more than 1 result?
			// i search shit like "higurashi" and i only get 1 result and then just a bunch of related searches ad finitum
			// also, stop pretending you guys have your own index, this is clearly all bing results
			try{
				$datadome =
					$this->get(
						$proxy,
						"https://dd.qwant.com/js/",
						[
							"jspl" => "-oirHDeP7G3G9LXY3rhxGDYf6t_ZlYPzaiL2dRhQ34uNUc4dO2Td0SmmKu_VJp7-jrAIoOMrTqKgCtV4cPzntMyNuOYN7QQ1q0Fi2c-679vYpJr9ub5dRFXMhCMQmjZsOWXUa8yafIXf85UA5Goct-n4hVT5dFfBGM-dE6gwZEFkHQ1j1195sLybZFTNA1IfX26MBMxlVHoyT-aHtkd6YjGXbvOPMMsObVHDa_Cf4BmApRGo-eehsAttsCqDRwkl0M2E1b0OXCIwMVnCK-qWZ81MGaJGjqxdj_LaxhAjE9JI_4oNedAg4Yj4m_vkQBKdmWJT_bTUsllAZXqX5IxYQHJ8WvlknOTr6sR3wgoSOa_JNVHkUQxqXBbQcoDdy6VciL5rIqwWl4Y2caVYzeXa8YNXxwsfrPDegH3dQ6vOiIiCDT3bexkcS_y7GAioPPv9qDu7M9y4sI8t0VkPgMbWY6MIu5u9WAv6lEiKIYrtlyU-iiXnNdkd9EnpC1TD8_s12PtCuLg5bGgLVmI1dw8VXVZxmVyFvfBtuqXkzM-kZOyv-YIRcf-wPY8DUtnHox1d6t64oYhGv-pYV5dmZ0rGs99LC6ThiifeF_oHz5ZK46Ms68WsiAjc1HLAbFPFxWw8X6lyLlpT5aHgFdDBeCTExKXyItlc9PBxkgK8UnXHQFiWX3HTen8rziwkI31AbkMU7xGP6cvuwuLG7MA7sH3yLF1uimQlhr6EuTrImWQNV7X81DqyZ0OE7I-uT9bp_xISa5it-6AJpFtGcwz04_OuO2zvZazBeiKJI5Fw0X2r3RHTHdA29SXqTIe4y2LRAAp0MSAZUIJAMIbbrz9BG-3D5BHfQUA1Bbuklxv-tNWiuanWbbTmAiuxTAo9P-D2ZMrWHwdN8sCb3nINLur38z1xviSs2qEEjKo9kzQIWoHgwm_amV-jjDtNBXwpCQnoaqt3FfL8e59ecYWVg61Gnl3YCwq6rubFdkIv6UStcWSlj4pRFlFZSjQxzznvGTAc5Nz1JJWVeBHJItei4wqDUYEw2BNTS_fU9keV3LFyKDH_x248AxHLiXt583DhZZwPAKlpmMo0E7odzfOgVQGM5b9l64NL3RQRq8cDxI8KgmOFnPHdI7Myq1A3nW1uoBB8m9_GR_9orboKl5tAQrT0VTMRd6JyNwOOQ71ZoQpMwsD5Xd9YDZd618TNE9dA38h9OyBco_-hwKCYayyEqib1atLLBO_gnO4InR471AklOokDOKx1oY_xgNhlFrZskE4nQQOqeCQ_47oysob9s3kblA8UqDrL2vM3i2KPLWwlQzMAPrQAIv7Amp6rO-wYwBQdbP7XghYympxx9-hNxc1Duq-nS4R3pjPtZg3LoCvG1N_0JLf9jfPBqw_rdPM6DnjEOvpCdvrfv4wBlEpgqbaRQCZBPEz-O5ZlY24OyPvaNX0krPeXkbi-KYuwZSAbfW0zsksXb8cbRmz6tvh25IRYP1XNDPVd8NFrYOdDH9GzhV5MYZziYMurQlVwgumGLz85Y6qTHZU2D5qkMH7o5C5bzptReO96889oxkReS_LDhzdxH0tAQ5a9GgETJTHAXJ9Lt7X037i0qQlatbPLizOl9fo4vUBGJ7w-uSUmIzg1yIwOo8xhLu39WghXfoJ_EX5ptNi7arpAo11UoJIGEgWgq7bS40q2dtnG5HYpp9ef37iFgztM7PV5dlJf_W-n9uXJyCk2nhxZeuGiIGhMTzjP_8oeyiMFelmoyLlqK8wOs6hvWqgDD4NwtZc-XBkEAKsxuPrirb_sszp0z6BWsh3D6fBxAW_lTrNVkxZ1EBtoEN72HvO1aeX8PVuJP3CKwkkUcAhJ5Zvajo8t4Ai8ave2MTZul4QhDIe7ZALJMo71u-hNSj8YG9JLPqF1jcnbkl8ykt_XMMxX8wSZ_AfTsfmAuOWBfEzxv8Fl2Ne-oxcEtfuYnYNJIjQDHXXdtYbWKBezE41wonQu7FWh3wSSF3DDm9EocWMsUZ610nrIviOFdtTqjrY7KnOZp0hS9Hm2V04z-06Db-To71L5O5poYKuOlDAP_ULfW0hgMgYaPnt4Nive9gtue6ZQ5yNFylKM11zCWcF8vmuEPGx4NIZjvNFB-QSkdxZtBTQOgO6r4BqHJNZIDqiD9A6PyMemVtQEo-JhooX6bwHFHx6C84r-kKq1lg0RaDelbu1h6347kocxOczdgHWlagKPFrUvHpPHvCZeFmzLLmPWbDEpUhhfkbEZlNe47svO5WN1TYZMCAlE0g01pe6b_NB9-twQ4FJB1SkUQRA4o5DxiIHSgXBBfw1X98XPEKcJgCU2zRbYuU69mvyPcsxoPsLo8Cl2OAXj2U1tcLUxVhR_vu3I0SG_Ee8hxBp5b6QbpIu2sE0bgpqfi88XqUXf3qvxGbkoZDZ8dPvEvCVmpDZOOGJY5Db4MnpKBByW79lUnpbLCbsVOoxYu8SVmVQ63eGY9yBlACuWxlXA_PhCpnKYwMppjxKvKqalNJGMMg5WQ8cvhsIftE5wgaj21n0A15GwRO1WgUcsO_dLe1MqO88hTHWHV0Q3gNut3sdsmVr_n-0SniLtVAaw4IEtDZWNUfhpaoC7iyKxoJ86t8r697EYPlWUu4nTQhb3TOXAjHF22rFsJJMldKHVxjxjd086ky3OY3L_7cbjVof60-y4gTJdumw5hch-mhaT93nVnaTuPLGCAC48dZSgOitW8Dw62mPS05fTPAfKUY1HpyIWr9ACk7U2KVAijpzFB-bXvqJeCyBeUHr4cB61K2XxTmkNA8Oo9RC32TeYDrThFzoUyYZ_D6zPe1lLtQaFsPRvDBSsYCcr5fcABlST4DyUgnkgqtx-0-D1QEwtWiH3xiHLQGa82ldjqNIXL9LqwxYoCRBKuljHyqEylZD26A9Jp6SL9rk_Ol6TJlYa3zx44PHyMoA3Coy-HIZW5NMwVKdV7egUDDOchvuFnc1Zv2QpSFKc0JY8lYFtSm1HZFwjq7hf2hBqkU0Xneh8AT4Ty9QVb4mJ8rpzc8m_NBZNrpZVE-pIzmv2yGjnRwKSrwsjat7b2iY34UPFqGBlDk5FX6EfRlN9bq-nztWdextLc47ndr1gxsjC-vr6tbdXl5FxMVhbsnLH80wOC6hHubxCysr5C7Ft59s3MLhVQCc3IIOPMBhLOjjjVsu6K8kAJvvamIpj-iYUQj9T8JztAqfobytzbiwPsQ3mywUGkJpZxTjXjqkKdT6yfFdW5ksR8p3VlKGAAKft9TKKcc5IgErmIRtiZF4hGhB45nwIO5XHA59V94QuV3b0oJrleuDCBj0iDFqWYpsUVHyKl3RjrO86Yq1IbNRwXIIMrlsVYos9CS1I75k9kW4yQC6ni3a2ZitDkiEB-SoQuTlBsvfTs892Wt5q3CWH6_7srZO6l2QEAlWqyQp_P4i8j8uiEbZksGsgghVIgOkriblDP9ml7PdkpRX7S3OpY7jjQgTNR8XHaZ-SEw0-zPiqIIt5VioFK8JljPn-nMIDx-ew_vTDKqmKEOF39ezQcc5oQNTQNUvTAe8qCbK4ghS3fagItezoHtB5zm3qwhx9Iu6JMJy0UhywUZBaInY6NokiVZGDgtoqlVjOk7K4299MVZgsOr2P9QHRDo41lYnCeWWFUf50d-ouEOUO_ngteDpgG1DfV64eycrUOWAnpfI8XOUZ7mxS89SqJriw3T3tcljowHiOFw9QjYsjJi0vuHL5jfoBZVdYAUH6KiLfIScO-KgqitdUTpFwks8Qk7xfd2HcEfArlzSiAt3Hmb2Ty-5mhvtQj2RbPehgoI7gqa60b6Qffth-5tbIjXoKlZCQsaKVMAggF9C3w0Rv5uJ3ExCTGsfKO9IVOf--AfxaA8cNoO0W3NTaMH2iWftY0O1-1x3VJEcerp2v9BcZ0iIL3VFJ9eyOPYNHNwTYrI3S6OrVZxuQXFbu2E_Us32wxYqXvnMx-DEOk7bUUFIanL69Cbn_mR_9Wm1sFixBQ15diJoe1Oyl3XWHWL6_YF4XnHzZlMWySt3Awdeynwu99Ter8WMGXl-6K30novKRWC9jDVOfAtxPvXBkkRtpm8nnJT8fwkmIQoWOEPT2MCg3VYzzHzVEH-AueuRiCZj_o7L-fRqbtFrWPyVnGuetUYS5u0D3n7Rr6kCVqWhLfwjqAwV_calzIQs8-J0-AJe6GGaaONPYfXwXjYan0Sl11CS64lXvx7i3MgeOZuH210T6g6hj54dSLw0upO1L8k0FGli3I5OhLMekeGMLT_N2akyHktjbz8Odpk2ms0pJP_PdqvUegjXgH81F5lr40HAXZs7jfC9ytgcWaEjwlmnoFHSCTdlCIgJqEBzItXiz6ilk5zP35VwxdQTNoms7Meqg-ZrgRuLRaqXNtpQT9qWBmiFP6zoYLwymqytZ_P1SV9DCM-KIZQ2Cq-ew8PsYSrwl8GmfuegoKEMBiON1I4mC9ChDgzJmbo5P9te60T0URS5g9ExiQuSo-JXs0MKC57LLiNo3MPZvqR1wRJOTg0_FqS4JFxzPwN5OqhBzfs25f6kK-axf0wWp-LetUx3U1CTNaxfbCTK5tb8KnLOIs3lDwJw5H5wIIRgXnfNLBJT8A2srfXkdpA",
							"eventCounters" => [],
							"jsType" => "ch",
							"cid" => "3i4vPGHCl74uIBumWaxF2a0PjFkyCJxcSvFnn~heX7keA6sGHgRbnzSYcMr_RzJFlfeeBdg9HeufzFPxnvSHbyWowSvjm6mNCSPhkx6MwDqptANoj93ZgGVbrzxGtcYe",
							"ddk" => "78B13B7513D180B7AB6D6FF9EB0A51",
							"Referer" => "https://www.qwant.com/?q={$search}&t=web",
							"request" => "/?q={$search}&t=web",
							"responsePage" => "origin",
							"ddv" => "5.8.0"
						],
						true
					);
			}catch(Exception $error){
				
				throw new Exception("Failed to fetch JS challenge token");
			}
			
			$datadome = json_decode($datadome, true);
			if($datadome === null){
				
				throw new Exception("Failed to decode JS challenge JSON");
			}
			
			if(!isset($datadome["cookie"])){
				
				throw new Exception("Failed to get cookie from JS challenge endpoint");
			}
			
			$cookie = explode(";", $datadome["cookie"])[0];
		}
		
		return $cookie;
	}
}
