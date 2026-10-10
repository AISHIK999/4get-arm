<?php

class coccoc{
	
	public function __construct(){
		
		include "lib/backend.php";
		$this->backend = new backend("coccoc");
		
		include "lib/fuckhtml.php";
		$this->fuckhtml = new fuckhtml();
		
		$this->proc = curl_multi_init();
		$this->handles = [];
	}
	
	
	private function get($proxy, $url, $cookie = false, $get = []){
		
		$curlproc = curl_init();
		
		if($get !== []){
			$get = http_build_query($get);
			$url .= "?" . $get;
		}
		
		curl_setopt($curlproc, CURLOPT_URL, $url);
		
		// http2 bypass
		curl_setopt($curlproc, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		
		if($cookie === false){
			
			curl_setopt($curlproc, CURLOPT_HTTPHEADER, [
				"User-Agent: " . config::USER_AGENT,
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: en-CA,en-US;q=0.9,en;q=0.8",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"DNT: 1",
				"Sec-GPC: 1",
				"Connection: keep-alive",
				"Upgrade-Insecure-Requests: 1",
				"Sec-Fetch-Dest: document",
				"Sec-Fetch-Mode: navigate",
				"Sec-Fetch-Site: none",
				"Sec-Fetch-User: ?1",
				"Priority: u=0, i"
			]);
		}else{
			
			curl_setopt($curlproc, CURLOPT_HTTPHEADER, [
				"User-Agent: " . config::USER_AGENT,
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: en-CA,en-US;q=0.9,en;q=0.8",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"DNT: 1",
				"Sec-GPC: 1",
				"Connection: keep-alive",
				"Referer: https://coccoc.com/search",
				"Cookie: {$cookie}",
				"Upgrade-Insecure-Requests: 1",
				"Sec-Fetch-Dest: document",
				"Sec-Fetch-Mode: navigate",
				"Sec-Fetch-Site: same-origin",
				"Priority: u=0, i",
				"TE: trailers"
			]);
		}
		
		$this->backend->assign_proxy($curlproc, $proxy);
		
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		
		curl_setopt($curlproc, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curlproc, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curlproc, CURLOPT_TIMEOUT, 30);
		
		$data = curl_exec($curlproc);
		
		if(curl_errno($curlproc)){
			throw new Exception(curl_error($curlproc));
		}
		
		curl_close($curlproc);
		return $data;
	}
	
	public function getfilters($pagetype){
		
		return [
			"nsfw" => [
				"display" => "NSFW",
				"option" => [
					"yes" => "Yes", // nsfw by default????
					"no" => "No" // &safe=1
				]
			],
			"time" => [
				"display" => "Time posted",
				"option" => [
					"any" => "Any time",
					"1w" => "1 week ago",
					"2w" => "2 weeks ago",
					"1m" => "1 month ago",
					"3m" => "3 months ago",
					"6m" => "6 months ago",
					"1Y" => "1 year ago"
				]
			],
			"filter" => [
				"display" => "Remove duplicates",
				"option" => [
					"no" => "No",
					"yes" => "Yes" // &filter=0
				]
			]
		];
	}
	
	private function handle_redir($proxy, $html){
		
		$this->fuckhtml->load($html);
		
		$title =
			$this->fuckhtml
			->getElementsByTagName("title");
		
		if(
			count($title) !== 0 &&
			$this->fuckhtml
			->getTextContent(
				$title[0]
			) == "Redirecting..."
		){
			
			// get cookie
			if(
				preg_match(
					'/document\.cookie ?= ?"([^;"]+).*"/',
					$html,
					$cookie
				) === 0
			){
				
				throw new Exception("Failed to grep cookie");
			}
			
			// get redirect URL
			if(
				preg_match(
					'/window\.location\.replace\("([^"]+)"\)/',
					$html,
					$redir
				) === 0
			){
				
				throw new Exception("Failed to grep redirection URL");
			}
			
			try{
				
				$html =
					$this->get(
						$proxy,
						$redir[1],
						$cookie[1],
						[]
					);
			}catch(Exception $error){
				
				throw new Exception("Failed to get redirection page");
			}
		}
		
		return $html;
	}
	
	private function redirect_add_url($url){
		
		// https://coccoc.com/composer/goto?url=0AWtXcuK-mJVvFl8Qhny9LZdMOyEd08nFmTtpg82bmuqtp8p8G_OnTebNhPtNyIXMGQ1TZZ-ADMz0QEiFJhsTLIFkkPDXnJjHo3kqsobmyntiOhFag-2aqTs
		
		if(
			preg_match(
				'/\/goto\?url=([^&]+)/',
				$url,
				$slug
			) === 0
		){
			
			// not a coccoc redirect
			return $url;
		}
		
		$curlproc = curl_init();
		
		curl_setopt($curlproc, CURLOPT_URL, "https://coccoc.com/composer/goto?url=" . urlencode($slug[1]));
		
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		curl_setopt($curlproc, CURLOPT_HTTPHEADER,
			["User-Agent: " . config::USER_AGENT,
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			"Accept-Language: en-US,en;q=0.5",
			"Accept-Encoding: gzip, deflate, br, zstd",
			"DNT: 1",
			"Sec-GPC: 1",
			"Connection: keep-alive",
			"Upgrade-Insecure-Requests: 1",
			"Sec-Fetch-Dest: document",
			"Sec-Fetch-Mode: navigate",
			"Sec-Fetch-Site: none",
			"Sec-Fetch-User: ?1",
			"Priority: u=0, i"]
		);
		
		curl_setopt($curlproc, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curlproc, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curlproc, CURLOPT_TIMEOUT, 30);
		
		// funny
		curl_setopt($curlproc, CURLOPT_HEADER, true);
		
		//$this->backend->assign_proxy($curlproc, $proxy);
		
		curl_multi_add_handle($this->proc, $curlproc);
		$this->handles[] = [
			"replacement" => $url,
			"handle" => $curlproc
		];
		
		return $url;
	}
	
	private function resolve_urls(&$scraped_data){
		
		do{
			do{
				$status = curl_multi_exec($this->proc, $active);
			}while($status === CURLM_CALL_MULTI_PERFORM);

			if($active){
				curl_multi_select($this->proc);
			}

		}while($active && $status === CURLM_OK);
		
		//
		// if we reach this, we're done downloading garbage
		//
		
		// parse redirect values & create data shit
		$keys = [];
		$replacements = [];
		
		for($i=0; $i<count($this->handles); $i++){
			
			$headers =
				curl_multi_getcontent(
					$this->handles[$i]["handle"]
				);
			
			if(
				!preg_match(
					'/^location: ?([^ ]+)/im',
					$headers,
					$link
				)
			){
				
				// failed to extract redirect location
				continue;
			}
			
			// theyre too retarded to encode shit properly apparently.
			// this is the best i can do, it will still yield errors when searching for "higurashi"
			// example: https://coccoc.com/composer/goto?url=0AWtXcuIKWPOkxvarGvy3GJ36XipRirzzqAvltMM5QUfB1RPu49LrNB9qmuAQyvvI0eCTwf-FpjRjxUN8XbruhA0rvCHNNlhVNEozMUqgRcjzsdhyUMCEgwLCXVTQkXnLXqrUw8celcIZdfmZ1geOvioUcg
			// redirects to https://vi.wikipedia.org/wiki/Higurashi_no_Naku_Koro_ni#C%E1ch_ch?i
			// notice the encoding errors after #
			// retards
			
			$link = mb_convert_encoding($link[1], "UTF-8", "ISO-8859-1");
			
			if(
				preg_match(
					'/^(https?:|)\/\//',
					$link
				)
			){
				
				$keys[] = $this->handles[$i]["replacement"];
				$replacements[] = $link;
			}
		}
		
		$this->recursive_replace($keys, $replacements, $scraped_data);
		
		curl_multi_close($this->proc);
	}
	
	private function recursive_replace(&$keys, &$replacements, &$pointer){
		
		foreach($pointer as $key => &$value){
			
			if($key == "url"){
				
				$search =
					array_search(
						$value,
						$keys
					);
				
				if($search !== false){
					
					// replace url
					$value = $replacements[$search];
				}
				
			}elseif(is_array($value)){
				
				$this->recursive_replace($keys, $replacements, $value);
			}
		}
	}
	
	public function web($get){
		
		if($get["npt"]){
			
			[$query, $proxy] =
				$this->backend->get(
					$get["npt"],
					"web"
				);
			
			$query = json_decode($query, true);
		}else{
			
			$proxy = $this->backend->get_ip();
			
			$query = [
				"query" => $get["s"]
			];
			
			// add filters
			if($get["nsfw"] == "no"){
				
				$query["safe"] = 1;
			}
			
			if($get["time"] != "any"){
				
				$query["tbs"] = $get["time"];
			}
			
			if($get["filter"] == "yes"){
				
				$query["filter"] = 0;
			}
		}
		
		try{
			
			$html =
				$this->get(
					$proxy,
					"https://coccoc.com/search",
					false,
					$query
				);
		}catch(Exception $error){
			
			throw new Exception("Failed to get search page");
		}
		//$html = file_get_contents("scraper/coccoc.html");
		
		$html = $this->handle_redir($proxy, $html);
		
		$html = explode("window.composerResponse", $html, 2);
		
		if(count($html) !== 2){
			
			throw new Exception("Failed to grep window.composerResponse");
		}
		
		$html =
			json_decode(
				$this->fuckhtml
				->extract_json(
					ltrim($html[1], " =")
				),
				true
			);
		
		if($html === null){
			
			throw new Exception("Failed to decode JSON");
		}
		
		if(
			isset($html["captcha"]) &&
			(int)$html["captcha"] === 1
		){
			
			throw new Exception("Coc Coc returned a Captcha");
		}
		
		if(!isset($html["search"]["search_results"])){
			
			throw new Exception("Coc Coc did not return a search_results object");
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
		
		// word correction
		foreach($html["top"] as $element){
			
			if(isset($element["spellChecker"][0]["query"])){
				
				$out["spelling"] = [
					"type" => "not_many",
					"using" => $html["search"]["query"],
					"correction" => $element["spellChecker"][0]["query"]
				];
			}
		}
		
		foreach($html["search"]["search_results"] as $result){
			
			if(isset($result["type"])){
				
				switch($result["type"]){
					
					//
					// Related searches
					//
					case "related_queries":
						$out["related"] = $result["queries"];
						continue 2;
					
					//
					// Videos
					//
					case "video_hits":
						foreach($result["results"] as $video){
							
							if(
								isset($video["image_url"]) &&
								!empty($video["image_url"])
							){
								
								$thumb = [
									"ratio" => "16:9",
									"url" => $video["image_url"]
								];
							}else{
								
								$thumb = [
									"ratio" => null,
									"url" => null
								];
							}
							
							$out["video"][] = [
								"title" =>
									$this->titledots(
										$this->fuckhtml
										->getTextContent(
											$video["title"]
										)
									),
								"description" => null,
								"author" => [
									"name" => $video["uploader"],
									"url" => null,
									"avatar" => null
								],
								"date" => (int)$video["date"],
								"duration" => (int)$video["duration"],
								"views" => null,
								"thumb" => $thumb,
								"url" => $video["url"]
							];
						}
						continue 2;
				}
			}
			
			if(
				!isset($result["title"]) ||
				!isset($result["url"])
			){
				
				// should not happen
				continue;
			}
			
			if(isset($result["rich"]["data"]["image_url"])){
				
				$thumb = [
					"url" => $result["rich"]["data"]["image_url"],
					"ratio" => "16:9"
				];
			}else{
				
				$thumb = [
					"url" => null,
					"ratio" => null
				];
			}
			
			$sublinks = [];
			
			if(isset($result["rich"]["data"]["linked_docs"])){
				
				foreach($result["rich"]["data"]["linked_docs"] as $sub){
					
					$sublinks[] = [
						"title" =>
							$this->titledots(
								$this->fuckhtml
								->getTextContent(
									$sub["title"]
								)
							),
						"description" =>
							$this->titledots(
								$this->fuckhtml
								->getTextContent(
									$sub["content"]
								)
							),
						"date" => null,
						"url" => $this->redirect_add_url($sub["url"])
					];
				}
			}
			
			// get date
			if(isset($result["date"])){
				
				$date = (int)$result["date"];
			}else{
				
				$date = null;
			}
			
			// probe for metadata
			$table = [];
			
			if(isset($result["rich"]["data"]["rating"])){
				
				$table["Rating"] = $result["rich"]["data"]["rating"];
				
				if(isset($result["rich"]["data"]["num_rating"])){
					
					$table["Rating"] .= " (" . number_format($result["rich"]["data"]["num_rating"]) . " ratings)";
				}
			}
			
			if(isset($result["rich"]["data"]["views"])){
				
				$table["Views"] = number_format($result["rich"]["data"]["views"]);
			}
			
			if(isset($result["rich"]["data"]["duration"])){
				
				$table["Duration"] = $this->int2hms($result["rich"]["data"]["duration"]);
			}
			
			if(isset($result["rich"]["data"]["channel_name"])){
				
				$table["Author"] = $result["rich"]["data"]["channel_name"];
			}
			
			if(isset($result["rich"]["data"]["video_quality"])){
				
				$table["Quality"] = $result["rich"]["data"]["video_quality"];
			}
			
			if(isset($result["rich"]["data"]["category"])){
				
				$table["Category"] = $result["rich"]["data"]["category"];
			}
			
			$out["web"][] = [
				"title" =>
					$this->titledots(
						$this->fuckhtml
						->getTextContent(
							$result["title"]
						)
					),
				"description" =>
					$this->titledots(
						$this->fuckhtml
						->getTextContent(
							$result["content"]
						)
					),
				"url" => $this->redirect_add_url($result["url"]),
				"date" => $date,
				"type" => "web",
				"thumb" => $thumb,
				"sublink" => $sublinks,
				"table" => $table
			];
		}
		
		//
		// Get wikipedia head
		//
		if(isset($html["right"])){
			
			foreach($html["right"] as $wiki){
				
				$description = [];
				
				if(isset($wiki["short_intro"])){
					
					$description[] =
						[
							"type" => "quote",
							"value" => $wiki["short_intro"],
						];
				}
				
				if(isset($wiki["intro"])){
					
					$description[] =
						[
							"type" => "text",
							"value" => $wiki["intro"],
						];
				}
				
				// get table elements
				$table = [];
				
				if(isset($wiki["fields"])){
					
					foreach($wiki["fields"] as $element){
						
						$table[$element["title"]] = implode(", ", $element["value"]);
					}
				}
				
				// get sublinks
				$sublinks = [];
				
				if(isset($wiki["website"])){
					
					if(
						preg_match(
							'/^http/',
							$wiki["website"]
						) === 0
					){
						
						$sublinks["Website"] = "https://" . $wiki["website"];
					}else{
						
						$sublinks["Website"] = $wiki["website"];
					}
				}
				
				if(isset($wiki["profiles"])){
					
					foreach($wiki["profiles"] as $sitename => $url){
						
						$sitename = explode("_", $sitename);
						$sitename = ucfirst($sitename[count($sitename) - 1]);
						
						$sublinks[$sitename] = $url;
					}
				}
				
				$out["answer"][] = [
					"title" =>
						isset($wiki["title"]) ?
							$this->titledots(
								$wiki["title"]
							) : null,
					"description" => $description,
					"url" => null,
					"thumb" => isset($wiki["image"]["contentUrl"]) ? $wiki["image"]["contentUrl"] : null,
					"table" => $table,
					"sublink" => $sublinks
				];
			}
		}
		
		// get next page
		if((int)$html["search"]["page"] < (int)$html["search"]["max_page"]){
			
			// https://coccoc.com/composer?_=1754021153532&p=0&q=zbabduiqwhduwqhdnwq&reqid=bwcAs00q&s=direct&apiV=1
			// ^json endpoint, but we can just do &page=2 lol
			
			if(!isset($query["page"])){
				
				$query["page"] = 2;
			}else{
				
				$query["page"]++;
			}
			
			$out["npt"] =
				$this->backend
				->store(
					json_encode($query),
					"web",
					$proxy
				);
		}
		
		$this->resolve_urls($out);
		return $out;
	}
	
	public function video($get){
		
		//$html = file_get_contents("scraper/coccoc.html");
		if($get["npt"]){
			
			[$query, $proxy] =
				$this->backend->get(
					$get["npt"],
					"videos"
				);
			
			$query = json_decode($query, true);
		}else{
			
			$proxy = $this->backend->get_ip();
			
			$query = [
				"query" => $get["s"],
				"tbm" => "vid"
			];
			
			// add filters
			if($get["nsfw"] == "no"){
				
				$query["safe"] = 1;
			}
			
			if($get["time"] != "any"){
				
				$query["tbs"] = $get["time"];
			}
			
			if($get["filter"] == "yes"){
				
				$query["filter"] = 0;
			}
		}
		
		try{
			
			$html =
				$this->get(
					$proxy,
					"https://coccoc.com/search",
					false,
					$query
				);
		}catch(Exception $error){
			
			throw new Exception("Failed to get search page");
		}
		
		$html = $this->handle_redir($proxy, $html);
		
		$html = explode("window.composerResponse", $html, 2);
		
		if(count($html) !== 2){
			
			throw new Exception("Failed to grep window.composerResponse");
		}
		
		$html =
			json_decode(
				$this->fuckhtml
				->extract_json(
					ltrim($html[1], " =")
				),
				true
			);
		
		if($html === null){
			
			throw new Exception("Failed to decode JSON");
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
		
		if(!isset($html["search_video"]["search_results"])){
			
			if(isset($html["search_video"]["error"]["title"])){
				
				if($html["search_video"]["error"]["title"] == "Không tìm thấy kết quả nào"){
					
					return $out;
				}
				
				throw new Exception("Coc Coc returned an error: " . $html["search_video"]["error"]["title"]);
			}
			
			throw new Exception("Coc Coc did not supply a search_results object");
		}
		
		foreach($html["search_video"]["search_results"] as $video){
			
			if(isset($video["rich"]["data"]["image_url"])){
				
				$thumb = [
					"ratio" => "16:9",
					"url" => $video["rich"]["data"]["image_url"]
				];
			}else{
				
				$thumb = [
					"ratio" => null,
					"url" => null
				];
			}
			
			$out["video"][] = [
				"title" =>
					$this->titledots(
						$this->fuckhtml
						->getTextContent(
							$video["title"]
						)
					),
				"description" =>
					$this->titledots(
						$this->fuckhtml
						->getTextContent(
							$video["content"]
						)
					),
				"author" => [
					"name" => 
						isset($video["rich"]["data"]["channel_name"]) ?
							$video["rich"]["data"]["channel_name"] : null,
					"url" => null,
					"avatar" => null
				],
				"date" =>
					isset($video["date"]) ?
						$video["date"] : null,
				"duration" =>
					isset($video["rich"]["data"]["duration"]) ?
						(int)$video["rich"]["data"]["duration"] : null,
				"views" => null,
				"thumb" => $thumb,
				"url" => $this->redirect_add_url($video["url"])
			];
		}
		
		// get next page
		if((int)$html["search_video"]["page"] < (int)$html["search_video"]["max_page"]){
			
			if(!isset($query["page"])){
				
				$query["page"] = 2;
			}else{
				
				$query["page"]++;
			}
			
			$out["npt"] =
				$this->backend
				->store(
					json_encode($query),
					"videos",
					$proxy
				);
		}
		
		$this->resolve_urls($out);
		return $out;
	}
	
	private function titledots($title){
		
		return trim($title, " .\t\n\r\0\x0B…");
	}
	
	private function int2hms($seconds){
		
		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds % 3600) / 60);
		$seconds = $seconds % 60;
		
		return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
	}
}
