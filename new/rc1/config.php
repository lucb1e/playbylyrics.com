<?php 
	$db = new mysqli("localhost", "playbylyrics", "censored", "playbylyrics");
	if ($db->connect_error)
		die("Database connection error.");
	
	// Update 2015-07-21: I was going to display "last search queries" or "popular queries" or something. This was supposed to be for a filter so they don't contain crap or non-music searches.
	$undesirablewords = ["sex", "fuck", "lucb1e", "penis", "cock", "asshole", "playby", "pbl"];
	
	// Update 2015-07-21: I have no clue what this is supposed to be
	$replacementtable = array();
	for ($i = ord("a"); $i <= ord("z"); $i++) {
		$replacementtable[$i] = array();
	}
	for ($i = ord("0"); $i <= ord("9"); $i++) {
		$replacementtable[$i] = array();
	}
	$replacementtable["a"]["4"] = 1.5;
	$replacementtable["e"]["3"] = 1.5;
	$replacementtable["b"]["8"] = 1.5;
	$replacementtable["t"]["7"] = 1.5;
	$replacementtable["z"]["2"] = 1.5;
	$replacementtable["i"]["1"] = 1.5;
	$replacementtable["l"]["1"] = 1.5;
	$replacementtable["l"]["7"] = 1.5;
	$replacementtable["g"]["9"] = 1.5;
	$replacementtable["b"]["6"] = 1.5;
	$replacementtable["o"]["0"] = 1.5;
	$replacementtable["s"]["2"] = 1.25;
	$replacementtable["j"]["1"] = 1.25;
