<?php

class google{
	
	public function __construct(){
		
		include "lib/backend.php";
		$this->backend = new backend("google");
		
		include "lib/fuckhtml.php";
		$this->fuckhtml = new fuckhtml();
	}
	
	private function get(/*$proxy, */$url, $get = []){
		
		$curlproc = curl_init();
		
		if($get !== []){
			$get = http_build_query($get);
			$url .= "?" . $get;
		}
		
		curl_setopt($curlproc, CURLOPT_URL, $url);
		
		// http2 bypass
		curl_setopt($curlproc, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		
		$headers =
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
			"Priority: u=0, i"];
		
		// i dont think proxies are necessary here...
		//$this->backend->assign_proxy($curlproc, $proxy);
		
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		curl_setopt($curlproc, CURLOPT_HTTPHEADER, $headers);
		
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
	
	function resolve($id){
		
		$html =
			$this->get(
				//$this->backend->get_ip(),
				"https://www.google.com/goto",
				[
					"url" => $id
				]
			);
		
		$this->fuckhtml->load($html);
		
		$a =
			$this->fuckhtml
			->getElementsByTagName(
				"a"
			);
		
		if(count($a) === 0){
			
			throw new Exception("Failed to find link");
		}
		
		$link =
			$this->fuckhtml
			->getTextContent(
				$a[0]["attributes"]["href"]
			);
		
		if(
			preg_match(
				'/^(https?:|)\/\//',
				$link
			)
		){
			
			return $link;
		}
		
		throw new Exception("Got malformed link: " . $link);
	}
}
