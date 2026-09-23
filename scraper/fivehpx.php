<?php

class fivehpx{
	
	public function __construct(){
		
		include "lib/backend.php";
		$this->backend = new backend("fivehpx");
		
		include "lib/fuckhtml.php";
		$this->fuckhtml = new fuckhtml();
	}
	
	public function getfilters($page){
		
		return [
			"sort" => [
				"display" => "Sort",
				"option" => [
					"relevance" => "Relevance",
					"pulse" => "Pulse",
					"newest" => "Newest",
					"aqs" => "AI Score"
				]
			],
			"category" => [
				"display" => "Category",
				"option" => [
					"any" => "Any category",
					"landscape,nature" => "Landscape",
					"city" => "City",
					"events,sports" => "Events",
					"creative" => "Creative"
				]
			],/*
			"gear" => [
				"display" => "Gear",
				"option" => [
					"any" => "Any gear",
					"camera" => "Camera",
					"phone" => "Phone"
				]
			],*/
			"downloadable" => [
				"display" => "Downloadable",
				"option" => [
					"any" => "Doesn't matter",
					"yes" => "Yes"
				]
			]
		];
	}
	
	private function get($proxy, $url, $get = [], $post_data = null){
		
		$curlproc = curl_init();
		
		if($get !== []){
			$get = http_build_query($get);
			$url .= "?" . $get;
		}
		
		curl_setopt($curlproc, CURLOPT_URL, $url);
		
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		
		if($post_data === null){
			
			curl_setopt($curlproc, CURLOPT_HTTPHEADER,
				["User-Agent: " . config::USER_AGENT,
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
				"Accept-Language: en-US,en;q=0.5",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"DNT: 1",
				"Sec-GPC: 1",
				"Connection: keep-alive",
				"Upgrade-Insecure-Requests: 1",
				"Sec-Fetch-Dest: document",
				"Sec-Fetch-Mode: navigate",
				"Sec-Fetch-Site: same-origin",
				"Sec-Fetch-User: ?1",
				"Priority: u=0, i",
				"TE: trailers"]
			);
		}else{
			
			curl_setopt($curlproc, CURLOPT_HTTPHEADER,
				["User-Agent: " . config::USER_AGENT,
				"Accept: application/graphql-response+json,application/json;q=0.9",
				"Accept-Language: en-CA,en-US;q=0.9,en;q=0.8",
				"Accept-Encoding: gzip, deflate, br, zstd",
				"Referer: https://500px.com/",
				"content-type: application/json",
				"x-500px-platform: Web",
				"Content-Length: " . strlen($post_data),
				"Origin: https://500px.com",
				"DNT: 1",
				"Sec-GPC: 1",
				"Alt-Used: api-neo.500px.com",
				"Connection: keep-alive",
				"Cookie: x-500px-device-id-prod=a7b57e48-e477-4382-8c43-70c74415eb96",
				"Sec-Fetch-Dest: empty",
				"Sec-Fetch-Mode: cors",
				"Sec-Fetch-Site: same-site",
				"Priority: u=4",
				"TE: trailers"]
			);
						
			// set post data
			curl_setopt($curlproc, CURLOPT_POST, true);
			curl_setopt($curlproc, CURLOPT_POSTFIELDS, $post_data);
		}
		
		curl_setopt($curlproc, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curlproc, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curlproc, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curlproc, CURLOPT_TIMEOUT, 30);
		
		// http2 bypass
		curl_setopt($curlproc, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		
		$this->backend->assign_proxy($curlproc, $proxy);
		
		$data = curl_exec($curlproc);
		
		if(curl_errno($curlproc)){
			
			throw new Exception(curl_error($curlproc));
		}
		
		curl_close($curlproc);
		return $data;
	}
	
	public function image($get){
		
		if($get["npt"]){
			
			[$variables, $proxy] =
				$this->backend->get(
					$get["npt"], "images"
				);
			
			$variables = json_decode($variables, true);
		}else{
			
			$search = $get["s"];
			if(strlen($search) === 0){
				
				throw new Exception("Search term is empty!");
			}
			
			$proxy = $this->backend->get_ip();
			$variables = [
				"type" => "TEXT",
				"sort" => strtoupper($get["sort"]),
				"first" => 20,
				"keyword" => $search,
				"resourceTypes" => ["PHOTO"]
			];
			
			if($get["downloadable"] != "any"){
				
				$variables["downloadable"] = "ALLOW";
			}
			
			if($get["category"] != "any"){
				
				$variables["categories"] = [strtoupper($get["category"])];
			}
			/* for some reason this filter doesnt do anything? lol
			if($get["gear"] != "any"){
				
				$variables["equipments"] = [strtoupper($get["gear"])];
			}*/
		}
		
		try{
			
			$json =
				$this->get(
					$proxy,
					"https://api-neo.500px.com/graphql",
					[],
					json_encode([
						"operationName" => "searchResource",
						"variables" => $variables,
						"extensions" => [
							"clientLibrary" => [
								"name" => "@apollo/client",
								"version" => "4.1.6"
							]
						],
						"query" => "query searchResource(\$keyword: String!, \$type: SearchType = TEXT, \$first: Int!, \$after: String, \$resourceTypes: [SearchResourceType!], \$categories: [String!], \$equipments: [String!], \$downloadable: PhotoDownloadable, \$sort: SearchSortOption = RELEVANCE, \$excludeNsfw: Boolean) {\n  searchResource(\n    keyword: \$keyword\n    type: \$type\n    first: \$first\n    after: \$after\n    resourceTypes: \$resourceTypes\n    categories: \$categories\n    equipments: \$equipments\n    downloadable: \$downloadable\n    sort: \$sort\n    excludeNsfw: \$excludeNsfw\n  ) {\n    edges {\n      cursor\n      node {\n        __typename\n        ... on Photo {\n          id\n          isDeleted\n          isInReview\n          isPrivate\n          questInfo {\n            rewardQuestName\n            __typename\n          }\n          honors {\n            ...PhotoHonorFields\n            __typename\n          }\n          title\n          description\n          downloadable\n          licensing {\n            status\n            __typename\n          }\n          urls {\n            size_600\n            size_1024\n            __typename\n          }\n          uploader {\n            id\n            username\n            avatar\n            displayName\n            isBlockedByMe\n            isFollowedByMe\n            isFollowingMe\n            membership {\n              membership\n              __typename\n            }\n            __typename\n          }\n          isNsfw\n          isLikedByMe\n          viewerHasReposted\n          width\n          height\n          dominantColorLight\n          dominantColorDark\n          hasComment\n          uploadedAt\n          __typename\n        }\n        ... on Gallery {\n          id\n          isDeleted\n          isPrivate\n          kind\n          honors {\n            ...PhotoHonorFields\n            __typename\n          }\n          name\n          itemCount\n          hasComment\n          isLikedByMe\n          isNsfw\n          viewerHasReposted\n          creator {\n            id\n            username\n            displayName\n            avatar\n            isFollowedByMe\n            isFollowingMe\n            isBlockedByMe\n            membership {\n              membership\n              __typename\n            }\n            __typename\n          }\n          coverPhotos(first: 5) {\n            id\n            urls {\n              size_600\n              size_1024\n              __typename\n            }\n            __typename\n          }\n          __typename\n        }\n        ... on PhotoGroup {\n          id\n          isDeleted\n          isInReview\n          isPrivate\n          honors {\n            ...PhotoHonorFields\n            __typename\n          }\n          title\n          description\n          numCount\n          publicItemCount\n          isLikedByMe\n          isNsfw\n          viewerHasReposted\n          uploader {\n            id\n            username\n            displayName\n            avatar\n            isFollowedByMe\n            isFollowingMe\n            isBlockedByMe\n            membership {\n              membership\n              __typename\n            }\n            __typename\n          }\n          coverPhotos(first: 5) {\n            id\n            urls {\n              size_600\n              size_1024\n              __typename\n            }\n            __typename\n          }\n          __typename\n        }\n        ... on User {\n          id\n          username\n          displayName\n          avatar\n          location\n          isBlockedByMe\n          isFollowedByMe\n          isFollowingMe\n          membership {\n            membership\n            __typename\n          }\n          userCoverPhotos: coverPhotos(first: 6) {\n            id\n            urls {\n              size_1024\n              __typename\n            }\n            __typename\n          }\n          __typename\n        }\n        ... on Video {\n          id\n          isDeleted\n          isInReview\n          isPrivate\n          questInfo {\n            rewardQuestName\n            __typename\n          }\n          honors {\n            ...PhotoHonorFields\n            __typename\n          }\n          title\n          coverUrl\n          videoUrl\n          width\n          height\n          isNsfw\n          isLikedByMe\n          uploader {\n            id\n            username\n            avatar\n            displayName\n            isBlockedByMe\n            isFollowedByMe\n            isFollowingMe\n            membership {\n              membership\n              __typename\n            }\n            __typename\n          }\n          __typename\n        }\n      }\n      __typename\n    }\n    pageInfo {\n      hasNextPage\n      endCursor\n      __typename\n    }\n    totalCount\n    __typename\n  }\n}\n\nfragment PhotoHonorFields on PhotoHonor {\n  __typename\n  ... on PhotoHonorSelected {\n    type\n    __typename\n  }\n  ... on PhotoHonorAmbassadorsPick {\n    ambassador {\n      id\n      avatar\n      __typename\n    }\n    __typename\n  }\n  ... on PhotoHonorPxGallery {\n    gallery {\n      id\n      name\n      __typename\n    }\n    __typename\n  }\n}"
					])
				);
		}catch(Exception $error){
			
			throw new Exception("Failed to fetch graphQL object");
		}
		
		$json = json_decode($json, true);
		
		if($json === null){
			
			throw new Exception("Failed to decode graphQL object");
		}
		
		if(isset($json["errors"][0]["message"])){
			
			throw new Exception("500px returned an API error: " . $json["errors"][0]["message"]);
		}
		
		if(!isset($json["data"]["searchResource"]["edges"])){
			
			throw new Exception("No edges returned by API");
		}
		
		$out = [
			"status" => "ok",
			"npt" => null,
			"image" => []
		];
		
		foreach($json["data"]["searchResource"]["edges"] as $image){
			
			$image = $image["node"];
			$title =
				trim(
					$this->fuckhtml
					->getTextContent(
						$image["title"]
					) . ": " .
					$this->fuckhtml
					->getTextContent(
						$image["description"]
					)
					, " :"
				);
			
			$small = $this->image_ratio(600, $image["width"], $image["height"]);
			//$large = $this->image_ratio(2048, $image["width"], $image["height"]);
			$large = $this->image_ratio(3840, $image["width"], $image["height"]);
			
			// for some fuck ass reason the invalid signature error gives me the
			// signature key they use, lol
			// signStrPreview:
			// 67gfdsgf!5uiyoi3fdsphoto/37070890/0.jpg21790132400
			// 67gfdsgf!5uiyoi3fdsphoto/211496303/0.jpg111790132400
			
			// hash("sha256", <-key->photo/<-url id->/0.jpg<-size-><-expiry->)
			// https://cdn-resize-prod-com.500px.cloud/photo/211496303/0.jpg?s=1&expiry=1790132400&sig=b2873066126c294d7ca0b6f74db7f2626d79d5337a4c71add910f699734fade2
			
			$images = [];
			
			preg_match(
				'/photo\/([0-9]+)/',
				$image["urls"]["size_600"],
				$image_id
			);
			
			if(count($image_id) !== 0){
				
				$expiry = time() + 3600;
				
				$images[] = [
					"url" =>
						"https://cdn-resize-prod-com.500px.cloud/photo/{$image_id[1]}/0.jpg?s=4&expiry={$expiry}&sig=" .
						hash("sha256", "67gfdsgf!5uiyoi3fdsphoto/{$image_id[1]}/0.jpg4{$expiry}"),
					"width" => $large[0] > $image["width"] ? $image["width"] : $large[0],
					"height" => $large[1] > $image["height"] ? $image["height"] : $large[1]
				];
			}
			
			// add thumbnail
			$images[] = [
				"url" => $image["urls"]["size_600"],
				"width" => $small[0],
				"height" => $small[1]
			];
			
			$out["image"][] = [
				"title" => $title,
				"source" => $images,
				"url" => "https://500px.com/photo/{$image["id"]}"
			];
		}
		
		// get NPT token
		
		if($json["data"]["searchResource"]["pageInfo"]["hasNextPage"] === true){
			
			$variables["after"] = $json["data"]["searchResource"]["pageInfo"]["endCursor"];
			
			$out["npt"] =
				$this->backend->store(
					json_encode($variables),
					"images",
					$proxy
				);
		}
			
		return $out;
	}
	
	private function image_ratio($longest_edge, $width, $height){
		
		$ratio = [
			$longest_edge / $width,
			$longest_edge / $height
		];
		
		if($ratio[0] < $ratio[1]){
			
			$ratio = $ratio[0];
		}else{
			
			$ratio = $ratio[1];
		}
		
		return [
			floor($width * $ratio),
			floor($height * $ratio)
		];
	}
}
