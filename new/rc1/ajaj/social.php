<?php 
	$tinysong_api_key  = "g3ty0ur0wn";
	$lastfm_api_key    = "g3ty0ur0wn";
	
	switch ($_GET["type"]) {
		case "gs":
			if (!empty($_GET["search"])) {
				echo file_get_contents("http://tinysong.com/b/" . urlencode($_GET["search"]) . "?format=json&key=$tinysong_api_key");
				exit;
			}
			else {
				die("Empty search query.");
			}
			exit;
		
		case "last.fm":
			if (!empty($_GET["search"])) {
				echo file_get_contents("http://ws.audioscrobbler.com/2.0/?method=track.search&format=json&track=" . urlencode($_GET["search"]) . "&api_key=$lastfm_api_key");
			}
			else {
				die("Empty search query.");
			}
			exit;
		
		default:
			die("Unrecognized type.");
	}
