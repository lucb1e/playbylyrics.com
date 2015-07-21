<?php 
	$videoid = $ytData->data->items[$_GET["attempt"] - $googleResultNum]->id;
	if ($lyricsWereEntered) {
		if (count($ytData->data->items) <= $youtubeResultNum || $videoid == "") {
			echo "<br/><h3>No$more results$found.</h3>";
		}
		else {
			//echo "<object height='400' width='600'><param name='movie' value='https://www.youtube.com/v/$videoid&autoplay=1' /><embed height='400' src='https://www.youtube.com/v/$videoid&autoplay=1' type='application/x-shockwave-flash' width='600'></embed></object>";
			echo "<iframe width='600' height='400' src='https://www.youtube.com/embed/$videoid?autoplay=1' frameborder='0' allowfullscreen></iframe>";
			//echo "<iframe id='playerfrm' src='https://youtube.googleapis.com/v/" . $videoid . "&autoplay=1' width='600' height='400'></iframe><br/>";
			echo "<br/>";
			echo "Not the right video? Bad quality? <a href='?lyrics=" . urlencode($_GET["lyrics"]) . "&attempt=" . ($_GET["attempt"] + 1) . "'>Try another one</a><br/>";
			echo "<br/>";
			echo "<a href='" . str_replace('/url?q=', '', $googleResult[0]["url"]) . "' target='_blank'>Open lyrics in a new tab</a><br/>";
			echo "<a href='javascript: downloadMp3();'>Download as MP3</a><br/>";
			echo "<div id='mp3downloadstatus'></div>";
			echo "<a href='javascript: loopIt();'>Auto-replay this</a><br/>";
			echo "<div id='more'><a href='javascript: moreOptions();'>More options</a></div><br/><br/>";
		} // There were Youtube results for the chosen Google result
	}
?>
<div class="searchBlock">
	<form method="get" action="./" onsubmit="onSubmit();">
		<input type="text" name="lyrics" placeholder="Lyrics" value='<?php echo str_replace("'", "&#39;", $_GET["lyrics"]); ?>' x-webkit-speech="x-webkit-speech"/>
		<input type="submit" id="btnSubmit" value="Play!"/>
	</form>
</div>
<p>For best results, combine the lyrics with the artist or the song title.</p>
<script>
	document.getElementsByName("lyrics")[0].focus();
	window.addEventListener('pageshow', pageShow, false);
	subm = document.getElementById("btnSubmit");
	function pageShow(ev) {
		subm.disabled = "";
		subm.value = "Play!";
	}
	function onSubmit() {
		subm.value = "Busy...";
		subm.disabled = "disabled";
	}
	<?php 
		if ($lyricsWereEntered) {
			?>
			function loopIt() {
				window.open("http://www.infinitelooper.com/?v=<?php echo $videoid ?>");
				iframehtml = document.getElementById("playerfrm").outerHTML;
				document.getElementById("playerfrm").outerHTML = "<div class='resumeDiv' style='width: 480px; height: 320px; margin: 0 auto 0 auto;'><br/><br/><br/>"
															   + "You chose to auto-replay the video. I've hid this one so<br/>"
															   + "that it doesn't continue playing here.<br/>"
															   + "<a href='javascript: restoreIframe();'>Restore video here</a><br/><br/>"
															   + "<br/><br/><br/><span class='light'>If the auto-replay doesn't work,<br/>"
															   + "you might need to disable your pop-up blocker.</span></div>";
			}
			function restoreIframe() {
				document.getElementsByClassName("resumeDiv")[0].outerHTML = iframehtml;
			}
			function moreOptions() {
				document.getElementById("more").innerHTML =
				  "Share this on <a href='https://twitter.com/share?url=" + escape(shortUrl) + "&text=" + escape("#playbylyrics Now playing:") + "' target='_blank'>Twitter</a>, "
					+ "<a href='https://plus.google.com/share?url=" + escape(shortUrl) + "' target='_blank'>Google+</a> or "
					+ "<a href='http://www.facebook.com/sharer.php?u=" + escape(shortUrl) + "&t=Now+listening+to...' target='_blank'>Facebook</a><br/>"
				+ "Listen on <a href='javascript: listenGS();'>Grooveshark</a> or <a href='javascript: listenLFM();'>Last.fm</a><br/>";
				aGET("/ajaj/social.php?type=gs&search=<?php echo str_ireplace("lyrics", "", urlencode(str_replace("'", "", $youtubeTitle)));?>", function(data){ groovesharkData = data; });
				aGET("/ajaj/social.php?type=last.fm&search=<?php echo str_ireplace("lyrics", "", urlencode(str_replace("'", "", $youtubeTitle)));?>", function(data){ lastfmData = data; });
			}
			function listenGS() {
				if (groovesharkData == "")
					return setTimeout(listenGS, 150);
				
				var data = eval("(" + groovesharkData + ")");
				
				if (!data.SongName || data.SongName == 'undefined')
					return alert("Song not found on Grooveshark :(");
				
				if (!confirm("On Grooveshark I found this song title: '" + data.SongName + "' from this artist: " + data.ArtistName + ".\nPlease confirm this is the right song, or cancel to search on Grooveshark."))
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
							 , "Busy blinking LEDs on the server"
							 , "Retrieving admin from a photo shoot"
							 , "Waiting for GPGPU CUDA streams"
							 , "Delaying plans to electrocute the admin"
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
			
			aGET("/ajaj/shorten.php?lyrics=<?php echo urlencode($_GET["lyrics"]) . (intval($_GET["attempt"]) > 0 ? "&attempt=" . intval($_GET["attempt"]) : "");?>", function(data) {
				shortUrl = data;
			});
			<?php 
		}
	?>
</script>