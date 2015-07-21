<?php
	$startLoading = microtime(true);
	$queries = 0;
	
	$youtubeTitle = "";
	
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
			if ($ytData === false) {
				errorPage("Internal server error. Please try again (reload the page; press F5).", "getYoutube returned false");
			}
			
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
		setTemplateOption("margintop", 10);
		$pageTitle = "";
		if ($_GET["attempt"] > 25) {
			$more = " more";
			$found = "";
		}
	}
	
	//require("config.php");
	/* Todo:
	   * check for swear words;
	   * check for IPban;
	   * display only 20/100 of the latest searches
	   * remove special characters $|\/()_!@ from search query
	   * remove spaces before applying wordfilter
	   * filter case-insensitive
	   */
	//$db->query("SELECT 
	
	if (count($ytData->data->items) > $youtubeResultNum) { // Something was found
		$videoid = $ytData->data->items[$_GET["attempt"] - $googleResultNum]->id;
	}
	
	setTemplateOption("title", $pageTitle);
	
	beginTemplateOutput();
	require("templates/index.template.php");
	finishTemplate();
