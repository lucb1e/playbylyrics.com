<?php 
	if (urlencode($_GET["videoid"]) != $_GET["videoid"] && strlen($_GET["videoid"]) < 30) {
		die("error - Invalid video id it seems... Please tell me via the feedback form that errorcode 41 occured and which lyrics you used, then I can fix it!");
	}
	
	// Check youtube-mp3
	$data = @file_get_contents("http://www.youtube-mp3.org/api/itemInfo/?video_id=$_GET[videoid]&ac=www&r=" . time());
	if ($data != "") {
		$data = json_freaking_decode($data);
		
		// Download the url to the file
		$fp = fsockopen("www.youtube-mp3.org", 80) or die("error - Couldn't connect to conversion servers.");
		fwrite($fp, "GET /get?video_id=" . $_GET["videoid"] . "&h=" . $data["info"]["h"][0] . "&r=" . time() . " HTTP/1.1\r\nHost: www.youtube-mp3.org\r\nConnection: close\r\n\r\n");
		$data = fread($fp, 1024 * 1024);
		
		// Parse the headers
		$data = explode("\r\n", $data);
		foreach ($data as $header) {
			$header = explode(": ", $header);
			if (strtolower($header[0]) == "location") // Throw the location back
				die($header[1]);
		}
	}
	
	// Youtube-mp3 hasn't got it or there was a parse error
	// Continue with Audiothief
	$fp = fsockopen("www.audiothief.com", 80) or die("error - Couldn't connect to conversion servers.");
	fwrite($fp, "HEAD /en-US/Video/Download/$_GET[videoid] HTTP/1.1\r\nHost: www.audiothief.com\r\nConnection: close\r\n\r\n");
	$data = fread($fp, 1024 * 1024);
	
	if (strpos($data, "audio/mpeg3") !== false) // Audiothief got it
		die("http://www.audiothief.com/en-US/Video/Download/$_GET[videoid]");
	
	// Nobody has it yet. Since youtube-mp3 is both sued and in read-only mode...
	die("has to convert");
	
	
	function json_freaking_decode($json, $whitespace = array(" ", "\t", "\n", "\r"), $control_char_stuff = array("(", ")", "=")) {
		$final_array = array();
		$stack = array();
		$var = "";
		$val = "";
		$mode = "in-variable";
		$quotes = "";
		for ($i = 0; $i < strlen($json); $i++) {
			switch ($json[$i]) {
				case "[":
				case "{":
					if (!empty($var))
						array_push($stack, $var);
					break;
				
				case "]":
				case "}":
					if ($mode == "in-value")
						$mode = "in-variable";
					
					if (count($stack) == 0) // There are more closing brackets than opening ones - BUT WHO CARES :D - Just ignore it.
						break;
					
					if (!empty($var) && !empty($val)) {
						$tmp = array($var => $val);
						foreach (array_reverse($stack) as $layer) {
							$tmp = array($layer => $tmp);
						}
					}
					else {
						if (!empty($var)) { // {"val1", "val2"}
							$tmp = array($var); // $var is the value. Yeah that's the kind of logic you get when parsing invalid json. Or perhaps it's valid, I don't know, it just needs to parse.
							foreach (array_reverse($stack) as $layer) {
								$tmp = array($layer => $tmp);
							}
						}
					}
					if (isset($tmp)) {
						$final_array = array_merge_recursive($final_array, $tmp);
						unset($tmp);
					}
					
					$var = "";
					$val = "";
					$mode = "in-variable";
					
					unset($stack[count($stack) - 1]);
					break;
				
				case ",":
					if ($quotes != "") {
						if ($mode == "in-variable")
							$var .= ",";
						else
							$val .= ",";
						break;
					}
					if (!empty($var) && !empty($val)) {
						$tmp = array($var => $val);
						foreach (array_reverse($stack) as $layer) {
							$tmp = array($layer => $tmp);
						}
					}
					else {
						if (!empty($var)) { // {"val1", "val2"}
							$tmp = array($var); // $var is the value. Yeah that's the kind of logic you get when parsing invalid json. Or perhaps it's valid, I don't know, it just needs to parse.
							foreach (array_reverse($stack) as $layer) {
								$tmp = array($layer => $tmp);
							}
						}
					}
					if (isset($tmp)) {
						$final_array = array_merge_recursive($final_array, $tmp);
						unset($tmp);
					}
					$var = "";
					$val = "";
					$mode = "in-variable";
					break;
				
				case "=": // Whichever padawan wrote this has to learn a lot still.
					if ($quotes != "")
						if ($mode == "in-variable")
							$var .= "=";
						else
							$val .= "=";
					else {
						$val = "";
						$mode = "in-value";
					}
					break;
				
				case ":":
					if ($quotes != "")
						if ($mode == "in-variable")
							$var .= ":";
						else
							$val .= ":";
					else {
						$val = "";
						$mode = "in-value";
					}
					break;
				
				case "'":
					if ($quotes == "'") {
						$quotes = "";
						if ($mode == "in-value") {
							if (!empty($var) && !empty($val)) {
								$tmp = array($var => $val);
								foreach (array_reverse($stack) as $layer) {
									$tmp = array($layer => $tmp);
								}
							}
							else {
								if (!empty($var)) { // {"val1", "val2"}
									$tmp = array($var); // $var is the value. Yeah that's the kind of logic you get when parsing invalid json. Or perhaps it's valid, I don't know, it just needs to parse.
									foreach (array_reverse($stack) as $layer) {
										$tmp = array($layer => $tmp);
									}
								}
							}
							if (isset($tmp)) {
								$final_array = array_merge_recursive($final_array, $tmp);
								unset($tmp);
							}
						}
					}
					else
						if ($quotes != "")
							if ($mode == "in-value")
								$val .= "'";
							else
								$var .= "'";
						else
							$quotes = "'";
					break;
				
				case '"':
					if ($quotes == '"') {
						$quotes = "";
						if ($mode == "in-value") {
							if (!empty($var) && !empty($val)) {
								$tmp = array($var => $val);
								foreach (array_reverse($stack) as $layer) {
									$tmp = array($layer => $tmp);
								}
							}
							else {
								if (!empty($var)) { // {"val1", "val2"}
									$tmp = array($var); // $var is the value. Yeah that's the kind of logic you get when parsing invalid json. Or perhaps it's valid, I don't know, it just needs to parse.
									foreach (array_reverse($stack) as $layer) {
										$tmp = array($layer => $tmp);
									}
								}
							}
							if (isset($tmp)) {
								$final_array = array_merge_recursive($final_array, $tmp);
								unset($tmp);
							}
						}
					}
					else
						if ($quotes != "")
							if ($mode == "in-value")
								$val .= '"';
							else
								$var .= '"';
						else
							$quotes = '"';
					break;
				
				default:
					if ($quotes == "" && (in_array($json[$i], $whitespace) || in_array($json[$i], $control_char_stuff)))
						break; // Insignificant whitespace
					
					if ($mode == "in-value") {
						if ($quotes != "") {
							$val .= $json[$i];
						}
						else { // Let's guess that somebody forgot to comma-separate their values
							if (!empty($var) && !empty($val)) {
								$tmp = array($var => $val);
								foreach (array_reverse($stack) as $layer) {
									$tmp = array($layer => $tmp);
								}
							}
							else {
								if (!empty($var)) { // {"val1", "val2"}
									$tmp = array($var); // $var is the value. Yeah that's the kind of logic you get when parsing invalid json. Or perhaps it's valid, I don't know, it just needs to parse.
									foreach (array_reverse($stack) as $layer) {
										$tmp = array($layer => $tmp);
									}
								}
							}
							if (isset($tmp)) {
								$final_array = array_merge_recursive($final_array, $tmp);
								unset($tmp);
							}
							$var = $json[$i]; // "var:abc" the "a" would be ignored otherwise
							$val = "";
							$mode = "in-variable";
						}
					}
					else
						$var .= $json[$i];
			}
		}
		
		return $final_array;
	}
