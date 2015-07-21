<?php 
	if (isset($_POST["feedback"])) {
		mail("SOMEEMAILADDRESS1@example.com", substr($_POST["feedback"], 0, 80), $_POST["feedback"] . "\n\n\nWanted to be contacted through $_POST[contact]", "From: SOMEEMAILADDRESS4@example.com")
			or die("Something messed up on this end of the cable... I'm sorry, but your feedback was not sent. If you reload the page (also known as 'refresh') and confirm that you want to resend the data, it automatically attempts to send it again. If that still doesn't work, please contact me through <a href='mailto:SOMEEMAILADDRESS2@example.com'>SOMEEMAILADDRESS2@example.com</a>!<br />Thanks!<br /><br />Alternatively, <a href='./'>click here</a> to return to PlayByLyrics.com.");
		header("Location: feedback.php?sent");
		exit;
	}
?><!DOCTYPE html>
<html>
	<head>
		<title>PlayByLyrics.com - Feedback form</title>
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
			.linkAsText {
				text-decoration: none;
				color: #000;
			}
			.copyright {
				padding-bottom: 15px;
			}
		</style>
	</head>
	<body>
		<div style="width: 535px; margin: 0 auto 0 auto; position: relative;">
			<center>
				<br />
				<h1><img src='res/img/play.png' /> <a href='./' class='linkAsText'>Play By Lyrics .com</a></h1>
			</center>
			<a href="/">&lt;- Back to the site</a><br />
			<h3 style="display: inline-block; margin-bottom: 10px; padding-top: 10px;">Feedback</h3>
			<?php 
				if (isset($_GET["sent"])) {
					die("<br />The form has been sent; thanks a lot for your feedback!<br />If you provided a way to contact you back, I will do so very soon!<br /><br /><a href='./'>Back to PlayByLyrics.com</a>");
				}
			?>
			<form method="POST" onsubmit="submittt();" action="feedback.php">
				<span>New ideas? Thoughts on current features? I'd love to hear from you!</span><br />
				<span style='color: #888;'>With your help we can make this website better for you and all other users, all you have to do is ask!</span><br />
				<br />
				<br />
				<b><label for="contact">How would you like me to reply? </b><span class="light">(optional)</span></label><br />
				<input name="contact" id="contact" type="text" style="width: 490px; color: #999;" value="@twitter, email@address.com, or however you like!" onfocus="value = value.replace('@twitter, email@address.com, or however you like!', ''); style.color='#000';" onkeyup="style.color='#000'; value = value.replace('@twitter, email@address.com, or however you like!', '');" /><br />
				<br />
				<label for="feedback"><b>What do you think?</b></label><br />
				<textarea name="feedback" id="feedback" style="width: 500px; height: 150px;"></textarea><br />
				<br />
				<input type="submit" id="submitt" value="Send feedback" />
			</form>
			<br />
			<br />
			<br />
			<a href='./'>Back to the site</a>
			<script>
				document.getElementById("lyrics").focus();
				function submittt() {
					var subm = document.getElementById("submitt");
					subm.value = "Sending...";
					subm.disabled = "disabled";
				}
			</script>
			<br />
			<br />
			<br />
			<br />
			<div class='light copyright' style="text-align: center;">
				&copy; 2012 <a href='http://playbylyrics.com' class='light'>PlayByLyrics.com</a> - <a href='about.htm' class='light underlined'>About</a> - <a href='terms.htm' class='light underlined'>Legal &amp; Privacy</a> - <a href='feedback.php' class='light underlined'>Feedback</a>
			</div>
		</div>
		<script type="text/javascript">var sc_project=8098831;var sc_invisible=1;var sc_security="50f11c25";</script><script type="text/javascript" src="http://www.statcounter.com/counter/counter.js"></script><noscript><img src="http://c.statcounter.com/8098831/0/50f11c25/1/" width="1"></noscript>
	</body>
</html>
