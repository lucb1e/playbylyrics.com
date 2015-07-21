<?php 
	beginTemplateOutput();
	
	if (isset($_POST["feedback"])) {
		mail("SOMEEMAILADDRESS1@example.com", substr($_POST["feedback"], 0, 80), $_POST["feedback"] . "\n\n\nWanted to be contacted through $_POST[contact]", "From: SOMEEMAILADDRESS4@example.com")
			or die("Something messed up on this end of the cable... I'm sorry, but your feedback was not sent. If you reload the page (also known as 'refresh') and confirm that you want to resend the data, it automatically attempts to send it again. If that still doesn't work, please contact me through <a href='mailto:SOMEEMAILADDRESS2@example.com'>SOMEEMAILADDRESS2@example.com</a>!<br />Thanks!<br /><br />Your message was: " . htmlentities($_POST["feedback"]) . "<br/><br/>Alternatively, <a href='./'>click here</a> to return to PlayByLyrics.com.");
		header("Location: feedbacksent");
		exit;
	}
	
	require("templates/feedback.template.php");
	finishTemplate();