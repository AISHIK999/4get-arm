<?php

class purili{
	
	public function __construct(){
		
		include "lib/backend.php";
		$this->backend = new backend("purili");
	}
	
	public function getfilters($page){
		
		switch($page){
			case "web":
				return [
					"nsfw" => [
						"display" => "NSFW",
						"option" => [
							"yes" => "Yes",
							"maybe" => "Maybe",
							"no" => "No"
						]
					],
					"language" => [
						"display" => "Language",
						"option" => [
							"any" => "Any language",
							"en" => "English only",
							"nl" => "Dutch preferred",
							"de" => "German preferred",
							"fr" => "French preferred",
							"es" => "Spanish preferred",
							"it" => "Italian preferred",
							"pt" => "Portuguese preferred"
						]
					]
				];
				break;
			
			case "videos":
				return [];
				break;
		}
	}
	
	private function get($proxy, $url, $get = [], $nsfw = null, $language = null){
		
		$curlproc = curl_init();
		
		switch($nsfw){
			
			case "yes":
			case null:
				$get["fv"] = "0";
				$nsfw = "off";
				break;
				
			case "maybe":
				$get["fv"] = "1";
				$nsfw = "moderate";
				break;
				
			case "no":
				$get["fv"] = "2";
				$nsfw = "strict";
				break;
		}
		
		switch($language){
			
			case "any":
			case null:
				$language_cookie = "";
				break;
			
			default:
				$language_cookie = "; purili-language={$language}";
				break;
		}
		
		if($get !== []){
			$get = http_build_query($get);
			$url .= "?" . $get;
		}
		
		curl_setopt($curlproc, CURLOPT_URL, $url);
		
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		curl_setopt($curlproc, CURLOPT_HTTPHEADER,
			["User-Agent: " . config::USER_AGENT,
			"Accept: */*",
			"Accept-Language: en-US,en;q=0.9",
			"Accept-Encoding: gzip, deflate, br, zstd",
			"DNT: 1",
			"Sec-GPC: 1",
			"Connection: keep-alive",
			"Cookie: purili-safesearch={$nsfw}{$language_cookie}",
			"Sec-Fetch-Dest: empty",
			"Sec-Fetch-Mode: cors",
			"Sec-Fetch-Site: same-origin",
			"Priority: u=4"]
		);
		
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
			
			[$q, $proxy] = $this->backend->get($get["npt"], "web");
			$q = json_decode($q, true);
			
			$nsfw = $q["nsfw"];
			unset($q["nsfw"]);
			
			$language = $q["language"];
			unset($q["language"]);
		}else{
			
			$search = $get["s"];
			if(strlen($search) === 0){
				
				throw new Exception("Search term is empty!");
			}
			
			$proxy = $this->backend->get_ip();
			
			$q = [
				"q" => $search,
				"page" => 1,
				// added at $this->get()
				// "fv" => 0
			];
			
			$nsfw = $get["nsfw"];
			$language = $get["language"];
		}
		
		// https://puri.li/api/search?q=4get&page=1&fv=0
		try{
			$json = $this->get(
				$proxy,
				"https://puri.li/api/search",
				$q,
				$nsfw,
				$language
			);
		}catch(Exception $error){
			
			throw new Exception("Failed to hit API");
		}
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Failed to decode JSON");
		}
		
		if(!isset($json["results"])){
			
			throw new Exception("Failed to access results array");
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
		
		foreach($json["results"] as $result){
			
			$out["web"][] = [
				"title" => $result["title"],
				"description" => $result["description"],
				"url" => $result["url"],
				"date" => null,
				"type" => "web",
				"thumb" => [
					"url" => null,
					"ratio" => null
				],
				"sublink" => [],
				"table" => []
			];
		}
		
		// get next page
		if($json["hasNext"]){
			
			$q["page"]++;
			$q["nsfw"] = $nsfw;
			$q["language"] = $language;
			
			$out["npt"] =
				$this->backend->store(
					json_encode($q),
					"web",
					$proxy
				);
		}
		
		return $out;
	}
	
	public function video($get){
		
		
		if($get["npt"]){
			
			[$q, $proxy] = $this->backend->get($get["npt"], "videos");
			$q = json_decode($q, true);
			
		}else{
			
			$search = $get["s"];
			if(strlen($search) === 0){
				
				throw new Exception("Search term is empty!");
			}
			
			$proxy = $this->backend->get_ip();
			
			$q = [
				"q" => $search,
				"offset" => 0,
				"limit" => 12
				// added at $this->get()
				// "fv" => 0
			];
		}
		
		// https://puri.li/api/videos/search?q=higurashi&limit=12&fv=1
		// hidden &offset parameter only accessible through the API
		try{
			$json = $this->get(
				$proxy,
				"https://puri.li/api/videos/search",
				$q,
				null,
				null
			);
		}catch(Exception $error){
			
			throw new Exception("Failed to hit API");
		}
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Failed to decode JSON");
		}
		
		if(!isset($json["results"])){
			
			throw new Exception("Failed to access results array");
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
		
		foreach($json["results"] as $result){
			
			if(isset($result["thumbnail"])){
				
				$thumb = [
					"ratio" => "16:9",
					"url" => $result["thumbnail"]
				];
			}else{
				
				$thumb = [
					"ratio" => null,
					"url" => null
				];
			}
			
			$out["video"][] = [
				"title" => $result["title"],
				"description" =>
					str_replace(
						"\n", " ",
						mb_substr(
							$result["description"], 0, 255, "utf-8"
						)
					),
				"author" => [
					"name" => null,
					"url" => isset($result["channel_id"]) ? "https://www.youtube.com/channel/" . $result["channel_id"] : null,
					"avatar" => null
				],
				"date" => strtotime($result["upload_date"]),
				"duration" => $result["duration"],
				"views" => $result["view_count"],
				"thumb" => $thumb,
				"url" => $result["url"]
			];
		}
		
		// get next page
		if($json["limit"] + $json["offset"] < $json["total"]){
			
			$q["offset"] = $q["offset"] + $json["limit"];
			
			$out["npt"] =
				$this->backend->store(
					json_encode($q),
					"videos",
					$proxy
				);
		}
		
		return $out;
	}
}
