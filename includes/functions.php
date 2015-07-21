<?php 
	function setTemplate($file) {
		global $maintemplate1, $maintemplate2;
		
		$maintemplate = file_get_contents($file);
		$pos = strpos($maintemplate, "__CONTENT__");
		
		$maintemplate1 = substr($maintemplate, 0, $pos);
		$maintemplate2 = substr($maintemplate, $pos + 11);
	}
	
	function setTemplateOption($option, $value) {
		global $templateOptions;
		
		$templateOptions[$option] = $value;
	}
	
	function beginTemplateOutput() {
		global $maintemplate1, $templateOptions;
		
		foreach ($templateOptions as $option=>$value) {
			$maintemplate1 = str_replace("__" . strtoupper($option) . "__", $value, $maintemplate1);
		}
		
		echo $maintemplate1;
	}
	
	function finishTemplate() {
		global $maintemplate2, $templateOptions;
		
		foreach ($templateOptions as $option=>$value) {
			$maintemplate2 = str_replace("__" . strtoupper($option) . "__", $value, $maintemplate2);
		}
		
		echo $maintemplate2;
	}
	
	function errorPage($message, $debugmessage = "") {
		if (empty($debugmessage))
			$debugmessage = $message;
		
		$fid = fopen("log/error.log", "a");
		fwrite($fid, date("r") . " $_SERVER[REMOTE_ADDR] $debugmessage.<br>\n");
		fclose($fid);
		
		beginTemplateOutput();
		echo $message;
		finishTemplate();
		exit;
	}
	
	function getGoogle($str, $resultNum = 0, $tillResult = 1) {
		global $queries;
		$queries++;
		
		// Get the mobile search results
		$fp = @fsockopen("www.google.com", 80, $use, $less, 2);
		if (!$fp) {
			$queries++;
			$fp = @fsockopen("www.google.com", 80, $use, $less, 3);
			if (!$fp)
				errorPage("Internal server error. Please try again (reload the page; press F5).", "Couldn't connect to google:80 in 5 seconds");
		}
		
		fwrite($fp, "GET /m?q=" . urlencode($str . " lyrics") . " HTTP/1.1\r\nHost: www.google.com\r\nUser-Agent: Mozilla/5.0 (SymbianOS/9.3; Series60/3.2 NokiaE75-1/202.12.01; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.1.19424\r\nAccept: text/html,application/xhtml+xml,application/xml\r\nAccept-Language: en-us,en\r\nConnection: close\r\n\r\n");
		
		// Read the data (up to 25 packets, 100KB max packet size)
		$data = "";
		$i = 0;
		while (($data .= fread($fp, 1024 * 100)) && $i++ < 25);
		
		// Cut the response headers
		$data = substr($data, strpos($data, "\r\n\r\n"), 1024 * 100); // 100KB max result length ought to be enough
		
		$dom = new DOMDocument;
		@$dom->loadHTML($data);
		$dom = $dom->getElementById("universal"); // <div id="universal"> is where the results are in
		if (empty($dom->nodeValue)) // Unless no results were found
			return -1;
		$dom = $dom->getElementsByTagName("a");
		
		if ($tillResult == 1) {
			return array( "url"   => $dom->item($resultNum)->getAttribute("href")
			            , "title" => $dom->item($resultNum)->nodeValue );
		}
		else {
			$array = array();
			while ($resultNum < $tillResult) {
				if ($dom->item($resultNum) == null) { // If there are insufficient results
					if (count($array) == 0) // Return -1 when there weren't any results yet
						return -1;
					else
						return $array; // Or the results so far
				}
				
				$array[] = array( "url"   => $dom->item($resultNum)->getAttribute("href")
				                , "title" => $dom->item($resultNum)->nodeValue );
				$resultNum++;
			}
			return $array;
		}
	}
	
	function getYoutube($str) {
		global $queries;
		$queries++;
		
		$retval = file_get_contents("http://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc&q=" . urlencode($str));
		if ($retval === false) {
			$queries++;
			$retval = file_get_contents("http://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc&q=" . urlencode($str));
		}
		
		if ($retval == false)
			return false;
		
		return json_decode($retval);
	}
	
	function randomExample() {
		switch (rand(1,5)) {
			case 1:
				return "<a class='example' href='/?lyrics=when+I+ruled+the+world'>when I ruled the world</a>";
			case 2:
				return "<a class='example' href='/?lyrics=paint+heart+black'>paint heart black</a>";
			case 3:
				return "<a class='example' href='/?lyrics=call+me+maybe'>call me maybe</a>";
			case 4:
				return "<a class='example' href='/?lyrics=swear+to+you+be+there+for+you'>swear to you be there for you</a>";
			case 5:
				return "<a class='example' href='/?lyrics=Linkin+park+I+tried+so+hard'>Linkin park I tried so hard</a>";
		}
	}
	
	function findWord($str, $word) {
		// Checks if $word is loosely found in $str
		global $replacementtable;
		
		$str = strtolower($str);
		
		$match = 1;
		
		for ($i = 0; $i < strlen($str); $i++) {
			for ($j = 0; $j < strlen($word); $j++) {
				if ($str[$i] == $word[$j])
					$match *= 2;
				else
					if (isset($replacementtable[$word[$j]][$str[$i]]))
						$match *= $replacementtable[$word[$j]][$str[$i]];
			}
		}
		
		return $match;
	}