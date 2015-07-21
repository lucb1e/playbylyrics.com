<?php 
	// Version 1.4
	// Feature changes:
	// + Title changed to include the result before the query
	// + When no YT results found, it first Youtube-searches the second and third Google result before returning a "No results found"-error.
	// + When no Google results found, it tries if Youtube can find anything, both with ($_GET["lyrics"] . " lyrics") and without.
	// 
	// Internal changes:
	// + PHP code now above HTML; structural code change
	// + getGoogle can now return multiple results at once
	
	$startLoading = microtime(true);
	$queries = 0;
	
	switch (true) {
		case preg_match("/terms/", $_GET["p"]):
			$pageTitle = " - Terms, privacy and disclaimer";
			break;
		case preg_match("/about/", $_GET["p"]):
			$pageTitle = " - About";
			break;
		case preg_match("/feedback/", $_GET["p"]):
			$pageTitle = " - Feedback";
			break;
	}
	
	
	$youtubeTitle = "";
		
	/* ********************** *
	 *    F U N C T I O N S   *
	 * ********************** */
	
	function getGoogle($str, $resultNum = 0, $tillResult = 1) {
		global $queries;
		$queries++;
		
		// Get the mobile search results
		$fp = fsockopen("www.google.com", 80, $use, $less, 2) or die("Internal error. Please try again (reload the page).");
		fwrite($fp, "GET /m?q=" . urlencode($str . " lyrics") . " HTTP/1.1\r\nHost: www.google.com\r\nUser-Agent: Mozilla/5.0 (SymbianOS/9.3; Series60/3.2 NokiaE75-1/202.12.01; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.1.19424\r\nAccept: text/html,application/xhtml+xml,application/xml\r\nAccept-Language: en-us,en\r\nConnection: close\r\n\r\n");
		
		// Read the data (up to 25 packets, 100KB max packet size)
		$data = "";
		$i = 0;
		while (($data .= fread($fp, 1024 * 100)) && $i++ < 25);
		 
		// Cut the response headers
		$data = substr($data, strpos($data, "\r\n\r\n"), 1024 * 1024); // 1MB max result length ought to be enough
		
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
		
		return json_decode(file_get_contents("https://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc&q=" . urlencode($str)));
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
	
	/* ************************** *
	 *    A P P L I C A T I O N   *
	 * ************************** */
	
	$lyricsWereEntered = strlen($_GET["lyrics"]) > 2;
	
	// Validate and sanitize $_GET["attempt"]. Default: 0. If given: =intval($_GET["attempt"]).
	if (!isset($_GET["attempt"])) {
		$_GET["attempt"] = 0;
	}
	else {
		$_GET["attempt"] = intval($_GET["attempt"]);
	}
	
	if ($lyricsWereEntered && $_GET["attempt"] <= 25) {
		// Based on whether the attempt-number is 0, set the variables used in "No$more results$found."
		   // When $attempt > 0, it will echo "No results found." When $attempt==0, it will echo "No more results."
		$more = $_GET["attempt"] > 0 ? " more" : "";
		$found = $_GET["attempt"] == 0 ? " found" : "";
		
		// Compute the Google and Youtube result number according to the attempt-number.
		   // $_GET["attempt"] = 0; $googleResultNum = 0; $youtubeResultNum = 0;
		   //                  = 1;                  = 0;                   = 1;
		   //                  = 2;                  = 1;                   = 0;
		   //                  = 3;                  = 1;                   = 1;
		   //                  = 4;                  = 2;                   = 0;
		$googleResultNum  = floor($_GET["attempt"] / 2);
		$youtubeResultNum = $_GET["attempt"] % 2;
		
		// Fetch the Google result, unless the query starts with a +
		if (substr($_GET["lyrics"], 0, 1) == "+") {
			$googleResult = -1;
			$_GET["lyrics"] = substr($_GET["lyrics"], 1);
		}
		else {
			$googleResult = getGoogle($_GET["lyrics"], $googleResultNum, $googleResultNum + 3);
		}
		
		if ($googleResult == -1) { // Google has no clue, will youtube?
			$ytData = getYoutube($_GET["lyrics"] . " lyrics");
			if (isset($_GET["debug"]))
				echo count($ytData->data->items);
			
			if (count($ytData->data->items) == 0) {
				$ytData = getYoutube($_GET["lyrics"]);
			}
		}
		else { // Google found something! Query Youtube
			$ytData = getYoutube($googleResult[0]["title"]);
			
			$i = 1; // It already did the first attempt, so start at 1 instead of 0.
			while (count($ytData->data->items) <= $youtubeResultNum && $i++ < 3) { // Google found something, but Youtube apparently not. Try the next Google result up to two times (so it got 3 results in total).
				$ytData = getYoutube($googleResult[$i]["title"]);
			}
		}
		
		if (count($ytData->data->items) > $youtubeResultNum) {
			$pageTitle = " - " . $ytData->data->items[$youtubeResultNum]->title . " - " . str_replace("<", "&gt;", $_GET["lyrics"]);
			$youtubeTitle = $ytData->data->items[$youtubeResultNum]->title;
		}
	}
	else {
		$pageTitle = "";
		if ($_GET["attempt"] > 25) {
			$more = " more";
			$found = "";
		}
	}
	
?><!DOCTYPE html>
<html>
	<head>
		<title>PlayByLyrics.com<?php echo $pageTitle; ?></title>
		<link rel=stylesheet href="res/style.css">
		<?php 
			if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "iPod")) {
				echo "<style>body{-webkit-text-size-adjust:none;}</style>";
			}
		?>
	</head>
	<body>
		<div class="container">
			<div class="head-shadow"></div>
			<div class="head" onclick="location='./';"></div>
			<div class="content-shadow"></div>
			<div class="content">
				<?php 
					if (file_exists("pages/p" . str_replace("/", "", $_GET["p"])))
						require("pages/p" . str_replace("/", "", $_GET["p"]));
				?>
			</div>
			<div class="footer-shadow"></div>
			<div class="footer">
				&copy; 2012 <a href='http://playbylyrics.com' class='nounderline'>PlayByLyrics.com</a> - <a href='about.htm'>About</a> - <a href='terms.htm'>Legal &amp; Privacy</a> - <a href='feedback.php'>Feedback</a>
			</div>
		</div>
		<?php 
			echo "<!-- Loading time: " . (microtime(true) - $startLoading) . ". Queries: " . $queries . ". -->";
		?>
	</body>
</html>
