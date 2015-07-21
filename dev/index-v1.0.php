<?php exit; ?><!DOCTYPE html>
<html>
	<head>
		<title>PlayByLyrics.com<?php 
			if (isset($_GET["lyrics"])) {
				echo " - " . $_GET["lyrics"];
			}
		?></title>
		<style>
			body {
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
		</style>
	</head>
	<body>
		<div style="width: 535px; margin: 0 auto 0 auto; text-align: center; position: relative;">
			<?php 
				function getGoogle($str, $resultNum = 0) {
					$str .= " lyrics";
					$fp = fsockopen("www.google.com", 80, $use, $less, 2) or die("Internal error. Please try again (reload the page).");
					fwrite($fp, "GET /m?q=" . urlencode($str) . " HTTP/1.1\r\nHost: www.google.com\r\nUser-Agent: Mozilla/5.0 (SymbianOS/9.3; Series60/3.2 NokiaE75-1/202.12.01; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.1.19424\r\nAccept: text/html,application/xhtml+xml,application/xml\r\nAccept-Language: en-us,en\r\nConnection: close\r\n\r\n");
					$data = "";
					$i = 0;
					while (($data .= fread($fp, 1024 * 100)) && $i++ < 25);
					
					$data = substr($data, strpos($data, "<?xml"), 1024 * 1024);
					
					$dom = new DOMDocument;
					@$dom->loadHTML($data);
					$dom = $dom->getElementById("universal");
					if (empty($dom->nodeValue))
						return -1;
					$dom = $dom->getElementsByTagName("a");
					
					$url = $dom->item($resultNum)->getAttribute("href");
					$title = $dom->item($resultNum)->nodeValue;
					
					return array("url" => $url, "title" => $title);
				}
				
				function getYoutube($str) {
					return json_decode(file_get_contents("https://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc&q=" . urlencode($str)));
				}
				
				if (strlen($_GET["lyrics"]) > 2) {
					if (!isset($_GET["attempt"])) {
						$_GET["attempt"] = 0;
					}
					else {
						$_GET["attempt"] = intval($_GET["attempt"]);
					}
					$more = $_GET["attempt"] > 0 ? " more" : "";
					$found = $_GET["attempt"] == 0 ? " found" : "";
					
					$googleResultNum = floor($_GET["attempt"] / 2);
					$googleResult = getGoogle($_GET["lyrics"], $googleResultNum);
					
					if ($googleResult == -1) {
						echo "<br><h3>No$more results$found.</h3>";
					}
					else {
						$firstUrl = $googleResult["url"];
						$ytData = getYoutube($googleResult["title"]);
						if (count($ytData->data->items) <= $_GET["attempt"] - $googleResultNum) {
							echo "<br><h3>No$more results$found.</h3>";
						}
						else {
							echo "<iframe id='playerfrm' src='http://www.youtube.com/v/" . $ytData->data->items[$_GET["attempt"] - $googleResultNum]->id . "&autoplay=1' width='480' height='320'></iframe><br />";
							echo "<br />";
							echo "Not the right video? Bad quality? <a href='?lyrics=" . urlencode($_GET["lyrics"]) . "&attempt=" . ($_GET["attempt"] + 1) . "'>Try another one</a><br />";
							echo "<br />";
							echo "<a href='" . $firstUrl . "' target='_blank'>Open lyrics in a new tab</a><br />";
							echo "<a href='javascript: loopIt();'>Auto-replay this</a><br />";
						} // There were Youtube results for the chosen Google result
					} // There are Google results at all
				}
				else { //No search query given yet
					echo "<br /><h1><img src='play.png' /> Play By Lyrics .com</h1>";
				}
			?>
			<form method="GET" onsubmit="submittt();" style='margin-top: 40px;'>
				Lyrics:
				<input style='width: 340px;' id="lyrics" name="lyrics" value='<?php echo str_replace("'", "&#39;", $_GET["lyrics"]); ?>'>
				<input style='width: 105px; height: 29px;' type="submit" id='submitt' value='Play!'><br />
				
				<span class='light' style='position: absolute; left: 65px;'>For best results, combine lyrics with artist / song title</span>
			</form>
			<script>
				document.getElementById("lyrics").focus();
				function submittt() {
					var subm = document.getElementById("submitt");
					subm.value = "Fetching...";
					subm.disabled = "disabled";
				}
				function loopIt() {
					window.open("http://www.infinitelooper.com/?v=<?php echo strlen($_GET["lyrics"]) > 2 ? $ytData->data->items[$_GET["attempt"] - $googleResultNum]->id : ""; ?>");
					iframehtml = document.getElementById("playerfrm").outerHTML;
					document.getElementById("playerfrm").outerHTML = "<div class='resumeDiv' style='width: 480px; height: 320px;'><a href='javascript: restoreIframe();'>Restore video</a></div>";
				}
				function restoreIframe() {
					document.getElementsByClassName("resumeDiv")[0].outerHTML = iframehtml;
				}
			</script>
			<br />
			<br />
			<br />
			<br />
			<span class='light copyright'>
				&copy; 2012 <a href='http://playbylyrics.com' class='light'>PlayByLyrics.com</a> - <a href='about.htm' class='light underlined'>About</a> - <a href='terms.htm' class='light underlined'>Legal &amp; Privacy</a> - <a href='feedback.php' class='light underlined'>Feedback</a>
			</span>
		</div>
	</body>
</html>