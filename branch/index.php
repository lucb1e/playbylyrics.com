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
	
	$lyricsWereEntered = strlen($_GET["lyrics"]) > 2 || isset($_GET["vidid"]);
	if (isset($_GET["vidid"])) {
		$videoid = $_GET["vidid"];
	}
	else {
	
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
	}
	
?><!DOCTYPE html>
<html>
	<head>
		<title>PlayByLyrics.com<?php echo $pageTitle; ?></title>
		<style>
			body {
				<?php echo false === strpos($_SERVER["HTTP_USER_AGENT"], "iPod") ? "" : "-webkit-text-size-adjust: none;"; ?>
				font-family: Tahoma;
				font-size: 16px;
				overflow-y: scroll;
			}
			input {
				font-family: Tahoma;
				height: 25px;
				font-size: 16px;
				padding: 0 5px 0 5px;
			}
			.light {
				color: #999;
				font-size: 12pt;
			}
			.underlined {
				text-decoration: underline;
			}
			.underlined:hover {
				color: #88D;
			}
			.example {
				color: #00A;
			}
		</style>
	</head>
	<body>
		<div style="width: 535px; margin: 0 auto 0 auto; text-align: center; position: relative;">
			<?php 
				if (!isset($videoid))
					$videoid = $ytData->data->items[$_GET["attempt"] - $googleResultNum]->id;
				
				if ($lyricsWereEntered) {
					if ((count($ytData->data->items) <= $youtubeResultNum || $videoid == "") && !isset($videoid)) {
						echo "<br/><h3>No$more results$found.</h3>";
					}
					else {
						echo "<iframe id='playerfrm' src='http://www.youtube.com/v/" . $videoid . "&autoplay=1' width='480' height='320'></iframe><br/>";
						echo "<br/>";
						echo "Not the right video? Bad quality? <a href='?lyrics=" . urlencode($_GET["lyrics"]) . "&attempt=" . ($_GET["attempt"] + 1) . "'>Try another one</a><br/>";
						echo "<br/><a href='javascript:next();'>Next</a><br/>";
						echo "<br/>";
						echo "<a href='" . str_replace('/url?q=', '', $googleResult[0]["url"]) . "' target='_blank'>Open lyrics in a new tab</a><br/>";
						echo "<div id='more'><a href='javascript: moreOptions();'>More options</a></div>";
					} // There were Youtube results for the chosen Google result
				}
				else { //No search query given yet
					echo "<br/><h1><img src='res/img/play.png' /> Play By Lyrics .com</h1>";
				}
			?>
			<form method="GET" onsubmit="submittt();" style='margin-top: 40px;'>
				Lyrics:
				<input style='width: 340px;' id="lyrics" name="lyrics" value='<?php echo str_replace("'", "&#39;", $_GET["lyrics"]); ?>'>
				<input style='width: 105px; height: 29px;' type="submit" id='submitt' value='Play!'><br/>
				<span class='light' style='position: absolute; left: 65px;'>For best results, combine lyrics with artist / song title</span>
			</form>
			<?php 
				if (!$lyricsWereEntered) {
					//echo "<br/><br/><div style='text-align:left;padding-left:0px;font-size:12pt;'>Example: " . randomExample() . "</div>";
				}
			?>
			<div id="alreadyhadall"></div>
			<script>
				videoid = "<?php echo $videoid;?>";
				previd  = "<?php echo substr($_GET["previd"], -185);?>";
				aGET("get.php?url=" + escape("https://gdata.youtube.com/feeds/api/videos/" + videoid + "/related?v=2&alt=json"), function(data) {
					var n = 0;
					do {
						related = eval("(" + data + ")")["feed"]["entry"][n++]["id"]["$t"].split(":")[3];
					} while (previd.indexOf(related) > -1 && n < 30);
					if (related == undefined || n >= eval("(" + data + ")")["feed"]["entry"].length) {
						related = eval("(" + data + ")")["feed"]["entry"][1]["id"]["$t"].split(":")[3];
						$("alreadyhadall").innerHTML = "You've already listened all videos related to this one, so I'll just pick the second and hope for the best!";
					}
				});
				aGET("get.php?url=" + escape("https://gdata.youtube.com/feeds/api/videos/" + videoid + "?alt=json"), function(data) {
					vidlen = parseInt(eval("(" + data + ")")["entry"]["media$group"]["yt$duration"]["seconds"]);
					if (vidlen == "NaN" || vidlen < 30) // Some error, just make it take forever
						vidlen = 999999999;
					
					setTimeout(function() {
						next();
					}, (vidlen + 5) * 1000);
				});
				
				
				function next() {
					location = "?vidid=" + related + "&previd=" + previd + "," + videoid;
				}
				
				document.getElementById("lyrics").focus();
				window.addEventListener('pageshow', pageShow, false);
				subm = document.getElementById("submitt");
				function pageShow(ev) {
					subm.disabled = "";
					subm.value = "Play!";
				}
				function submittt() {
					subm.value = "Fetching...";
					subm.disabled = "disabled";
				}
				<?php 
					if ($lyricsWereEntered) {
						?>
						function loopIt() {
							window.open("http://www.infinitelooper.com/?v=<?php echo $videoid ?>");
							iframehtml = document.getElementById("playerfrm").outerHTML;
							document.getElementById("playerfrm").outerHTML = "<div class='resumeDiv' style='width: 480px; height: 320px; margin: 0 auto 0 auto;'><br/><br/><br/><a href='javascript: restoreIframe();'>Restore video</a><br/><br/>"
																		   + "<br/><br/><br/><span class='light'>(You might need to disable your pop-up blocker for this feature)</span></div>";
						}
						function restoreIframe() {
							document.getElementsByClassName("resumeDiv")[0].outerHTML = iframehtml;
						}
						function moreOptions() {
							document.getElementById("more").innerHTML
							= "<a href='javascript: loopIt();'>Auto-replay this</a><br/>"
							+ "Share this on <a href='https://twitter.com/share?url=" + escape(shortUrl) + "&text=" + escape("#playbylyrics Now playing:") + "' target='_blank'>Twitter</a>, "
								+ "<a href='https://plus.google.com/share?url=" + escape(shortUrl) + "' target='_blank'>Google+</a> or "
								+ "<a href='http://www.facebook.com/sharer.php?u=" + escape(shortUrl) + "&t=Now+listening+to...' target='_blank'>Facebook</a><br/>"
							+ "Listen on <a href='javascript: listenGS();'>Groovesbark</a> or <a href='javascript: listenLFM();'>Last.fm</a><br/>"
							+ "<a href='javascript: downloadMp3();'>Download as MP3</a><br/>"
							+ "<div id='mp3downloadstatus'></div>";
							aGET("/ajaj/social.php?type=gs&search=<?php echo str_ireplace("lyrics", "", urlencode(str_replace("'", "", $youtubeTitle)));?>", function(data){ groovesharkData = data; });
							aGET("/ajaj/social.php?type=last.fm&search=<?php echo str_ireplace("lyrics", "", urlencode(str_replace("'", "", $youtubeTitle)));?>", function(data){ lastfmData = data; });
						}
						function listenGS() {
							if (groovesharkData == "")
								return setTimeout(listenGS, 150);
							
							var data = eval("(" + groovesharkData + ")");
							
							if (!data.SongName || data.SongName == 'undefined')
								return alert("Song not found on Grooveshark :(");
							
							if (!confirm("Searching Grooveshark, I found '" + data.SongName + "' from " + data.ArtistName + ". Please confirm this is the right song, or cancel to search on Grooveshark."))
								location.href = "http://grooveshark.com/search?q=<?php echo str_ireplace("lyrics", "", urlencode($youtubeTitle));?>";
							else
								location.href = data.Url;
						}
						function listenLFM() {
							if (lastfmData == "")
								return setTimeout(listenLFM, 150);
							
							var data = eval("(" + lastfmData + ")");
							data = data.results.trackmatches.track[0];
							if (confirm("Searching Last.fm, I found '" + data.name + "' from " + data.artist + ". Please confirm this is the right song, or cancel to search on Last.fm."))
								location.href = data.url;
							else
								location.href = "http://last.fm/search?q=<?php echo str_ireplace("lyrics", "", urlencode($youtubeTitle));?>";
						}
						function aGET(uri, callback) {
							var req = new XMLHttpRequest();
							req.open("GET", uri, true);
							req.send(null);
							req.onreadystatechange = function() {
								if (req.readyState == 4)
									callback(req.responseText);
							}
						}
						function downloadMp3() {
							if (!checkingmp3) {
								aGET('/ajaj/checkmp3.php?videoid=<?php echo $videoid;?>', downloadMp32);
								checkingmp3 = true;
								populateMp3Status();
							}
							else {
								alert("It should redirect you in a few seconds... If it doesn't, some error has occured and you should refresh the page.");
							}
						}
						function populateMp3Status() {
							if (!checkingmp3)
								return;
							
							var options = ["Manning pirate ship"
										 , "Hoisting pirate sails"
										 , "Checking for download"
										 , "Replacing drunk pirate"
										 , "Waiting for server"
										 , "Waking up a pirate"
										 , "Patching Adobe Flash Player"
										 , "Ending a game of poker with the server"
										 , "E-mailing the admin"
										 , "Running CSMA/CD diagnostics"
										 , "Collecting bytes"
										 , "Spinning up harddrive"
										 , "Busy with blinking LEDs on the server"
										 , "Retrieving admin from a photo shoot"
										 , "Waiting for GPGPU CUDA streams"
							  ];
							var found = false;
							for (var i in options) {
								if (Math.random() < 1 / options.length) {
									options[i] = "<img src='res/img/loading.gif' height='15'/>" + options[i];
									if (document.getElementById("mp3downloadstatus").innerHTML != options[i]) {
										document.getElementById("mp3downloadstatus").innerHTML = options[i] + "...";
										found = true;
										break;
									}
								}
							}
							if (!found) {
								populateMp3Status();
								return;
							}
							
							setTimeout(populateMp3Status, 2500);
						}
						function downloadMp32(response) {
							checkingmp3 = false;
							document.getElementById("mp3downloadstatus").innerHTML = "";
							if (response.indexOf("error - ") == 0) {
								alert(response.replace("error - ", ""));
							}
							else {
								if (response == "has to convert") {
									document.getElementById("mp3downloadstatus").innerHTML = "<form class='myform' target='_blank' action='http://www.audiothief.com/en-US/Video/Convert'><input type='hidden' name='VideoUrl' value='http://youtube.com/watch?v=<?php echo $videoid;?>'><input type=submit value='This video has not yet been converted, click here to do so'></form>";
								}
								else {
									location = response;
								}
							}
						}
						
						var shortUrl = "";
						var groovesharkData = "";
						var lastfmData = "";
						var checkingmp3 = false;
						<?php 
					}
				?>
			</script>
			<br/>
			<br/>
			<br/>
			<br/>
			<span class='light copyright'>
				&copy; 2012 <a href='http://playbylyrics.com' class='light'>PlayByLyrics.com</a> - <a href='about.htm' class='light underlined'>About</a> - <a href='terms.htm' class='light underlined'>Legal &amp; Privacy</a> - <a href='feedback.php' class='light underlined'>Feedback</a>
			</span>
		</div>
		<script type="text/javascript">var sc_project=8098831;var sc_invisible=1;var sc_security="50f11c25";</script><script type="text/javascript" src="http://www.statcounter.com/counter/counter.js"></script><noscript><img src="http://c.statcounter.com/8098831/0/50f11c25/1/" width="1"></noscript>
		<?php 
			if (isset($_GET["debug"])) {
				echo "<!-- Loading time: " . (microtime(true) - $startLoading) . ". Queries: " . $queries . ". -->";
			}
		?>
	</body>
</html>
