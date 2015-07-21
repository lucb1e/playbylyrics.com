<?php
	require("includes/functions.php");
	
	setTemplateOption("margintop", 3);
	setTemplateOption("title", "");
	setTemplate("templates/main.template.php");
	
	switch ($_GET["page"]) {
		default:
			require("pages/main.php");
			break;
		
		case "about":
			setTemplateOption("title", " - About");
			require("pages/about.php");
			break;
		
		case "feedback":
			setTemplateOption("title", " - Feedback");
			require("pages/feedback.php");
			break;
		
		case "feedbacksent":
			setTemplateOption("title", " - Feedback");
			require("pages/feedbacksent.php");
			break;
		
		case "terms":
			setTemplateOption("title", " - Legal &amp; Privacy");
			require("pages/terms.php");
			break;
	}
	
	if (isset($_GET["debug"]))
		echo "<!-- Loading time: " . (microtime(true) - $startLoading) . ". Queries: " . $queries . ". -->";