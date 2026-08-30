<?php

class yt{
	
	public function __construct(){
		
		include "lib/backend.php";
		$this->backend = new backend("yt");
	}
	
	public function getfilters($page){
		
		if($page != "videos"){
			
			return [];
		}
		
		return [
			"date" => [
				"display" => "Time posted",
				"option" => [
					"any" => "Any time",
					"hour" => "Last hour",
					"today" => "Today",
					"week" => "This week",
					"month" => "This month",
					"year" => "This year"
				]
			],
			"type" => [
				"display" => "Type",
				"option" => [
					"video" => "Video",
					"channel" => "Channel",
					"playlist" => "Playlist",
					"Movie" => "Movie"
				]
			],
			"duration" => [
				"display" => "Duration",
				"option" => [
					"any" => "Any duration",
					"short" => "Short (>4min)",
					"medium" => "Medium (4-20min)",
					"long" => "Long (<20min)"
				]
			],
			"feature" => [
				"display" => "Feature",
				"option" => [
					"any" => "No features",
					"live" => "Live",
					"4k" => "4K",
					"hd" => "HD",
					"subtitles" => "Subtitles/CC",
					"creativecommons" => "Creative Commons",
					"360" => "VR 360°",
					"vr180" => "VR 180°",
					"3d" => "3D",
					"hdr" => "HDR"
				]
			],
			"sort" => [
				"display" => "Sort by",
				"option" => [
					"relevance" => "Relevance",
					"upload_date" => "Upload date",
					"view_count" => "View count",
					"rating" => "Rating"
				]
			]
		];
	}
	
	private function ytfilter($date, $type, $duration, $feature, $sort){
		
		// ------------
		// INCOMPATIBLE FILTERS
		// channel,playlist		DURATION, FEATURES, SORT BY
		// Movie				Features=[live, subtitles, creative commons, 3d]
		
		// live, 3D
		// Type[channel, playlist, movie]
		
		// UPLOAD DATE, DURATION, 4k, 360, VR180, HDR
		// Type[channel, playlist]
		
		// -----------
		
		// MUST BE TOGETHER
		// Relevance,upload date		Type=Video
		
		switch($type){
			
			case "channel":
			case "playlist":
				if($duration != "any"){ $duration = "any"; }
				if($feature != "any"){ $feature = "any"; }
				if($sort != "any"){ $sort = "any"; }
				break;
			
			case "movie":
				if(
					in_array(
						$feature,
						[
							"live",
							"subtitles",
							"creative_commons",
							"3d"
						],
					)
				){
					
					$feature = "any";
				}
				break;
		}
		
		switch($feature){
			
			case "live":
			case "3d":
				if(
					in_array(
						$type,
						[
							"channel",
							"playlist",
							"movie"
						],
					)
				){
					
					$type = "video";
				}
				break;
		}
		
		if(
			(
				$date != "any" ||
				$duration != "any" ||
				$feature == "4k" ||
				$feature == "360" ||
				$feature == "vr180" ||
				$feature == "hdr"
			) &&
			(
				$type == "channel" ||
				$type == "playlist"
			)
		){
			
			$type = "video";
		}
		
		if(
			$date == "any" &&
			$type == "video" &&
			$duration == "any" &&
			$feature == "any" &&
			$sort == "relevance"
		){
			
			return null;
		}
		
		//print_r([$date, $type, $duration, $feature, $sort]);
		
		/*
			Encode hex data
		*/
		
		// UPLOAD DATE
		// hour				EgQIARAB			12 04 08 01 10 01
		// today			EgQIAhAB			12 04 08 02 10 01
		// week				EgQIAxAB			12 04 08 03 10 01
		// month			EgQIBBAB			12 04 08 04 10 01
		// year				EgQIBRAB			12 04 08 05 10 01
		
		// TYPE
		// video			EgIQAQ%253D%253D	12 02 10 01
		// channel			EgIQAg%253D%253D	12 02 10 02
		// playlist			EgIQAw%253D%253D	12 02 10 03
		// movie			EgIQBA%253D%253D	12 02 10 04
		
		// DURATION
		// -4min			EgIYAQ%253D%253D	12 02 18 01
		// 4-20min			EgIYAw%253D%253D	12 02 18 03
		// 20+min			EgIYAg%253D%253D	12 02 18 02
		
		// FEATURE
		// live				EgJAAQ%253D%253D	12 02 40 01
		// 4K				EgJwAQ%253D%253D	12 02 70 01
		// HD				EgIgAQ%253D%253D	12 02 20 01
		// Subtitles/CC		EgIoAQ%253D%253D	12 02 28 01
		// Creative Commons	EgIwAQ%253D%253D	12 02 30 01
		// 360				EgJ4AQ%253D%253D	12 02 78 01
		// VR180			EgPQAQE%253D		12 03 d0 01 01
		// 3D				EgI4AQ%253D%253D	12 02 38 01
		// HDR				EgPIAQE%253D		12 03 c8 01 01
		// (location & purchased unused)
		
		// SORT BY
		// Relevance		CAASAhAB			08 00 12 02 10 01 (is nothing by default)
		// Upload date		CAI%253D			08 02
		// View count		CAM%253D			08 03
		// Rating			CAE%253D			08 01
		
		// video
		// 12 02 10 01
		
		// under 4 minutes
		// 12 02 18 01
		
		// video + under 4 minutes
		// 12 04 10 01 18 01
		
		// video + under 4 minutes + HD
		// 08 00 12 06 10 01 18 01 20 01
		
		// video + under 4 minutes + upload date
		// 08 02 12 04 10 01 18 01
		
		// video + under 4 minutes + HD + upload date
		// 08 02 12 06 10 01 18 01 20 01
		
		// this year + video + under 4 minutes + HD + upload date
		// 08 02 12 08 08 05 10 01 18 01 20 01
		
		// this week + video + over 20 minutes + HD + view count
		// 08 03 12 08 08 03 10 01 18 02 20 01
		
		//echo urlencode(urlencode(base64_encode(hex2bin($str))));
		//echo bin2hex(base64_decode(urldecode(urldecode("CAI%253D"))));
		
		// week + video + 20min + rating
		// 08 01 12 06 08 03 10 01 18 02
		
		// week + video + 20min + live + rating
		// 08 01 12 08 08 03 10 01 18 02 40 01
		
		// live 12 02 40 01
		
		$hex = null;
		if(
			$date == "any" &&
			$type == "video" &&
			$duration == "any" &&
			$feature == "any" &&
			$sort == "relevance"
		){
			
			return $hex;
		}
		
		$opcode = 0;
		
		if($date != "any"){ $opcode += 2; }
		if($type != "any"){ $opcode += 2; }
		if($duration != "any"){ $opcode += 2; }
		
		switch($feature){
			
			case "live":
			case "4k":
			case "hd":
			case "subtitles":
			case "creativecommons":
			case "360":
			case "3d":
				$opcode += 2;
				break;
			
			case "hdr":
			case "vr180":
				$opcode += 3;
				break;
		}
		
		switch($sort){
			
			case "relevance": $hex .= "0800"; break;
			case "upload_date": $hex .= "0802"; break;
			case "view_count": $hex .= "0803"; break;
			case "rating": $hex .= "0801"; break;
		}
		
		$hex .= "12" . "0".$opcode;
		
		switch($date){
			
			case "hour": $hex .= "0801"; break;
			case "today": $hex .= "0802"; break;
			case "week": $hex .= "0803"; break;
			case "month": $hex .= "0804"; break;
			case "year": $hex .= "0805"; break;
		}
		
		switch($type){
			
			case "video": $hex .= "1001"; break;
			case "channel": $hex .= "1002"; break;
			case "playlist": $hex .= "1003"; break;
			case "movie": $hex .= "1004"; break;
		}
		
		switch($duration){
			
			case "short": $hex .= "1801"; break;
			case "medium": $hex .= "1803"; break;
			case "long": $hex .= "1802"; break;
		}
		
		switch($feature){
			
			case "live": $hex .= "4001"; break;
			case "4k": $hex .= "7001"; break;
			case "hd": $hex .= "2001"; break;
			case "subtitles": $hex .= "2801"; break;
			case "creativecommons": $hex .= "3001"; break;
			case "360": $hex .= "7801"; break;
			case "vr180": $hex .= "d00101"; break;
			case "3d": $hex .= "3801"; break;
			case "hdr": $hex .= "c80101"; break;
		}
		
		//echo $hex . "\n\n";
		return urlencode(base64_encode(hex2bin($hex)));
	}
	
	// me reading youtube's json
	// https://imgur.com/X9hVlFX
	
	const req_web = 0;
	const req_xhr = 1;
	
	private function get($proxy, $url, $get = [], $reqtype = self::req_web, $continuation = null){
		
		$curlproc = curl_init();
		
		if($get !== []){
			$get = http_build_query($get);
			$url .= "?" . $get;
		}
		
		curl_setopt($curlproc, CURLOPT_URL, $url);
		
		switch($reqtype){
			case self::req_web:
				$headers =
					["User-Agent: " . config::USER_AGENT,
					"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
					"Accept-Language: en-US,en;q=0.5",
					"Accept-Encoding: gzip, deflate, br, zstd",
					"Cookie: PREF=tz=America.New_York",
					"DNT: 1",
					"Connection: keep-alive",
					"Upgrade-Insecure-Requests: 1",
					"Sec-Fetch-Dest: document",
					"Sec-Fetch-Mode: navigate",
					"Sec-Fetch-Site: none",
					"Sec-Fetch-User: ?1"];
				break;
			
			case self::req_xhr:
				$headers =
					["User-Agent: " . config::USER_AGENT,
					"Accept: */*",
					"Accept-Language: en-US,en;q=0.5",
					"Accept-Encoding: gzip, deflate, br, zstd",
					"Cookie: PREF=tz=America.New_York",
					"Referer: https://youtube.com.com/",
					"Content-Type: application/json",
					"Content-Length: " . strlen($continuation),
					"DNT: 1",
					"Connection: keep-alive",
					"Sec-Fetch-Dest: empty",
					"Sec-Fetch-Mode: same-origin",
					"Sec-Fetch-Site: same-origin"];
				
				curl_setopt($curlproc, CURLOPT_POST, true);
				curl_setopt($curlproc, CURLOPT_POSTFIELDS, $continuation);
				break;
		}
		
		curl_setopt($curlproc, CURLOPT_ENCODING, ""); // default encoding
		curl_setopt($curlproc, CURLOPT_HTTPHEADER, $headers);
		
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
	
	public function video($get){
		
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
			
			$first_page = false;
			
			[$npt, $proxy] =
				$this->backend->get(
					$get["npt"],
					"videos"
				);
			
			$npt = json_decode($npt, true);
			
			try{
				$json = $this->get(
					$proxy,
					"https://www.youtube.com/youtubei/v1/search",
					[
						"key" => $npt["key"],
						"prettyPrint" => "false"
					],
					self::req_xhr,
					json_encode($npt["post"])
				);
			}catch(Exception $error){
				
				throw new Exception("Could not fetch search page");
			}
			
			$json = json_decode($json, true);
		}else{
			
			$search = $get["s"];
			if(strlen($search) === 0){
				
				throw new Exception("Search term is empty!");
			}
			
			$proxy = $this->backend->get_ip();
			$date = $get["date"];
			$type = $get["type"];
			$duration = $get["duration"];
			$feature = $get["feature"];
			$sort = $get["sort"];
			
			// parse ytInitialData
			
			$get = [
				"search_query" => $search
			];
			
			if(
				(
					$filter =
					$this->ytfilter(
						$date,
						$type,
						$duration,
						$feature,
						$sort
					)
				) !== null
			){
				
				$get["sp"] = $filter;
			}
			
			try{
				$json = $this->get(
					$proxy,
					"https://www.youtube.com/results",
					$get
				);
			}catch(Exception $error){
				
				throw new Exception("Could not fetch search page");
			}
			
			//$json = file_get_contents("scraper/yt.html");
			
			// get next page data, unique to first page
			if(
				!preg_match(
					'/ytcfg\.set\(({".*})\); *window\.ytcfg/',
					$json,
					$ytconfig
				)
			){
				
				throw new Exception("Could not get ytcfg");
			}
			
			$ytconfig = json_decode($ytconfig[1], true);
			$first_page = true;
			
			// this is where all of the data is
			if(
				!preg_match(
					'/ytInitialData *= *({.*});<\/script>/',
					$json,
					$json
				)
			){
				
				throw new Exception("Could not get ytInitialData");
			}
			
			$json = json_decode($json[1], true);
		}
		
		$items = [
			"channelRenderer" => [],
			"videoRenderer" => [],
			"shortsLockupViewModel" => [],
			"lockupViewModel" => [], // playlists
			"continuationItemRenderer" => [] // pagination
		];
		
		$search = [
			"channelRenderer",
			"videoRenderer",
			"shortsLockupViewModel",
			"lockupViewModel",
			"continuationItemRenderer"
		];
		
		$this->recursive_search($search, $items, $json);
		
		// add normal videos
		foreach($items["videoRenderer"] as $video){
			
			$show_live_badge = false;
			
			if(
				$video["navigationEndpoint"]
				["commandMetadata"]
				["webCommandMetadata"]
				["webPageType"]
				== "WEB_PAGE_TYPE_SHORTS"
			){
				
				$type = "reel";
			}elseif(isset($video["upcomingEventData"])){
				
				// is an upcoming event, categorize as livestream
				$type = "livestream";
				
			}else{
				$type = "video";
				
				// detect if its a livestream
				if(isset($video["badges"])){
					
					foreach($video["badges"] as $badge){
						
						if(
							$badge
							["metadataBadgeRenderer"]
							["label"] == "LIVE"
						){
							
							$type = "livestream";
							$show_live_badge = true;
							break;
						}
					}
				}
			}
			
			$thumbnail =
				$video["thumbnail"]
				["thumbnails"]
				[0]
				["url"];
			
			$filename = basename(parse_url($thumbnail, PHP_URL_PATH));
			
			if(
				$filename == "hq720.jpg" ||
				$filename == "mqdefault.jpg"
			){
				
				// fetch trackerless version
				// its bigger and consumes more bandwidth, but it wont serve
				// blurred out thumbnails
				$thumbnail = "https://i.ytimg.com/vi/{$video["videoId"]}/{$filename}";
			}
			
			$out[$type][] = [
				"title" =>
					$this->assemble_runs(
						$video
						["title"]
					),
				"description" =>
					isset(
						$video["detailedMetadataSnippets"]
						[0]
						["snippetText"]
					) ?
					$this->titledots(
						$this->assemble_runs(
							$video["detailedMetadataSnippets"]
							[0]
							["snippetText"]
						)
					) : null,
				"author" => [
					"name" =>
						$this->assemble_runs(
							$video["longBylineText"]
						),
					"url" =>
						"https://www.youtube.com" .
						$video["ownerText"]
						["runs"]
						[0]
						["navigationEndpoint"]
						["commandMetadata"]
						["webCommandMetadata"]
						["url"],
					"avatar" =>
						$video["channelThumbnailSupportedRenderers"]
						["channelThumbnailWithLinkRenderer"]
						["thumbnail"]
						["thumbnails"]
						[0]
						["url"]
				],
				"date" =>
					isset(
						$video["publishedTimeText"]
						["simpleText"]
					) ?
					strtotime(
						trim(
							str_replace(
								"Streamed", "",
								$video["publishedTimeText"]
								["simpleText"]
							)
						)
					) : null,
				"duration" =>
					$show_live_badge === true ?
					"_LIVE" :
					(
						isset(
							$video["lengthText"]
							["simpleText"]
						) ?
						$this->hms2int(
							$video["lengthText"]
							["simpleText"]
						) : null
					),
				"views" =>
					isset(
						$video["viewCountText"]
						["simpleText"]
					) ?
					$this->views2int(
						$video["viewCountText"]
						["simpleText"]
					) : null,
				"thumb" => [
					"ratio" => "16:9",
					"url" => $thumbnail
				],
				"url" => "https://www.youtube.com/watch?v=" . $video["videoId"]
			];
		}
		
		// add channels
		foreach($items["channelRenderer"] as $channel){
			$out["author"][] = [
				"title" =>
					$channel
					["title"]
					["simpleText"],
				"followers" =>
					isset(
						$channel
						["videoCountText"]
						["simpleText"]
					) ?
					$this->truncatedcount2int(
						$channel
						["videoCountText"]
						["simpleText"]
					) :
					null,
				"description" =>
					isset($channel["descriptionSnippet"]) ?
					$this->titledots(
						$this->assemble_runs(
							$channel
							["descriptionSnippet"]
						)
					) : null,
				"thumb" =>
					[
						"url" =>
							$this->checkhttpspresence(
								$channel
								["thumbnail"]
								["thumbnails"]
								[
									count(
										$channel
										["thumbnail"]
										["thumbnails"]
									) - 1
								]
								["url"]
							),
						"ratio" => "1:1"
					],
				"url" =>
					"https://www.youtube.com/channel/" .
					$channel
					["channelId"]
			];
		}
		
		// add reels
		foreach($items["shortsLockupViewModel"] as $reel){
			
			$out["reel"][] = [
				"title" =>
					$reel
					["overlayMetadata"]
					["primaryText"]
					["content"],
				"description" =>
					isset(
						$reel
						["overlayMetadata"]
						["primaryText"]
						["content"]
					) ?
					$this->titledots(
						$reel
						["overlayMetadata"]
						["primaryText"]
						["content"]
					) : null,
				"author" => [
					"name" => null,
					"url" => null,
					"avatar" => null
				],
				"date" => null,
				"duration" => null,
				"views" =>
					$this->truncatedcount2int(
						$reel
						["overlayMetadata"]
						["secondaryText"]
						["content"]
					),
				"thumb" => [
					"url" =>
						$reel
						["thumbnailViewModel"]
						["thumbnailViewModel"]
						["image"]
						["sources"]
						[0]
						["url"],
					"ratio" => "9:16"
				],
				"url" =>
					"https://www.youtube.com/watch?v=" .
					$reel
					["onTap"]
					["innertubeCommand"]
					["reelWatchEndpoint"]
					["videoId"]
			];
		}
		
		// add playlists
		foreach($items["lockupViewModel"] as $playlist){
			
			// get author of playlist
			$author_name = null;
			$author_url = null;
			
			if(
				!isset(
					$playlist
					["metadata"]
					["lockupMetadataViewModel"]
					["title"]
					["content"]
				) ||
				!isset(
					$playlist
					["itemPlayback"]
					["inlinePlayerData"]
					["onSelect"]
					["innertubeCommand"]
					["commandMetadata"]
					["webCommandMetadata"]
					["url"]
				)
			){
				
				// got an ad viewmodel or some invalid item with no link
				// i dont know why the fuck that happens dont ask me
				continue;
			}
			
			foreach(
				$playlist
				["metadata"]
				["lockupMetadataViewModel"]
				["metadata"]
				["contentMetadataViewModel"]
				["metadataRows"]
				[0]
				["metadataParts"]
				as $possible_author
			){
				
				if(
					isset(
						$possible_author
						["text"]
						["commandRuns"]
						[0]
						["onTap"]
						["innertubeCommand"]
						["commandMetadata"]
						["webCommandMetadata"]
						["webPageType"]
					) &&
					$possible_author
					["text"]
					["commandRuns"]
					[0]
					["onTap"]
					["innertubeCommand"]
					["commandMetadata"]
					["webCommandMetadata"]
					["webPageType"] == "WEB_PAGE_TYPE_CHANNEL"
				){
					
					$author_name = $possible_author["text"]["content"];
					
					$author_url =
						"https://www.youtube.com" .
						$possible_author
						["text"]
						["commandRuns"]
						[0]
						["onTap"]
						["innertubeCommand"]
						["commandMetadata"]
						["webCommandMetadata"]
						["url"];
					
					break;
				}
			}
			
			$out["playlist"][] = [
				"title" =>
					$playlist
					["metadata"]
					["lockupMetadataViewModel"]
					["title"]
					["content"],
				"description" => null,
				"author" => [
					"name" => $author_name,
					"url" => $author_url,
					"avatar" => null
				],
				"date" => null,
				"duration" => null,
				"views" => null,
				"thumb" => [
					"url" =>
						$playlist
						["contentImage"]
						["collectionThumbnailViewModel"]
						["primaryThumbnail"]
						["thumbnailViewModel"]
						["image"]
						["sources"]
						[0]
						["url"],
					"ratio" => "16:9"
				],
				"url" =>
					"https://www.youtube.com" .
					$playlist
					["itemPlayback"]
					["inlinePlayerData"]
					["onSelect"]
					["innertubeCommand"]
					["commandMetadata"]
					["webCommandMetadata"]
					["url"]
			];
		}
		
		// get next page
		$found_npt = false;
		
		if($first_page === true){
			// we're using the old pagination trick, google now sends encrypted data
			if(count($items["continuationItemRenderer"]) !== 0){
				
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["screenWidthPoints"] = 1920;
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["screenHeightPoints"] = 999;
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["screenPixelDensity"] = 1;
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["screenDensityFloat"] = 1;
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["utcOffsetMinutes"] = -240;
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["internalExperimentFlags"] = [];
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["concistencyTokenJars"] = [];
				
				$ytconfig["INNERTUBE_CONTEXT"]["client"]["mainAppWebInfo"] = [
					"graftUrl" => $ytconfig["INNERTUBE_CONTEXT"]["client"]["originalUrl"],
					"webDisplayMode" => "WEB_DISPLAY_MODE_BROWSER",
					"isWebNativeShareAvailable" => false
				];
				
				$ytconfig["INNERTUBE_CONTEXT"]["adSignalsInfo"] = [
					"params" => [
						[
							"key" => "dt",
							"value" => (string)$ytconfig["TIME_CREATED_MS"]
						],
						[
							"key" => "flash",
							"value" => "0"
						],
						[
							"key" => "frm",
							"value" => "0"
						],
						[
							"key" => "u_tz",
							"value" => "-240"
						],
						[
							"key" => "u_his",
							"value" => "3"
						],
						[
							"key" => "u_h",
							"value" => "1080"
						],
						[
							"key" => "u_w",
							"value" => "1920"
						],
						[
							"key" => "u_ah",
							"value" => "1080"
						],
						[
							"key" => "u_cd",
							"value" => "24"
						],
						[
							"key" => "bc",
							"value" => "31"
						],
						[
							"key" => "bih",
							"value" => "999"
						],
						[
							"key" => "biw",
							"value" => "1920"
						],
						[
							"key" => "brdim",
							"value" => "0,0,0,0,1920,0,1920,1061,1920,999"
						],
						[
							"key" => "vis",
							"value" => "1"
						],
						[
							"key" => "wgl",
							"value" => "true"
						],
						[
							"key" => "ca_type",
							"value" => "image"
						]
					]
				];
				
				$found_npt = true;
				$npt = [
					"key" =>
						$ytconfig
						["INNERTUBE_API_KEY"],
					"post" => [
						"context" =>
							$ytconfig
							["INNERTUBE_CONTEXT"],
						"continuation" =>
							$items
							["continuationItemRenderer"]
							[0]
							["continuationEndpoint"]
							["continuationCommand"]
							["token"]
					]
				];
			}
		}else{
			
			// handle next page for json endpoint
			if(count($items["continuationItemRenderer"]) !== 0){
				
				$found_npt = true;
				$npt["post"]["continuation"] =
					$items
					["continuationItemRenderer"]
					[0]
					["continuationEndpoint"]
					["continuationCommand"]
					["token"];
			}
		}
		
		if($found_npt){
			
			$out["npt"] =
				$this->backend->store(
					json_encode(
						$npt
					),
					"videos",
					$proxy
				);
		}
		
		return $out;
	}
	
	private function recursive_search(&$search, &$items, &$pointer){
		
		foreach($pointer as $key => $value){
			
			if(
				in_array(
					$key,
					$search
				)
			){
				
				// found interesting item
				$items[$key][] = $value;
				
			}elseif(is_array($value)){
				
				$this->recursive_search($search, $items, $value);
			}
		}
	}
	
	private function textualdate2unix($number){
		
		$number =
			explode(
				" ",
				str_replace(
						[
							" ago",
							"seconds",
							"minutes",
							"hours",
							"days",
							"weeks",
							"months",
							"years"
						],
						[
							"",
							"second",
							"minute",
							"hour",
							"day",
							"week",
							"month",
							"year"
						],
						$number
					),
				2
			);
		
		$time = 0;
		switch($number[1]){
			
			case "second":
				$time = (int)$number[0];
				break;
			
			case "minute":
				$time = (int)$number[0] * 60;
				break;
			
			case "hour":
				$time = (int)$number[0] * 3600;
				break;
				
			case "day":
				$time = (int)$number[0] * 86400;
				break;
				
			case "week":
				$time = (int)$number[0] * 604800;
				break;
				
			case "month":
				$time = (int)$number[0] * 2629746;
				break;
				
			case "year":
				$time = (int)$number[0] * 31556952;
				break;
		}
		
		return time() - $time;
	}
	
	private function checkhttpspresence($link){
		
		if(substr($link, 0, 2) == "//"){
			
			return "https:" . $link;
		}
		
		return $link;
	}
	
	private function textualtime2int($number){
		
		$number = explode(" - ", $number);
		
		if(count($number) >= 2){
			
			$number = $number[count($number) - 2];
		}else{
			
			$number = $number[0];
		}
		
		$number =
			str_replace(
				[
					" ",
					"seconds",
					"minutes",
					"hours",
				],
				[
					"",
					"second",
					"minute",
					"hour"
				],
				$number
			);
		
		preg_match_all(
			'/([0-9]+)(second|minute|hour)/',
			$number,
			$number
		);
		
		$time = 0;
		
		for($i=0; $i<count($number[0]); $i++){
			
			switch($number[2][$i]){
				
				case "second":
					$time = $time + (int)$number[1][$i];
					break;
					
				case "minute":
					$time = $time + ((int)$number[1][$i] * 60);
					break;
					
				case "hour":
					$time = $time + ((int)$number[1][$i] * 3600);
					break;
			}
		}
		
		return $time;
	}
	
	private function views2int($views){
		
		return
			(int)str_replace(
				",", "",
				explode(" ", $views, 2)[0]
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
	
	private function truncatedcount2int($number){
		
		// decimal should always be 1 number long
		$number = explode(" ", $number, 2);
		$number = $number[0];
		
		$unit = strtolower($number[strlen($number) - 1]);
		
		$tmp = explode(".", $number, 2);
		$number = (int)$number;
		
		if(count($tmp) === 2){
			
			$decimal = (int)$tmp[1];
		}else{
			
			$decimal = 0;
		}
		
		switch($unit){
			
			case "k":
				$exponant = 1000;
				break;
			
			case "m":
				$exponant = 1000000;
				break;
			
			case "b";
				$exponant = 1000000000;
				break;
			
			default:
				$exponant = 1;
				break;
		}
		
		return ($number * $exponant) + ($decimal * ($exponant / 10));
	}
	
	private function titledots($title){
		
		$substr = substr($title, -3);
		
		if(
			$substr == "..." ||
			$substr == "…"
		){
			
			return str_replace(["\r", "\n"], " ", trim(substr($title, 0, -3), " \n\r\t\v\x00\0\x0B\xc2\xa0"));
		}
		
		return str_replace(["\r", "\n"], " ", trim($title, " \n\r\t\v\x00\0\x0B\xc2\xa0"));
	}
	
	private function assemble_runs($runs){
		
		if(!isset($runs["runs"])){ return ""; }
		
		$text = "";
		
		foreach($runs["runs"] as $run){
			
			if(isset($run["text"])){
				
				$text .= $run["text"];
			}
		}
		
		return $text;
	}
}
