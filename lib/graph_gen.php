<?php

class graph{
	
	public function render(array $graph = [], array $size = [], array $colors = [], array $colors_line = []){
		
		$graph = array_merge([
			"title" => "Title",
			"subtitle" => "description",
			"grad_x" => 25,
			"grad_y" => 20,
			"x_label" => "<= x =>",
			"y_label" => "<= y =>",
			"antialias" => false,
			"data" => []
		], $graph);
		
		$size = array_merge([
			"image_width" => 750,
			"image_height" => 500,
			"x" => 90,
			"y" => 50,
			"width" => 650,
			"height" => 300,
			"square" => 11,
			"column" => 6
		], $size);
		
		$colors = array_merge([
			"bg" =>				new ImagickPixel("#1d2021"),
			"lines_bg" =>		new ImagickPixel("#3c3836"),
			"outline" =>		new ImagickPixel("#a89984"),
			"text" =>			new ImagickPixel("#ebdbb2"),
			"transparent" =>	new ImagickPixel("transparent")
		], $colors);
		
		$colors_line = array_merge([
			new ImagickPixel("#83a598"),
			new ImagickPixel("#fb4934"),
			new ImagickPixel("#b8bb26"),
			new ImagickPixel("#fabd2f"),
			new ImagickPixel("#d3869b"),
			new ImagickPixel("#8ec07c"),
			new ImagickPixel("#fe8019"),
			new ImagickPixel("#458588"),
			new ImagickPixel("#cc241d"),
			new ImagickPixel("#98971a"),
			new ImagickPixel("#d79921"),
			new ImagickPixel("#b16286"),
			new ImagickPixel("#689d6a"),
			new ImagickPixel("#d65d0e"),
			new ImagickPixel("#a89984") // other
		], $colors_line);
		
		$not_enough_data = false;
		
		// compute datapoint max ranges
		$max_range_x = 0;
		$max_range_y = 0;
		if(count($graph["data"]) === 0){
			
			$not_enough_data = true;
		}else{
			
			foreach($graph["data"] as $datapoint => $value){
				
				if(count($value) < 1){
					
					$not_enough_data = true;
				}else{
					
					$nv1 = [];
					$nv2 = [];
					foreach($value as $k => $v){
						
						$nv1[] = $v[0];
						$nv2[] = $v[1];
					}
					
					$max1 = max($nv1);
					$max2 = max($nv2);
					if($max_range_x < $max1){ $max_range_x = $max1; }
					if($max_range_y < $max2){ $max_range_y = $max2; }
				}
			}
		}

		$im = new Imagick();
		$im->newImage($size["image_width"], $size["image_height"], $colors["bg"]);
		$im->setImageFormat("png");

		$draw = new ImagickDraw();

		//
		// Draw background grid
		//
		$draw->setStrokeColor($colors["lines_bg"]);
		$draw->setStrokeAntialias($graph["antialias"]);
		$draw->setStrokeWidth(1);
		$draw->setFillColor($colors["transparent"]);

		$text = new ImagickDraw();
		$text->setFontSize(11);
		$text->setTextAntialias($graph["antialias"]);
		$text->setFillColor($colors["text"]);


		// vertical lines
		$offset = $size["width"] / ($graph["grad_x"] - 1);

		for($i=0; $i<$graph["grad_x"]; $i++){
			
			$x = round($size["x"] + ($offset * $i));
			
			if(
				$i === 0 ||
				$i === $graph["grad_x"] - 1
			){
				
				$draw->setStrokeColor($colors["outline"]);
			}else{
				
				$draw->setStrokeColor($colors["lines_bg"]);
			}
			
			$draw->line(
				$x,
				$size["y"],
				$x,
				$size["y"] + $size["height"],
			);
			
			if($not_enough_data === false){
				// add numbas
				$number = number_format(round(($graph["grad_x"] - 1 - $i) * ($max_range_x / ($graph["grad_x"] - 1))));
				$number_width = $im->queryFontMetrics($text, $number)["textWidth"];
				
				$im->annotateImage(
					$text,
					round($x - ($number_width / 2)),
					$size["y"] + $size["height"] + 14,
					0,
					$number
				);
			}
		}


		// horizontal lines
		$text->setTextAlignment(Imagick::ALIGN_RIGHT);
		$offset = $size["height"] / ($graph["grad_y"] - 1);

		$vertical_text_size = 0;

		for($i=0; $i<$graph["grad_y"]; $i++){
			
			$y = round($size["y"] + ($offset * $i));
			
			if(
				$i === 0 ||
				$i === $graph["grad_y"] - 1
			){
				
				$draw->setStrokeColor($colors["outline"]);
			}else{
				
				$draw->setStrokeColor($colors["lines_bg"]);
			}
			
			$draw->line(
				$size["x"] + 1,
				$y,
				$size["x"] + $size["width"] - 1,
				$y,
			);
			
			if($not_enough_data === false){
				$val = number_format(round(($graph["grad_y"] - 1 - $i) * ($max_range_y / ($graph["grad_y"] - 1))));
				$query = $im->queryFontMetrics($text, $val);
				
				if($query["textWidth"] > $vertical_text_size){
					
					$vertical_text_size = $query["textWidth"];
				}
				
				// add numbas
				$im->annotateImage(
					$text,
					$size["x"] - 4,
					$y + 4,
					0,
					$val
				);
			}
		}


		//
		// Draw legend
		//
		$text->setTextAlignment(Imagick::ALIGN_LEFT);
		$top_padding = 44;
		$c = count($colors_line);
		$padding = $size["width"] / $size["column"];

		$line = new ImagickDraw();
		//$line->setStrokeWidth(1);
		$line->setStrokeAntialias($graph["antialias"]);

		if($not_enough_data === false){
			$i = 0;
			foreach($graph["data"] as $datafield => $data){
				
				$used_color = $colors_line[$i % $c];
				$draw->setStrokeColor($used_color);
				$row = floor($i / $size["column"]);
				
				$x = round($size["x"] + (($i % $size["column"]) * $padding));
				$y = $size["y"] + $size["height"] + $top_padding + ($row * 17);
				
				// draw legend
				$draw->rectangle(
					$x,
					$y,
					$x + $size["square"],
					$y + $size["square"]
				);
				
				$im->annotateImage(
					$text,
					$x + $size["square"] + 4,
					$y + 10,
					0,
					$datafield
				);
				
				//
				// Draw lines
				//
				$line->setStrokeColor($used_color);
				$old_x = null;
				$old_y = null;
				$continue = true;
				
				foreach($data as $field){
					
					// field 0 = hour (sorted)
					// field 1 = datapoint
					
					$x = $size["x"] + (($max_range_x - $field[0]) * ($size["width"] / $max_range_x));
					$y = $size["y"] + (($max_range_y - $field[1]) * ($size["height"] / $max_range_y));
					
					if($continue){
						
						$continue = false;
						$old_x = $x;
						$old_y = $y;
						continue;
					}
					
					$line->line($old_x, $old_y, $x, $y);
							
					$old_x = $x;
					$old_y = $y;
				}
				
				$i++;
			}
		}

		//
		// add background labels
		//
		$text->setFontSize(13);
		$text->setFillColor($colors["bg"]);
		$draw->setFillColor($colors["text"]);
		$draw->setStrokeColor($colors["transparent"]);

		// horizontal
		$label_width_x = $im->queryFontMetrics($text, $graph["x_label"]);
		$x_x = $size["x"] + ($size["width"] / 2) - ($label_width_x["textWidth"] / 2);
		$x_y = $size["y"] + $size["height"] + ($not_enough_data ? 21 : 30);

		$draw->rectangle(
			$x_x - 2,
			$x_y - $label_width_x["textHeight"] + 4,
			$x_x + $label_width_x["textWidth"] + 2,
			$x_y + 3,
		);

		// vertical
		$label_width_y = $im->queryFontMetrics($text, $graph["y_label"]);
		$y_x = $size["x"] - $label_width_y["textHeight"] - 13 - $vertical_text_size;
		$y_y = $size["y"] + ($size["height"] / 2) - ($label_width_y["textWidth"] / 2);

		$draw->rectangle(
			$y_x + 4,
			$y_y - 2,
			$y_x + $label_width_y["textHeight"] + 3,
			$y_y + $label_width_y["textWidth"] + 2,
		);

		$im->drawImage($draw);
		$im->drawImage($line);

		// horizontal text
		$im->annotateImage(
			$text,
			$x_x,
			$x_y,
			0,
			$graph["x_label"]
		);

		// vertical text
		$im->annotateImage(
			$text,
			$y_x + $label_width_y["textHeight"],
			$y_y + $label_width_y["textWidth"],
			270,
			$graph["y_label"]
		);

		//
		// draw title + subtext
		//
		$text->setFontSize(20);
		$text->setFontWeight(900);
		$text->setFillColor($colors["text"]);

		$im->annotateImage(
			$text,
			$size["x"],
			$size["y"] - 25,
			0,
			$graph["title"]
		);

		$text->setFontSize(13);
		$text->setFontWeight(100);

		$im->annotateImage(
			$text,
			$size["x"],
			$size["y"] - 10,
			0,
			$graph["subtitle"]
		);

		if($not_enough_data){
			
			// add not enough data notice
			$rect = new ImagickDraw();
			$rect->setFillColor($colors["bg"]);
			$rect->setStrokeColor($colors_line[1]);
			
			$rect_w = 200;
			$rect_h = 24;
				
			$rect_x = round($size["x"] + ($size["width"] / 2) - ($rect_w / 2));
			$rect_y = round($size["y"] + ($size["height"] / 2) - ($rect_h / 2));
			
			$rect->rectangle(
				$rect_x,
				$rect_y,
				$rect_x + $rect_w,
				$rect_y + $rect_h
			);
			
			$im->drawImage($rect);
			
			$label = "Not enough data";
			
			$text->setFontWeight(900);
			$query = $im->queryFontMetrics($text, $label);
			
			$im->annotateImage(
				$text,
				round($size["x"] + ($size["width"] / 2) - ($query["textWidth"] / 2)),
				round($size["y"] + ($size["height"] / 2) + ($query["textHeight"] / 2)) - 3,
				0,
				$label
			);
		}

		return $im->getImageBlob();
	}
}
