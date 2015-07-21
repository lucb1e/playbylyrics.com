<?php
	if (empty($_GET["lyrics"])) {
		header("Location: /");
		exit;
	}
	
	if (strlen($_GET["lyrics"]) > 700) {
		exit;
	}
	
	echo @file_get_contents("http://is.gd/create.php?format=simple&url=" . str_replace("%20", "+", urlencode("http://playbylyrics.com/?lyrics=" . $_GET["lyrics"] . "&attempt=" . intval($_GET["attempt"]))));
