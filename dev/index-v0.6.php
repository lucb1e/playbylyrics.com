<?php exit;?><!DOCTYPE html>
<html>
	<head>
		<title>PlayByLyrics.com</title>
		<style>
			body {
				font-family: Tahoma;
				font-size: 16px;
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
					$data = substr($data, strpos($data, "id=\"universal") + 25, 350);
					$data = substr($data, strpos($data, "href=\"http://") + strlen("href=\""));
					$url = substr($data, 0, strpos($data, '"'));
					$title = substr($data, strpos($data, '"'));
					$title = substr($title, strpos($title, '"') + 3);
					$title = substr($title, 0, strpos($title, "</div>"));
					$title = preg_replace("/\\<.\\>/", "", $title);
					$title = preg_replace("/\\<\\/.\\>/", "", $title);
					return array("url" => $url, "title" => $title);
				}
				
				function getYoutube($str) {
					return json_decode(file_get_contents("https://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc&q=" . urlencode($str)));
				}
				
				if (!isset($_GET["attempt"])) {
					$_GET["attempt"] = 0;
				}
				else {
					$_GET["attempt"] = intval($_GET["attempt"]);
				}
				$more = $_GET["attempt"] > 0 ? " more" : "";
				$found = $_GET["attempt"] == 0 ? " found" : "";
				
				if (strlen($_GET["lyrics"]) > 2) {
					if ($_GET["attempt"] > 2) {
						$_GET["attempt"] -= 1;
						$googleResultNum = 1;
					}
					else {
						$googleResultNum = 0;
					}
					
					$googleResult = getGoogle($_GET["lyrics"], $googleResultNum);
					if ($googleResult == -1) {
						echo "<br><h3>No$more results$found.</h3>";
					}
					else {
						$firstUrl = $googleResult["url"];
						$ytData = getYoutube($googleResult["title"]);
						if (count($ytData->data->items) <= $_GET["attempt"]) {
							echo "<br><h3>No$more results$found.</h3>";
						}
						else {
							echo "<iframe src='http://www.youtube.com/v/" . $ytData->data->items[$_GET["attempt"]]->id . "&autoplay=1' width='480' height='320'></iframe><br />";
							echo "<br />";
							echo "Not the right video? Bad quality? <a href='?lyrics=" . urlencode($_GET["lyrics"]) . "&attempt=" . ($_GET["attempt"] + 1) . "'>Try another one</a><br />";
							echo "<br />";
							echo "<a href='" . $firstUrl . "' target='_blank'>Open lyrics in a new tab</a><br />";
						}
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
			</script>
			<br />
			<br />
			<br />
			<br />
			<span class='light'>
				&copy; 2012 <a href='http://playbylyrics.com' class='light'>PlayByLyrics.com</a> - <a href='about.htm' class='light underlined'>Contact/About</a> - <a href='terms.htm' class='light underlined'>Terms/Privacy/Disclaimer</a>
			</span>
		</div>
	</body>
</html>
