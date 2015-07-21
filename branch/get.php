<?php 
	if ($_GET["url"][0] != "h" || $_GET["url"][6] != "/" )
		exit;
	
	echo file_get_contents($_GET["url"]);