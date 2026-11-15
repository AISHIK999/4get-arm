<?php

include "../../data/config.php";
new autocomplete();

class autocomplete{
	
	public function __construct(){
		
		header("Content-Type: application/x-suggestions+json");
		
		$this->scrapers = [
			"brave" => "https://search.brave.com/api/suggest?q={searchTerms}",
			"bing" => "https://www.bing.com/osjson.aspx?query={searchTerms}",
			"ddg" => "https://duckduckgo.com/ac/?q={searchTerms}&type=list",
			"ecosia" => "https://ac.ecosia.org/autocomplete?q={searchTerms}&type=list",
			"yandex" => "https://suggest.yandex.com/suggest-ff.cgi?part={searchTerms}&uil=en&v=3",
			"google" => "https://www.google.com/complete/search?client=chrome&q={searchTerms}",
			"qwant" => "https://api.qwant.com/v3/suggest/?q={searchTerms}&client=opensearch",
			"marginalia" => "https://marginalia-search.com/suggest/?partial={searchTerms}",
			"yt" => "https://suggestqueries-clients6.youtube.com/complete/search?client=youtube&q={searchTerms}",
			"sc" => "",
			"kagi" => "https://kagisuggest.com/api/autosuggest?q={searchTerms}",
			"startpage" => "https://www.startpage.com/osuggestions?q={searchTerms}",
			"yahoo" => "https://search.yahoo.com/sugg/gossip/gossip-us-ura/?output=sd1&command={searchTerms}",
			"purili" => "https://puri.li/api/suggest?q={searchTerms}"
		];
		
		/*
			Sanitize input
		*/
		if(!isset($_GET["s"])){
			
			// Missing search(s) parameter
			$this->doempty();
		}
		
		if(is_string($_GET["s"]) === false){
			
			// Invalid search(s) parameter
			$this->doempty();
		}
		
		if(strlen($_GET["s"]) > 500){
			
			// Search(s) exceeds the 500 char length
			$this->doempty();
		}
		
		/*
			Get $scraper
		*/
		if(!isset($_GET["scraper"])){
			
			if(isset($_COOKIE["scraper_ac"])){
				
				$scraper = $_COOKIE["scraper_ac"];
			}else{
				
				$scraper = "brave"; // default option
			}
		}else{
			
			$scraper = $_GET["scraper"];
		}
		
		if($scraper == "disabled"){
			
			// this shouldnt happen, but let's handle it anyways
			$this->doempty();
		}
		
		// make sure it exists
		if(!isset($this->scrapers[$scraper])){
			
			$scraper = "brave"; // default option
		}
		
		// return results
		switch($scraper){
			
			case "yt":
				// handle youtube cause it wants to be a special snowflake :(
				$js = $this->get($this->scrapers[$scraper], $_GET["s"]);
				
				preg_match(
					'/\((\[.*\])\)/',
					$js,
					$js
				);
				
				if(!isset($js[1])){
					
					$this->doempty();
				}
				
				$js = json_decode($js[1]);
				$json = [];
				
				foreach($js[1] as $item){
					
					$json[] = htmlspecialchars_decode(strip_tags($item[0]));
				}
				
				echo json_encode(
					[
						$_GET["s"],
						$json
					],
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE
				);
				break;
			
			case "sc":
				// soundcloud
				chdir("../../");
				include "scraper/sc.php";
				$sc = new sc();
				
				$token = $sc->get_token("raw_ip::::");
				
				$js = $this->get(
					"https://api-v2.soundcloud.com/search/queries?q={searchTerms}&client_id=" . $token . "&limit=10&offset=0&linked_partitioning=1&app_version=1693487844&app_locale=en",
					$_GET["s"]
				);
				
				$js = json_decode($js, true);
				
				if(!isset($js["collection"])){
					
					$this->doempty();
				}
				
				$json = [];
				foreach($js["collection"] as $item){
					
					$json[] = $item["query"];
				}
				
				echo json_encode(
					[
						$_GET["s"],
						$json
					],
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE
				);
				break;
			
			case "marginalia":
			case "purili":
				$json = $this->get($this->scrapers[$scraper], $_GET["s"]);
				
				$json = json_decode($json, true);
				if($json === null){
					
					$this->doempty();
				}
				
				echo json_encode(
					[
						$_GET["s"],
						$json
					],
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE
				);
				break;
			
			case "yahoo":
				$json = $this->get($this->scrapers[$scraper], $_GET["s"]);
				
				$json = json_decode($json, true);
				if(
					$json === null ||
					!isset($json["r"][0]["k"])
				){
					
					$this->doempty();
				}
				
				$payload = [];
				
				foreach($json["r"] as $term){
					
					if(isset($term["k"])){
						
						$payload[] = $term["k"];
					}
				}
				
				echo json_encode(
					[
						$_GET["s"],
						$payload
					],
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE
				);
				break;
			
			default:
				// if it respects the openSearch protocol
				$json = json_decode($this->get($this->scrapers[$scraper], $_GET["s"]), true);
				
				if(
					!isset($json[1]) ||
					!is_array($json[1])
				){
					
					$json[1] = [];
				}
				
				echo json_encode(
					[
						$_GET["s"],
						$json[1] // ensure it contains valid key 0
					],
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE
				);
				break;
		}
	}
	
	private function get($url, $query){
		
		try{
			$curlproc = curl_init();
			
			$url = str_replace("{searchTerms}", urlencode($query), $url);
			
			curl_setopt($curlproc, CURLOPT_URL, $url);
			
			curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
			curl_setopt($curlproc, CURLOPT_HTTPHEADER,
				["User-Agent: " . config::USER_AGENT,
				"Accept: application/json, text/javascript, */*; q=0.01",
				"Accept-Language: en-US,en;q=0.5",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"DNT: 1",
				"Connection: keep-alive",
				"Sec-Fetch-Dest: empty",
				"Sec-Fetch-Mode: cors",
				"Sec-Fetch-Site: same-site"]
			);
			
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
		
		}catch(Exception $error){
			
			doempty();
		}
	}
	
	private function doempty(){
		
		echo json_encode(
			[
				$_GET["s"],
				[]
			],
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE
		);
		die();
	}
}
